from os import environ, path
import os
import json

from bson import ObjectId
from dotenv import load_dotenv
from pymongo import MongoClient
from services.options_redisearch_service import RedisearchService as OptionRedisearchService

load_dotenv()


class DataSetService:
    def __init__(self):
        self.mongo_uri = os.environ.get("HOST", '0.0.0.0')
        self.db_name = os.environ.get("DB_NAME", 'admin')
        self.option_coll = "options"
        self.supplier_option_coll = "supplier_options"
        self.data_set_file = environ.get("DATA_SET_FILE", "data_set.json")
        self.redis_service = OptionRedisearchService()

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
        """Fetch options from MongoDB and store them in `data_set.json`."""
        option_service = OptionRedisearchService()
        option_service.create_index()

        self.clear_data_set()
        self.ensure_data_set_file()
        client = self.get_mongo_connection()
        if not client:
            return {"error": "Could not connect to MongoDB"}

        db = client[self.db_name]
        option_collection = db[self.option_coll]
        supplier_option_collection = db[self.supplier_option_coll]
        response = []

        try:
            options_data_set = {}
            options = list(option_collection.find())
            names_dic = {}
            origin_names_dic = {}

            for option in options:
                sku = str(option.get("_id"))  # Use _id
                display_names = option.get("display_name", [])
                origin_names = option.get("display_name", [])
                slug = str(option.get("slug"))

                name_list = self.extract_names(display_names)
                origin_name_list = self.extract_origin_names(origin_names)  # Extract origin names

                if sku:
                    options_data_set[sku] = {
                        "names": name_list,
                        "origin_names": origin_name_list,  # Store origin names inside same structure
                        "slug": slug
                    }
                names_dic[sku] = name_list
                origin_names_dic[sku] = origin_name_list

            with open(self.data_set_file, "w", encoding="utf-8") as file:
                json.dump(options_data_set, file, indent=4, ensure_ascii=False)

            # Insert basic options with the option itself as origin
            for sku, data in options_data_set.items():
                # Find the original option to get the name
                origin_option = option_collection.find_one({"_id": ObjectId(sku)})
                if origin_option:
                    self.insert_into_redis({sku: data}, origin_option)

            response.append(options_data_set)

        except Exception as e:
            print(f"error option {str(e)}")

        try:
            supplier_options_data_set = {}
            supplier_options = list(supplier_option_collection.find({"linked": {"$exists": True, "$ne": None}}))
            # print("supplier_options", supplier_options)
            names_dic = {}
            origin_names_dic = {}

            if not supplier_options:
                print("No supplier options found!")

            for option in supplier_options:
                try:
                    sku = str(option.get("linked", ""))  # Use linked
                    
                    # Skip if sku is empty or invalid
                    if not sku or sku.strip() == "":
                        print(f"Skipping option with empty linked value: {option.get('_id', 'unknown')}")
                        continue
                    
                    # Validate ObjectId format
                    try:
                        ObjectId(sku)
                    except (TypeError, ValueError) as e:
                        print(f"Skipping option with invalid ObjectId '{sku}': {e}")
                        continue
                    
                    display_names = option.get("display_name", [])

                    origin_option = option_collection.find_one({"_id": ObjectId(sku)})
                    
                    # Skip if origin_option is None
                    if not origin_option:
                        print(f"Origin option not found for SKU {sku}, skipping...")
                        continue
                    
                    slug = str(origin_option.get("slug", ""))
                    origin_names = option.get("display_name", [])
                    name_list = self.extract_names(display_names)
                    origin_name_list = self.extract_origin_names(origin_names)  # Extract origin names

                    if sku:
                        supplier_options_data_set[sku] = {
                            "names": name_list,
                            "origin_names": origin_name_list,
                            "slug": slug
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
                    existing_data.update(supplier_options_data_set)

                    # Write the updated data back to the file
                    with open(self.data_set_file, "w", encoding="utf-8") as file:
                        json.dump(existing_data, file, indent=4, ensure_ascii=False)

                    print("DataSet JSON updated successfully!")

                    # Pass origin_option to get the actual name from collection
                    self.insert_into_redis({sku: supplier_options_data_set[sku]}, origin_option)

                    response.append(supplier_options_data_set)
                    
                except Exception as e:
                    print(f"Error processing supplier option {option.get('_id', 'unknown')}: {e}, skipping...")
                    continue

        except Exception as e:
            print(f"error supplier option {str(e)}")

        return {"data-set": response or []}

    def insert_into_redis(self, data_set, origin_option=None):
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
                
                # Get origin_name from origin option's display_name array for this iso
                # If iso not in origin, use the current display_name
                origin_name = ""
                if origin_option and origin_option.get("display_name"):
                    try:
                        origin_name = next((entry.get("display_name", "") for entry in origin_option.get("display_name", []) if isinstance(entry, dict) and entry.get("iso") == iso), "")
                    except (StopIteration, TypeError):
                        pass
                
                # Fallback to current display_name if origin doesn't have this iso
                if not origin_name:
                    origin_name = display_name
                
                key = f"option:{sku}:{iso}:{display_name}"

                # Check if existing data is in Redis
                existing_data = self.redis_service.client.json().get(key)

                if existing_data:
                    print(f"Option already exists for {key}. Skipping insertion.")
                    continue

                # Define linked inside the loop scope where sku is available
                linked = data.get("linked", sku)

                # Get the main collection name
                main_name = origin_option.get("name", "") if origin_option else ""
                
                product_data = {
                    "sku": sku,
                    "name": main_name,  # Main collection name (single string)
                    "display_name": display_name,  # Localized display_name for this iso
                    "display_name_exact": display_name,
                    "origin_name": origin_name,  # Origin's display_name for this iso, or fallback to supplier's
                    "iso": iso,
                    "slug": slug,
                    "linked": linked
                }
                # Store ISO-specific value explicitly for retrieval
                if iso:
                    product_data[iso] = display_name
                self.redis_service.client.json().set(key, "$", product_data)

        print("Options inserted into Redis successfully!")

    def clear_data_set(self):
        """Clear previous option index and JSON file before regeneration."""
        # Drop only the `idx:option` index and its documents
        try:
            self.redis_service.client.execute_command("FT.DROPINDEX", self.redis_service.index_name, "DD")
            print(f"Index {self.redis_service.index_name} dropped successfully.")
        except Exception as e:
            if "Unknown Index name" in str(e):
                print(f"Index {self.redis_service.index_name} does not exist. Skipping drop.")
            else:
                raise
        # Recreate the index
        self.redis_service.create_index()
        
        # Clear the data set file by writing empty content
        with open(self.data_set_file, "w", encoding="utf-8") as file:
            file.write("")
        print(f"Cleared data set file: {self.data_set_file}")

    def add_single_option(self, option_data):
        """Add a single option directly to Redis search engine."""
        try:
            # Process the option data similar to generate_data_set
            processed_option = self.process_option_data(option_data)

            # Add to Redis using the same logic as generate_data_set
            self.insert_single_option_to_redis(processed_option)

            return {
                "message": "Option added successfully",
                "option_id": processed_option.get("_id"),
                "status": "success"
            }
        except Exception as e:
            return {
                "message": f"Failed to add option: {str(e)}",
                "status": "error",
                "error": str(e)
            }

    def process_option_data(self, option_data):
        """Process option data for Redis storage."""
        try:
            # Use linked as the SKU
            sku = option_data.get("linked", "")
            
            # Extract names and origin names
            names = self.extract_names(option_data.get("display_names", []))
            # Handle both origin_names (plural) and origin_name (singular) for backward compatibility
            origin_names_data = option_data.get("origin_names", [])
            if not origin_names_data and option_data.get("origin_name"):
                # If origin_name is provided as a string, convert it to the expected format
                origin_name_str = option_data.get("origin_name", "")
                origin_names_data = [{"iso": "en", "display_name": origin_name_str}]
            origin_names = self.extract_origin_names(origin_names_data)

            # Process the option - no _id needed, use linked
            processed_option = {
                "_id": str(sku),  # Use linked as _id
                "slug": option_data.get("slug", ""),
                "names": names,
                "origin_names": origin_names,
                "display_names": names,  # Add this for backward compatibility
                "origin_name": option_data.get("origin_name", ""),  # Keep original for reference
                "linked": option_data.get("linked", ""),  # Preserve linked field from payload
                "created_at": option_data.get("created_at", ""),
                "updated_at": option_data.get("updated_at", "")
            }

            return processed_option
        except Exception as e:
            raise Exception(f"Error processing option data: {str(e)}")

    def insert_single_option_to_redis(self, option_data):
        """Insert a single option into Redis using the same logic as generate_data_set."""
        try:
            # Use linked as the SKU (from _id field which now contains linked)
            sku = option_data.get("_id", "")
            slug = option_data.get("slug", "")
            names = option_data.get("names", [])
            origin_names = option_data.get("origin_names", [])
            linked = option_data.get("linked", sku)  # Preserve linked from payload
            main_name = option_data.get("origin_name", "")  # Use origin_name from payload as main name

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
                
                key = f"option:{sku}:{iso}:{display_name}"

                # Check if existing data is in Redis
                existing_data = self.redis_service.client.json().get(key)

                if existing_data:
                    print(f"Option already exists for {key}. Skipping insertion.")
                    continue

                product_data = {
                    "sku": sku,
                    "name": main_name,  # Main collection name (origin_name from payload)
                    "display_name": display_name,  # Localized display_name for this iso
                    "display_name_exact": display_name,
                    "origin_name": origin_name,  # Origin's display_name for this iso, or fallback to main_name
                    "iso": iso,
                    "slug": slug,
                    "linked": linked,  # Use linked from payload
                }
                if iso:
                    product_data[iso] = display_name
                self.redis_service.client.json().set(key, "$", product_data)

            print(f"Option {sku} inserted into Redis successfully!")

        except Exception as e:
            raise Exception(f"Error inserting option to Redis: {str(e)}")

    def delete_single_option(self, linked, display_names):
        """
        Delete option entries from Redis search engine based on linked and display_names.
        
        Args:
            linked: The linked value (option SKU/origin ID)
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
                
                # Build the exact key to delete: option:{linked}:{iso}:{display_name}
                key = f"option:{linked}:{iso}:{display_name}"
                
                # Check if key exists and delete it
                existing_data = self.redis_service.client.json().get(key)
                if existing_data:
                    self.redis_service.client.delete(key)
                    deleted_keys.append(key)
                    print(f"Deleted option key: {key}")

            if deleted_keys:
                print(f"Option deletion completed. Deleted {len(deleted_keys)} keys for linked={linked}.")
                return {
                    "message": "Option entries deleted successfully",
                    "linked": linked,
                    "deleted_keys": deleted_keys,
                    "deleted_count": len(deleted_keys),
                    "status": "success"
                }
            else:
                return {
                    "message": "No matching option entries found in search engine",
                    "linked": linked,
                    "deleted_count": 0,
                    "status": "not_found"
                }

        except Exception as e:
            return {
                "message": f"Failed to delete option: {str(e)}",
                "status": "error",
                "error": str(e)
            }