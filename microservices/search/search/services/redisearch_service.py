import redis
from os import environ
import json
from dotenv import load_dotenv
from services.helper_service import HelperService
import re


#iniject Helper Service
helper = HelperService()

# Use basic Levenshtein import with fallback
try:
    # Use the faster C implementation if available
    from Levenshtein import distance as levenshtein_distance
except ImportError:
    # Fall back to pure Python implementation
    from difflib import SequenceMatcher


    def levenshtein_distance(s1, s2):
        return 1 - SequenceMatcher(None, s1, s2).ratio() * len(max(s1, s2, key=len))

load_dotenv()

REDIS_PORT = environ.get("APP_REDIS_PORT", 6380)
HOST = environ.get("APP_REDIS_HOST", "redisearch")


class RedisearchService:
    def __init__(self):
        """Initialize Redis client."""
        self.client = redis.Redis(host=HOST, port=REDIS_PORT, decode_responses=True)
        self.index_name = "idx:category"
        # Cache for connection pooling
        self._connection_pool = None

    @property
    def connection(self):
        """Lazy initialization of Redis connection with connection pooling."""
        if self._connection_pool is None:
            self._connection_pool = redis.ConnectionPool(
                host=HOST,
                port=REDIS_PORT,
                decode_responses=True,
                max_connections=10  # Adjust based on your application needs
            )
        return redis.Redis(connection_pool=self._connection_pool)

    def create_index(self):
        """Create the RediSearch index if it does not exist."""
        try:
            existing_indexes = self.client.execute_command("FT._LIST")
            if self.index_name in existing_indexes:
                print("Index already exists!")
                return
        except redis.ResponseError as e:
            # Handle the case where FT._LIST command might fail
            print(f"Error checking existing indexes: {str(e)}")
            # Continue with index creation attempt

        try:
            # Create the index using the correct RediSearch syntax
            # FT.CREATE idx ON JSON PREFIX 1 prefix: SCHEMA $.field AS alias TYPE [options...]
            create_cmd = [
                "FT.CREATE", self.index_name,
                "ON", "JSON",
                "PREFIX", "1", "category:",
                "SCHEMA",
                "$.name", "AS", "name", "TEXT", "WEIGHT", "5.0", "SORTABLE",
                "$.display_name", "AS", "display_name", "TEXT", "WEIGHT", "4.0",
                "$.origin_name", "AS", "origin_name", "TEXT",
                "$.slug", "AS", "slug", "TEXT",
                "$.sku", "AS", "sku", "TAG",
                "$.iso", "AS", "iso", "TAG",
                "$.linked", "AS", "linked", "TAG"
            ]

            self.client.execute_command(*create_cmd)
            print("Index created successfully!")
        except redis.ResponseError as e:
            if "Index already exists" in str(e):
                print("Index already exists!")
            else:
                print(f"Error creating index: {str(e)}")
                raise

    def search_products(self, query, iso=None, limit=30, max_distance=2):
        """Search products using multiple RediSearch strategies with special character handling."""
        return self._search(query, iso, limit, max_distance)

    def search_categories(self, query, iso=None, limit=30, max_distance=2):
        """Search categories using RediSearch with special character handling."""
        return self._search(query, iso, limit, max_distance)

    def _search(self, query, iso=None, limit=30, max_distance=2):
        """Unified search method for categories with enhanced fuzzy search."""
        try:
            # Clean and normalize query
            query = query.strip()
            if not query:
                # If no query provided, return all items
                search_query = f'@iso:{{{iso}}}' if iso else "*"
                result = self.client.execute_command(
                    "FT.SEARCH", self.index_name, search_query, "LIMIT", 0, limit
                )
                return self._process_search(result) if result else []

            # Handle single character searches
            # if len(query) == 1:
            #     return self._search_single_character(query, iso, limit)
            
            # Handle special characters and fuzzy search
            return self._search_with_fuzzy(query, iso, limit, max_distance)
            
        except Exception as e:
            print(f"Error in search: {str(e)}")
            return {"error-search": str(e)}

    def _search_with_fuzzy(self, query, iso=None, limit=30, max_distance=2):
        """Enhanced search with fuzzy matching and special character handling."""
        try:
            # Keep an escaped form for literal phrase attempts, but derive tokens for TEXT matching
            escaped_for_literal = self._clean_query(query)
            words = re.findall(r'\w+', query.lower())
            words = [w for w in words if w]

            search_strategies = []

            if words:
                phrase = " ".join(words)
                first = words[0]

                # Phrase of tokens (sequence of words)
                search_strategies.append(f'@name:"{phrase}"')
                search_strategies.append(f'@display_name:"{phrase}"')
                # Starts with first token
                search_strategies.append(f'@name:{first}*')
                search_strategies.append(f'@display_name:{first}*')
                # Contains first token
                search_strategies.append(f'@name:*{first}*')
                search_strategies.append(f'@display_name:*{first}*')
                # Each word contains
                search_strategies.extend([f'@name:*{w}*' for w in words if len(w) > 1])
                search_strategies.extend([f'@display_name:*{w}*' for w in words if len(w) > 1])
                # Fuzzy per word (helps minor typos)
                search_strategies.extend([f'@name:%{w}%' for w in words if len(w) > 2])
                search_strategies.extend([f'@display_name:%{w}%' for w in words if len(w) > 2])

                # Also search origin_name the same ways when iso is not set
                if not iso:
                    search_strategies.append(f'@display_name:"{phrase}"')
                    search_strategies.append(f'@display_name:{first}*')
                    search_strategies.append(f'@display_name:*{first}*')
                    search_strategies.extend([f'@display_name:*{w}*' for w in words if len(w) > 1])
                    search_strategies.extend([f'@display_name:%{w}%' for w in words if len(w) > 2])

                    search_strategies.append(f'@origin_name:"{phrase}"')
                    search_strategies.append(f'@origin_name:{first}*')
                    search_strategies.append(f'@origin_name:*{first}*')
                    search_strategies.extend([f'@origin_name:*{w}*' for w in words if len(w) > 1])
                    search_strategies.extend([f'@origin_name:%{w}%' for w in words if len(w) > 2])

                # Try slug field too, often punctuation-less
                search_strategies.append(f'@slug:{first}*')
                search_strategies.extend([f'@slug:*{w}*' for w in words if len(w) > 1])

            # Literal attempt as a low-priority fallback
            if escaped_for_literal:
                search_strategies.append(f'@name:"{escaped_for_literal}"')

            all_results = []
            # When iso is omitted, allow multiple entries per linked (different languages)
            # but dedupe by (linked, display_name)
            seen_combo = set()
            def _norm(v):
                return str(v).strip().casefold() if v is not None else ""

            # for strategy in search_strategies:
            #     try:
            #         full_query = f'@iso:{{{iso}}} {strategy}' if iso else strategy
            #         result = self.client.execute_command(
            #             "FT.SEARCH", self.index_name, full_query, "LIMIT", 0, limit * 2
            #         )
            #
            #         if result and len(result) > 1:
            #             processed_result = self._process_search(result, dedupe_by_linked=False)
            #             for item in processed_result:
            #                 linked = _norm(item.get("linked"))
            #                 display_name = _norm(item.get("display_name"))
            #                 all_results.append(item)
            #
            #                 # if linked and display_name:
            #                 #     combo = (linked, display_name)
            #                 #     if combo in seen_combo:
            #                 #         continue
            #                 #     all_results.append(item)
            #                 #     seen_combo.add(combo)
            #                 # else:
            #                 #     # If either is missing, don't dedupe to avoid dropping data
            #                 #     all_results.append(item)
            #
            #     except Exception as e:
            #         print(f"Search strategy '{strategy}' failed: {e}")
            #         continue

            # assume _norm and seen_combo are already defined above (as in your file)

            for strategy in search_strategies:
                try:
                    full_query = f'@iso:{{{iso}}} {strategy}' if iso else strategy
                    result = self.client.execute_command(
                        "FT.SEARCH", self.index_name, full_query, "LIMIT", 0, limit * 2
                    )

                    if result and len(result) > 1:
                        processed_result = self._process_search(result, dedupe_by_linked=False)
                        for item in processed_result:
                            linked = _norm(item.get("linked"))
                            iso_val = _norm(item.get(iso)) if iso else ""

                            if iso and linked and iso_val:
                                combo = (linked, iso_val)
                                if combo in seen_combo:
                                    continue
                                all_results.append(item)
                                seen_combo.add(combo)
                            else:
                                # keep behavior for items missing linked/iso value
                                all_results.append(item)

                except Exception as e:
                    print(f"Search strategy '{strategy}' failed: {e}")
                    continue

            # If no results, try broader fuzzy search using token phrase if available
            if not all_results:
                token_phrase = " ".join(words) if words else query
                all_results = self._fallback_fuzzy_search(token_phrase, iso, limit)

            # Sort by relevance using token phrase
            sort_key_query = " ".join(words) if words else query
            all_results = self._sort_by_relevance(all_results, sort_key_query)

            seen_names = set()
            deduplicated_results = []
            for item in all_results:
                name = item.get("name", "").strip()
                if name and name not in seen_names:
                    deduplicated_results.append(item)
                    seen_names.add(name)
                elif not name:
                    # Keep items without name to avoid losing data
                    deduplicated_results.append(item)
            return deduplicated_results[:limit]

        except Exception as e:
            print(f"Error in fuzzy search: {e}")
            return []

    def _fallback_fuzzy_search(self, query, iso=None, limit=30):
        """Fallback fuzzy search when exact strategies fail."""
        try:
            # Get all items and filter locally
            search_query = f'@iso:{{{iso}}}' if iso else "*"
            result = self.client.execute_command(
                "FT.SEARCH", self.index_name, search_query, "LIMIT", 0, 1000
            )
            
            if not result or len(result) <= 1:
                return []
            
            all_items = self._process_search(result)
            fuzzy_matches = []
            query_lower = query.lower()
            
            for item in all_items:
                name = item.get("name", "").lower()
                display_name = item.get("display_name", "").lower()
                
                # Check if query is contained in name or display_name
                if (query_lower in name or 
                    query_lower in display_name or
                    any(word in name for word in query_lower.split() if len(word) > 1)):
                    fuzzy_matches.append(item)
            
            return fuzzy_matches[:limit]
            
        except Exception as e:
            print(f"Error in fallback fuzzy search: {e}")
            return []

    def _search_single_character(self, query, iso=None, limit=30):
        """Handle single character searches with enhanced strategies."""
        try:
            # Clean the single character
            query = self._clean_query(query)
            
            # Multiple strategies for single character
            search_strategies = [
                f'@name:{query}*',      # Starts with
                f'@name:*{query}*',     # Contains
                f'@name:%{query}%',     # Fuzzy
            ]
            if not iso:
                search_strategies.extend([
                    f'@origin_name:{query}*',
                    f'@origin_name:*{query}*',
                    f'@origin_name:%{query}%',

                    f'@display_name:{query}*',
                    f'@display_name:*{query}*',
                    f'@display_name:%{query}%'
                ])
            
            all_results = []
            seen_combo = set()
            def _norm(v):
                return str(v).strip().casefold() if v is not None else ""
            
            for strategy in search_strategies:
                try:
                    full_query = f'@iso:{{{iso}}} {strategy}' if iso else strategy
                    result = self.client.execute_command(
                        "FT.SEARCH", self.index_name, full_query, "LIMIT", 0, limit * 2
                    )
                    
                    # if result and len(result) > 1:
                    #     processed_result = self._process_search(result, dedupe_by_linked=False)
                    #     for item in processed_result:
                    #         linked = _norm(item.get("linked"))
                    #         display_name = _norm(item.get("display_name"))
                    #         if linked and display_name:
                    #             combo = (linked, display_name)
                    #             if combo in seen_combo:
                    #                 continue
                    #             all_results.append(item)
                    #             seen_combo.add(combo)
                    #         else:
                    #             # If either is missing, don't dedupe to avoid dropping data
                    #             all_results.append(item)
                                
                except Exception as e:
                    print(f"Single char strategy '{strategy}' failed: {e}")
                    continue
            
            # Sort by relevance
            all_results = self._sort_by_relevance(all_results, query)
            
            return all_results[:limit]
            
        except Exception as e:
            print(f"Error in single character search: {e}")
            return []

    def _filter_and_sort_single_char(self, results, query):
        """Filter and sort single character search results."""
        starts_with_char = []
        contains_char = []
        query_lower = query.lower()
        
        for item in results:
            if 'name' in item:
                name_lower = item['name'].lower()
                if name_lower.startswith(query_lower):
                    starts_with_char.append(item)
                elif query_lower in name_lower:
                    contains_char.append(item)
        
        return starts_with_char + contains_char

    def _sort_by_relevance(self, results, query):
        """Sort results by relevance to the query."""
        query_lower = query.lower()
        
        def sort_key(item):
            item_name = item.get("name", "").lower()
            if item_name.startswith(query_lower):
                return 0  # Highest priority
            return 1  # Lower priority
        
        results.sort(key=sort_key)
        return results

    def _build_query_variants(self, query):
        """Build multiple search query variants for better results."""
        query = query.strip().lower()
        query = re.sub(r'\s+', ' ', query)
        escaped = self._escape_special_chars(query)
        parts = re.findall(r'\w+', query)

        variants = [
            f'@name:"{escaped}"',      # Exact match
            f'@name:*{escaped}*',      # Contains
            f'@name:{escaped}*'        # Starts with
        ] + [f'@name:*{p}*' for p in parts]  # Individual words

        return variants

    def _escape_special_chars(self, text):
        """Escape special characters for RediSearch queries."""
        return re.sub(r'([,.<>"\':;!@#$%^&\-+=~|/\\\[\]\(\)\{\}])', r'\\\1', text)

    
    def _process_search(self, search_results, dedupe_by_linked=True):
        """Efficiently parse Redis search results into structured dicts.

        When dedupe_by_linked is True, only one item per linked group is returned.
        Set to False to return all translations (all iso variants) for a linked id.
        """
        structured_data = []
        seen_linked = set()  # Track seen linked values to prevent duplicates when deduping
        
        if not search_results or len(search_results) <= 1:
            return structured_data

        for i in range(1, len(search_results), 2):
            product_id = search_results[i]
            product_data = search_results[i + 1]

            if isinstance(product_data, list) and len(product_data) > 1:
                try:
                    json_data = json.loads(product_data[1])
                    iso_value = json_data.get("iso", "")
                    linked_value = json_data.get("linked", "")
                    
                    # Skip if we've already seen this linked value (only when deduping)
                    if dedupe_by_linked and linked_value in seen_linked:
                        continue
                    
                    structured_item = {
                        # "sku": product_id,  # Return the Redis JSON key as sku
                        "name": json_data.get("name", ""),  # Main collection name (single string)
                        "display_name": json_data.get("origin_name", ""),  # Localized display_name
                        # "origin_name": json_data.get("origin_name", ""),  # Origin's display_name for this iso
                        "slug": json_data.get("slug", ""),
                        "linked": linked_value
                    }

                    if iso_value:
                        structured_item[iso_value] = json_data.get("display_name", "")  # Localized display_name for this iso

                    structured_data.append(structured_item)
                    if dedupe_by_linked:
                        seen_linked.add(linked_value)  # Mark this linked value as seen
                    
                except (json.JSONDecodeError, IndexError) as e:
                    print(f"Error decoding Redis JSON at index {i}: {str(e)}")
                    continue

        # Filter out items with null names before returning
        filtered_results = []
        for item in structured_data:
            if item and item.get("name") is not None:  # Skip null names
                filtered_results.append(item)

        return filtered_results

    def get_redis_info(self):
        """Get Redis connection and memory usage information for all indexes."""
        try:
            self.client.ping()
            
            # Get all available indexes
            all_indexes = self.client.execute_command("FT._LIST")
            
            indexes_info = {}
            total_memory = 0
            
            for index_name in all_indexes:
                try:
                    index_info = self.client.execute_command(f"FT.INFO {index_name}")
                    
                    memory_usage = None
                    num_docs = None
                    
                    for i in range(len(index_info)):
                        if index_info[i] == "inverted_sz_mb":
                            memory_usage = index_info[i + 1]
                        elif index_info[i] == "num_docs":
                            num_docs = index_info[i + 1]
                    
                    if memory_usage:
                        total_memory += float(memory_usage)
                    
                    indexes_info[index_name] = {
                        "memory_usage_mb": memory_usage,
                        "num_docs": num_docs
                    }
                    
                except Exception as e:
                    indexes_info[index_name] = {
                        "error": str(e)
                    }
            
            return {
                "status": "Connected to Redis",
                "indexes": indexes_info,
                "total_memory_usage_mb": round(total_memory, 6),
                "total_indexes": len(all_indexes)
            }

        except redis.ConnectionError:
            return {"status": "Failed to connect to Redis"}

    def _clean_query(self, query):
        """Escape special characters for literal RediSearch attempts."""
        # Remove extra whitespace
        query = re.sub(r'\s+', ' ', query.strip())
        # Escape special characters for literal phrase query
        special_chars = r'([,.<>"\':;!@#$%^&\-+=~|/\\\[\]\(\)\{\}])'
        return re.sub(special_chars, r'\\\1', query)