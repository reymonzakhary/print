import redis
from os import environ, path
import os
import json
import re
from dotenv import load_dotenv
from redis.commands.search.field import TagField, TextField
from redis.commands.search.index_definition import IndexDefinition, IndexType
from Levenshtein import distance as levenshtein_distance  # Install with `pip install python-Levenshtein`
from services.helper_service import HelperService

load_dotenv()

REDIS_PORT = environ.get("APP_REDIS_PORT", 6380)
HOST = environ.get("APP_REDIS_HOST", "redisearch")

#iniject Helper Service
helper = HelperService()

class RedisearchService:
    def __init__(self):
        """Initialize Redis client."""
        self.client = redis.Redis(host=HOST, port=REDIS_PORT, decode_responses=True)
        self.index_name = "idx:option"

    def create_index(self):
        """Create the RediSearch index if it does not exist."""
        existing_indexes = self.client.execute_command("FT._LIST")
        if self.index_name in existing_indexes:
            print("Option Index already exists!")
            return

        # Updated schema: index name, display_name, and origin_name for comprehensive search
        schema = (
            TextField("$.name", as_name="name", weight=5.0, no_stem=False),
            TextField("$.display_name", as_name="display_name", weight=4.0, no_stem=False),
            TagField("$.display_name_exact", as_name="display_name_exact", separator="|"),
            TextField("$.origin_name", as_name="origin_name"),
            TagField("$.sku", as_name="sku"),  # Index SKU for filtering
            TagField("$.slug", as_name="slug"),
            TagField("$.iso", as_name="iso"),
            TagField("$.linked", as_name="linked")
        )

        self.client.ft(self.index_name).create_index(
            schema,
            definition=IndexDefinition(prefix=["option:"], index_type=IndexType.JSON)
        )
        print("Index created successfully!")

    def search_options(self, query, iso=None, limit=30, max_distance=2):
        """Search options using RediSearch with special character handling."""
        return self._search(query, iso, limit, max_distance)

    def _search(self, query, iso=None, limit=30, max_distance=2):
        """Unified search method for options with enhanced fuzzy search."""
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

            # Tokenize to drive branching so inputs like "4/" behave like "4"
            normalized = re.sub(r'\W+$', '', query)  # strip trailing non-word chars like /, -, _
            words = re.findall(r'[\w/]+', normalized.lower())

            # If tokenized to a single character (e.g., "4/" -> "4"), use single-char strategy
            if len(words) == 1 and len(words[0]) == 1:
                return self._search_single_character(words[0], iso, limit)

            # Handle single character searches (raw input exactly one char)
            if len(query) == 1:
                return self._search_single_character(query, iso, limit)
            
            # Handle special characters and fuzzy search
            exact_hits = self._search_exact_display_name(query, iso, limit)
            if exact_hits:
                return exact_hits

            return self._search_with_fuzzy(query, iso, limit, max_distance)
            
        except Exception as e:
            print(f"Error in search: {str(e)}")
            return {"error-search": str(e)}

    def _search_with_fuzzy(self, query, iso=None, limit=30, max_distance=2):
        """Enhanced search with fuzzy matching and special character handling."""
        try:
            # Keep an escaped form for literal phrase attempts, but derive tokens for TEXT matching
            escaped_for_literal = self._clean_query(query)
            normalized = re.sub(r'\W+$', '', query)
            words = [w for w in re.findall(r'[\w/]+', normalized.lower()) if w]
            escaped_words = [self._escape_special_chars(w) for w in words]

            search_strategies = []
            strategy_set = set()

            def add_strategy(fragment):
                if fragment and fragment not in strategy_set:
                    strategy_set.add(fragment)
                    search_strategies.append(fragment)

            if words:
                escaped_phrase = " ".join(escaped_words)
                first = words[0]
                escaped_first = escaped_words[0]
                candidate_fields = ["display_name", "name", "origin_name", "slug"]

                # Preserve order but avoid duplicates
                seen_fields = set()
                fields = []
                for field in candidate_fields:
                    if field and field not in seen_fields:
                        seen_fields.add(field)
                        fields.append(field)

                for field in fields:
                    add_strategy(f'@{field}:"{escaped_phrase}"')
                    add_strategy(f'@{field}:{escaped_first}*')
                    add_strategy(f'@{field}:*{escaped_first}*')
                    for ew, w in zip(escaped_words, words):
                        if len(w) > 1:
                            add_strategy(f'@{field}:*{ew}*')
                        if len(w) > 2:
                            add_strategy(f'@{field}:%{ew}%')

                for short in {w for w in words if len(w) == 1}:
                    esc_short = self._escape_special_chars(short)
                    for field in fields:
                        add_strategy(f'@{field}:{esc_short}*')
                        add_strategy(f'@{field}:*{esc_short}*')

                for token in (w for w in words if '/' in w):
                    parts = [self._escape_special_chars(p) for p in token.split('/') if p]
                    if len(parts) >= 2:
                        phrase_with_space = " ".join(parts)
                        for field in fields:
                            add_strategy(f'@{field}:"{phrase_with_space}"')

            # Literal attempt as a low-priority fallback
            if escaped_for_literal:
                add_strategy(f'@name:"{escaped_for_literal}"')

            all_results = []
            # Allow multiple entries per linked; dedupe by (linked, display_name)
            seen_linked_display = set()
            def _norm(v):
                return str(v).strip().casefold() if v is not None else ""

            for strategy in search_strategies:
                try:
                    full_query = f'@iso:{{{iso}}} {strategy}' if iso else strategy
                    result = self.client.execute_command(
                        "FT.SEARCH", self.index_name, full_query, "LIMIT", 0, limit * 2
                    )

                    if result and len(result) > 1:
                        processed_result = self._process_search(result)
                        for item in processed_result:
                            linked = _norm(item.get("linked"))
                            iso_val = _norm(item.get(iso)) if iso else ""

                            if iso and linked and iso_val:
                                combo = (linked, iso_val)
                                if combo in seen_linked_display:
                                    continue
                                all_results.append(item)
                                seen_linked_display.add(combo)
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

            sort_key_query = " ".join(words) if words else query
            all_results = self._sort_by_relevance(all_results, sort_key_query, iso)
            all_results = self._dedupe_linked_iso(all_results, iso)

            all_results = self._sort_by_relevance(all_results, query, iso)
            return all_results[:limit]

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
            terms = [w for w in re.findall(r'[\w/]+', query_lower) if w]
            
            for item in all_items:
                name = item.get("name", "").lower()
                display_name = item.get("display_name", "").lower()
                
                if (query_lower in name or 
                    query_lower in display_name or
                    any(term in name for term in terms if len(term) > 1) or
                    any(term in display_name for term in terms if len(term) > 1)):
                    fuzzy_matches.append(item)
            
            fuzzy_matches = self._sort_by_relevance(fuzzy_matches, query, iso)
            fuzzy_matches = self._dedupe_linked_iso(fuzzy_matches, iso)
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
            
            all_results = []
            seen_linked_display = set()
            def _norm(v):
                return str(v).strip().casefold() if v is not None else ""
            
            for strategy in search_strategies:
                try:
                    full_query = f'@iso:{{{iso}}} {strategy}' if iso else strategy
                    result = self.client.execute_command(
                        "FT.SEARCH", self.index_name, full_query, "LIMIT", 0, limit * 2
                    )
                    
                    if result and len(result) > 1:
                        processed_result = self._process_search(result)
                        for item in processed_result:
                            linked = _norm(item.get("linked"))
                            display_name = _norm(item.get("display_name"))
                            if linked and display_name:
                                combo = (linked, display_name)
                                if combo in seen_linked_display:
                                    continue
                                all_results.append(item)
                                seen_linked_display.add(combo)
                            else:
                                all_results.append(item)
                                
                except Exception as e:
                    print(f"Single char strategy '{strategy}' failed: {e}")
                    continue
            
            # Sort by relevance
            all_results = self._sort_by_relevance(all_results, query, iso)
            all_results = self._dedupe_linked_iso(all_results, iso)

            # Deduplicate by name - keep only first occurrence of each name
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

    def _dedupe_linked_iso(self, results, iso=None):
        """Remove duplicates by (linked, iso) combination."""
        if not iso:
            return results

        seen = set()
        deduped = []

        for item in results:
            linked = item.get("linked")
            if not linked:
                deduped.append(item)
                continue

            iso_value = item.get(iso, "")
            norm_value = iso_value.strip().casefold() if isinstance(iso_value, str) else ""
            key = (linked, iso, norm_value)

            if key in seen:
                continue
            seen.add(key)
            deduped.append(item)

        return deduped

    def _sort_by_relevance(self, results, query, iso=None):
        """Sort results by relevance to the query."""
        normalized = query.strip().lower()

        def sort_key(item):
            name = item.get("name", "")
            display_name = item.get("display_name", "")
            iso_value = ""

            if iso:
                iso_raw = item.get(iso, "")
                iso_value = iso_raw.strip().lower() if isinstance(iso_raw, str) else ""

            name_lower = name.strip().lower() if isinstance(name, str) else ""
            display_lower = display_name.strip().lower() if isinstance(display_name, str) else ""

            # Highest priority: exact match on ISO-specific value
            if iso_value and iso_value == normalized:
                return (0, 0)

            # Exact match on display_name or name
            if display_lower == normalized or name_lower == normalized:
                return (1, 0)

            # Startswith checks
            if iso_value and iso_value.startswith(normalized):
                return (2, 0)
            if display_lower.startswith(normalized):
                return (3, 0)
            if name_lower.startswith(normalized):
                return (4, 0)

            # Contains checks
            if iso_value and normalized in iso_value:
                return (5, 0)
            if normalized in display_lower:
                return (6, 0)
            if normalized in name_lower:
                return (7, 0)

            # Fallback
            return (8, 0)

        results.sort(key=sort_key)
        return results

    def _build_query_variants(self, query):
        """Build multiple search query variants for better results."""
        query = query.strip().lower()
        query = re.sub(r'\s+', ' ', query)
        escaped = self._escape_special_chars(query)
        parts = re.findall(r'[\w/]+', query)
        escaped_parts = [self._escape_special_chars(p) for p in parts]

        # For short words (≤3 chars), avoid exact match to prevent syntax errors
        if len(query) <= 3:
            variants = [
                f'@name:*{escaped}*',      # Contains (safe for short words)
                f'@name:{escaped}*'        # Starts with (safe for short words)
            ] + [f'@name:*{ep}*' for ep in escaped_parts]  # Individual words
        else:
            variants = [
                f'@name:"{escaped}"',      # Exact match (only for longer words)
                f'@name:*{escaped}*',      # Contains
                f'@name:{escaped}*'        # Starts with
            ] + [f'@name:*{ep}*' for ep in escaped_parts]  # Individual words

        return variants

    def _escape_special_chars(self, text):
        """Escape special characters for RediSearch queries."""
        return re.sub(r'([,.<>"\':;!@#$%^&\-+=~|/\\\[\]\(\)\{\}])', r'\\\1', text)

    def _escape_tag_value(self, text):
        """Escape special characters (including spaces) for RediSearch TAG queries."""
        if not text:
            return ""
        specials = {
            '\\': "\\\\",
            ',': "\\,",
            '.': "\\.",
            '<': "\\<",
            '>': "\\>",
            '{': "\\{",
            '}': "\\}",
            '[': "\\[",
            ']': "\\]",
            '"': '\\"',
            '\'': "\\'",
            ':': "\\:",
            '|': "\\|",
            '=': "\\=",
            '&': "\\&",
            '/': "\\/",
        }
        escaped = []
        for ch in text:
            if ch == ' ':
                escaped.append('\\ ')
            else:
                escaped.append(specials.get(ch, ch))
        return "".join(escaped)

    def _normalize_sku_across_translations(self, items):
        """Ensure stable sku per linked group (prefer 'en' variant's sku if available)."""
        grouped = {}
        for it in items:
            lk = it.get("linked")
            if not lk:
                continue
            grouped.setdefault(lk, []).append(it)

        for lk, group in grouped.items():
            canonical = None
            fallback = None
            for it in group:
                if not fallback and it.get("sku"):
                    fallback = it.get("sku")
                if 'en' in it and it.get("sku"):
                    canonical = it.get("sku")
                    break
            canonical_sku = canonical or fallback
            if canonical_sku:
                for it in group:
                    it["sku"] = canonical_sku
        return items

    def _process_search(self, search_results):
        """Efficiently parse Redis search results into structured dicts."""
        structured_data = []
        
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
                    
                    # Skip if we've already seen this linked value
                    doc_display_name = json_data.get("display_name", "")
                    iso_display_name = json_data.get(iso_value, "")
                    
                    structured_item = {
                        #"sku": product_id,
                        "name": json_data.get("name", ""),
                        "display_name": iso_display_name or doc_display_name,
                        #"display_name_exact": json_data.get("display_name_exact", doc_display_name),
                        #"origin_name": json_data.get("origin_name", ""),
                        "slug": json_data.get("slug", ""),
                        "linked": linked_value
                    }

                    if iso_value:
                        structured_item["iso"] = iso_value
                        structured_item[iso_value] = iso_display_name or doc_display_name

                    structured_data.append(structured_item)
                    
                except (json.JSONDecodeError, IndexError) as e:
                    print(f"Error decoding Redis JSON at index {i}: {str(e)}")
                    continue

        # Filter out items with null names before returning
        filtered_results = []
        for item in structured_data:
                filtered_results.append(item)
            # if item and item.get("name") is not None:  # Skip null names
            #     filtered_results.append(item)

        return filtered_results

    def _clean_query(self, query):
        """Escape special characters for literal RediSearch attempts."""
        # Remove extra whitespace
        query = re.sub(r'\s+', ' ', query.strip())
        # Escape special characters for literal phrase query
        special_chars = r'([,.<>"\':;!@#$%^&\-+=~|/\\\[\]\(\)\{\}])'
        return re.sub(special_chars, r'\\\1', query)

    def _search_exact_display_name(self, query, iso=None, limit=30):
        tag_value = self._escape_tag_value(query.strip())
        if not tag_value:
            return []

        exact_results = []
        tag_queries = [
            f'@display_name_exact:{{{tag_value}}}',
            f'@display_name_exact:{{{tag_value}*}}'
        ]

        for tq in tag_queries:
            try:
                full_query = f'@iso:{{{iso}}} {tq}' if iso else tq
                result = self.client.execute_command(
                    "FT.SEARCH", self.index_name, full_query, "LIMIT", 0, limit * 2
                )
                if result and len(result) > 1:
                    exact_results.extend(self._process_search(result))
            except Exception as e:
                print(f"Exact tag query '{tq}' failed: {e}")

        if not exact_results:
            return []

        exact_results = self._sort_by_relevance(exact_results, query, iso)
        exact_results = self._dedupe_linked_iso(exact_results, iso)
        return exact_results[:limit]
