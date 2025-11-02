from os import environ, path
import os
import json

from bson import ObjectId
from dotenv import load_dotenv
from pymongo import MongoClient
from services.redisearch_service import RedisearchService

load_dotenv()


class DataSetService:
    def __init__(self):
        self.mongo_uri = os.environ.get("HOST", '0.0.0.0')
        self.db_name = os.environ.get("DB_NAME", 'admin')
        self.category_coll = "categories"
        self.supplier_category_coll = "supplier_categories"
        self.data_set_file = environ.get("DATA_SET_FILE", "data_set.json")
        self.redis_service = RedisearchService()

    def get_mongo_connection(self):
        """Establish connection to MongoDB."""
        try:
            client = MongoClient(self.mongo_uri)
            client.admin.command("ping")  # Test the connection
            return client
        except Exception as e:
            print(f"MongoDB Connection Error: {e}")
            return None

    def ensure_data_set_file(self):
        """Ensure the dataset file exists, otherwise create an empty one."""
        if not os.path.exists(self.data_set_file):
            print(f"{self.data_set_file} not found. Creating a new one...")
            with open(self.data_set_file, "w", encoding="utf-8") as file:
                json.dump({}, file, indent=4, ensure_ascii=False)

    def extract_names(self, display_names):
        """Extract display names with associated ISO codes."""
        if isinstance(display_names, list):
            return [{"iso": name.get("iso", "en"), "display_name": name.get("display_name", "")}
                    for name in display_names if isinstance(name, dict)]
        return []

    def extract_origin_names(self, origin_names):
        """Extract display names with associated ISO codes."""
        if isinstance(origin_names, list):
            return [{"iso": name.get("iso", "en"), "display_name": name.get("display_name", "")}
                    for name in origin_names if isinstance(name, dict)]
        return []

    def generate_data_set(self):
        """Fetch categories from MongoDB and store them in `data_set.json`."""
        categories_service = RedisearchService()
        categories_service.create_index()

        self.clear_data_set()
        self.ensure_data_set_file()
        client = self.get_mongo_connection()
        if not client:
            return {"error": "Could not connect to MongoDB"}

        db = client[self.db_name]
        category_collection = db[self.category_coll]
        supplier_category_collection = db[self.supplier_category_coll]
        response = []

        try:
            categories_data_set = {}
            categories = list(category_collection.find())
            names_dic = {}
            origin_names_dic = {}

            for category in categories:
                sku = str(category.get("_id"))  # Use _id
                display_names = category.get("display_name", [])
                origin_names = category.get("display_name", [])
                slug = str(category.get("slug"))

                name_list = self.extract_names(display_names)
                origin_name_list = self.extract_origin_names(origin_names)  # Extract origin names

                if sku:
                    categories_data_set[sku] = {
                        "names": name_list,
                        "origin_names": origin_name_list,  # Store origin names inside same structure
                        "slug": slug  # Store Slug
                    }
                names_dic[sku] = name_list
                origin_names_dic[sku] = origin_name_list

            with open(self.data_set_file, "w", encoding="utf-8") as file:
                json.dump(categories_data_set, file, indent=4, ensure_ascii=False)

            # Insert basic categories with the category itself as origin
            for sku, data in categories_data_set.items():
                # Find the original category to get the name
                origin_category = category_collection.find_one({"_id": ObjectId(sku)})
                if origin_category:
                    self.insert_into_redis({sku: data}, origin_category)

            response.append(categories_data_set)

        except Exception as e:
            print(f"error category {str(e)}")

        try:
            supplier_categories_data_set = {}
            supplier_categories = list(supplier_category_collection.find({"linked": {"$exists": True, "$ne": None}}))
            # print("supplier_categories", supplier_categories)
            names_dic = {}
            origin_names_dic = {}

            if not supplier_categories:
                print("No supplier categories found!")

            for category in supplier_categories:
                try:
                    sku = str(category.get("linked", ""))  # Use linked
                    
                    # Skip if sku is empty or invalid
                    if not sku or sku.strip() == "":
                        print(f"Skipping category with empty linked value: {category.get('_id', 'unknown')}")
                        continue
                    
                    # Validate ObjectId format
                    try:
                        ObjectId(sku)
                    except (TypeError, ValueError) as e:
                        print(f"Skipping category with invalid ObjectId '{sku}': {e}")
                        continue
                    
                    display_names = category.get("display_name", [])

                    origin_category = category_collection.find_one({"_id": ObjectId(sku)})
                    
                    # Skip if origin_category is None
                    if not origin_category:
                        print(f"Origin category not found for SKU {sku}, skipping...")
                        continue
                    
                    slug = str(origin_category.get("slug", ""))
                    origin_names = category.get("display_name", [])
                    name_list = self.extract_names(display_names)
                    origin_name_list = self.extract_origin_names(origin_names)  # Extract origin names

                    if sku:
                        supplier_categories_data_set[sku] = {
                            "names": name_list,
                            "origin_names": origin_name_list,
                            "slug": slug,
                        }

                    names_dic[sku] = name_list
                    origin_names_dic[sku] = origin_name_list

                    if os.path.exists(self.data_set_file):
                        try:
                            with open(self.data_set_file, "r", encoding="utf-8") as file:
                                existing_data = json.load(file)  # Load existing JSON
                        except json.JSONDecodeError:
                            existing_data = {}  # If the file is empty or invalid, start fresh
                    else:
                        existing_data = {}

                    # Merge existing data with new data
                    existing_data.update(supplier_categories_data_set)

                    # Write the updated data back to the file
                    with open(self.data_set_file, "w", encoding="utf-8") as file:
                        json.dump(existing_data, file, indent=4, ensure_ascii=False)

                    print("DataSet JSON updated successfully!")

                    # Pass origin_category to get the actual name from collection
                    self.insert_into_redis({sku: supplier_categories_data_set[sku]}, origin_category)

                    response.append(supplier_categories_data_set)
                    
                except Exception as e:
                    print(f"Error processing supplier category {category.get('_id', 'unknown')}: {e}, skipping...")
                    continue

        except Exception as e:
            print(f"error supplier category {str(e)}")

        return {"data-set": response or []}

    def insert_into_redis(self, data_set, origin_category=None):
        """Insert products into Redis while preventing data loss by merging existing data."""
        print("Inserting data into Redis...")

        for sku, data in data_set.items():
            names = data.get("names", [])
            origin_names = data.get("origin_names", [])
            slug = data.get("slug", "")

            # Ensure names and origin_names are lists
            if not isinstance(names, list):
                names = []
            if not isinstance(origin_names, list):
                origin_names = []

            for name_entry in names:
                if not isinstance(name_entry, dict):
                    continue
                    
                iso = name_entry.get("iso", "en")
                display_name = name_entry.get("display_name", "")
                
                # Get origin_name: prefer from origin collection's display_name for this iso
                # If iso not in origin, fallback to supplier's display_name
                origin_name = ""
                if origin_category and origin_category.get("display_name"):
                    try:
                        origin_name = next((entry.get("display_name", "") for entry in origin_category.get("display_name", []) if isinstance(entry, dict) and entry.get("iso") == iso), "")
                    except (StopIteration, TypeError):
                        pass
                
                # Fallback to supplier's display_name if origin doesn't have this iso
                if not origin_name:
                    origin_name = display_name
                
                key = f"category:{sku}:{iso}:{display_name}"

                # Define linked inside the loop scope where sku is available
                linked = data.get("linked", sku)

                # Check if existing data is in Redis
                existing_data = self.redis_service.client.json().get(key)

                if existing_data:
                    print(f"Category already exists for {key}. Skipping insertion.")
                    continue

                # Get the main collection name
                main_name = origin_category.get("name", "") if origin_category else ""
                
                product_data = {
                    "sku": sku,
                    "name": main_name,  # Main collection name (single string, e.g., "Letterhead333")
                    "display_name": display_name,  # Localized display_name for this iso
                    "origin_name": origin_name,  # Origin's display_name for this iso, or fallback to supplier's
                    "iso": iso,
                    "slug": slug,
                    "linked": linked,
                }
                self.redis_service.client.json().set(key, "$", product_data)

        print("Products inserted into Redis successfully!")

    def clear_data_set(self):
        """Clear previous data from data_set.json and Redis before regeneration."""
        with open(self.data_set_file, "w", encoding="utf-8") as file:
            json.dump({}, file)

        print("DataSet JSON cleared.")
        self.redis_service.client.flushdb()
        print("Redis database cleared.")
        redis_service = RedisearchService()
        redis_service.create_index()

    def add_single_category(self, category_data):
        """Add a single category directly to Redis search engine."""
        try:
            # Process the category data similar to generate_data_set
            processed_category = self.process_category_data(category_data)

            # Add to Redis using the same logic as generate_data_set
            self.insert_single_category_to_redis(processed_category)

            return {
                "message": "Category added successfully",
                "category_id": processed_category.get("_id"),
                "status": "success"
            }
        except Exception as e:
            return {
                "message": f"Failed to add category: {str(e)}",
                "status": "error",
                "error": str(e)
            }

    def process_category_data(self, category_data):
        """Process category data for Redis storage."""
        try:
            # Use linked as the SKU
            sku = category_data.get("linked", "")
            
            # Extract names and origin names
            names = self.extract_names(category_data.get("display_names", []))
            # Handle both origin_names (plural) and origin_name (singular) for backward compatibility
            origin_names_data = category_data.get("origin_names", [])
            if not origin_names_data and category_data.get("origin_name"):
                # If origin_name is provided as a string, convert it to the expected format
                origin_name_str = category_data.get("origin_name", "")
                origin_names_data = [{"iso": "en", "display_name": origin_name_str}]
            origin_names = self.extract_origin_names(origin_names_data)

            # Process the category - no _id needed, use linked
            processed_category = {
                "_id": str(sku),  # Use linked as _id
                "slug": category_data.get("slug", ""),
                "names": names,
                "origin_names": origin_names,
                "display_names": names,  # Add this for backward compatibility
                "origin_name": category_data.get("origin_name", ""),  # Keep original for reference
                "linked": category_data.get("linked", ""),  # Preserve linked field from payload
                "created_at": category_data.get("created_at", ""),
                "updated_at": category_data.get("updated_at", "")
            }

            return processed_category
        except Exception as e:
            raise Exception(f"Error processing category data: {str(e)}")

    def insert_single_category_to_redis(self, category_data):
        """Insert a single category into Redis using the same logic as generate_data_set."""
        try:
            # Use linked as the SKU (from _id field which now contains linked)
            sku = category_data.get("_id", "")
            slug = category_data.get("slug", "")
            names = category_data.get("names", [])
            origin_names = category_data.get("origin_names", [])
            linked = category_data.get("linked", sku)  # Preserve linked from payload
            main_name = category_data.get("origin_name", "")  # Use origin_name from payload as main name

            # Ensure names and origin_names are lists
            if not isinstance(names, list):
                names = []
            if not isinstance(origin_names, list):
                origin_names = []

            for name_entry in names:
                if not isinstance(name_entry, dict):
                    continue
                    
                iso = name_entry.get("iso", "en")
                display_name = name_entry.get("display_name", "")
                
                # Get origin_name from origin_names array for this iso
                origin_name = ""
                if origin_names:
                    try:
                        origin_name = next((entry.get("display_name", "") for entry in origin_names if isinstance(entry, dict) and entry.get("iso") == iso), "")
                    except (StopIteration, TypeError):
                        origin_name = ""
                
                # Fallback to main_name if origin doesn't have this iso
                if not origin_name:
                    origin_name = main_name
                
                key = f"category:{sku}:{iso}:{display_name}"

                # Check if existing data is in Redis
                existing_data = self.redis_service.client.json().get(key)

                if existing_data:
                    print(f"Category already exists for {key}. Skipping insertion.")
                    continue

                product_data = {
                    "sku": sku,
                    "name": main_name,  # Main collection name (origin_name from payload)
                    "display_name": display_name,  # Localized display_name for this iso
                    "origin_name": origin_name,  # Origin's display_name for this iso, or fallback to main_name
                    "iso": iso,
                    "slug": slug,
                    "linked": linked,  # Use linked from payload
                }
                self.redis_service.client.json().set(key, "$", product_data)

            print(f"Category {sku} inserted into Redis successfully!")

        except Exception as e:
            raise Exception(f"Error inserting category to Redis: {str(e)}")

    def delete_single_category(self, linked, display_names):
        """
        Delete category entries from Redis search engine based on linked and display_names.
        
        Args:
            linked: The linked value (category SKU/origin ID)
            display_names: List of objects with iso and display_name, e.g.
                [
                    {"iso": "en", "display_name": "Briefpapier"},
                    {"iso": "fr", "display_name": "Briefpapier"},
                    ...
                ]
        """
        try:
            deleted_keys = []
            
            # Ensure display_names is a list
            if not isinstance(display_names, list):
                display_names = []

            # For each display_name entry, delete the matching Redis key
            for entry in display_names:
                if not isinstance(entry, dict):
                    continue
                    
                iso = entry.get("iso", "")
                display_name = entry.get("display_name", "")
                
                if not iso or not display_name:
                    continue
                
                # Build the exact key to delete: category:{linked}:{iso}:{display_name}
                key = f"category:{linked}:{iso}:{display_name}"
                
                # Check if key exists and delete it
                existing_data = self.redis_service.client.json().get(key)
                if existing_data:
                    self.redis_service.client.delete(key)
                    deleted_keys.append(key)
                    print(f"Deleted category key: {key}")

            if deleted_keys:
                print(f"Category deletion completed. Deleted {len(deleted_keys)} keys for linked={linked}.")
                return {
                    "message": "Category entries deleted successfully",
                    "linked": linked,
                    "deleted_keys": deleted_keys,
                    "deleted_count": len(deleted_keys),
                    "status": "success"
                }
            else:
                return {
                    "message": "No matching category entries found in search engine",
                    "linked": linked,
                    "deleted_count": 0,
                    "status": "not_found"
                }

        except Exception as e:
            return {
                "message": f"Failed to delete category: {str(e)}",
                "status": "error",
                "error": str(e)
            }
