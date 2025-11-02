from os import environ, path
import os
import json

from bson import ObjectId
from dotenv import load_dotenv
from pymongo import MongoClient
from services.boxes_redisearch_service import RedisearchService as BoxRedisearchService

load_dotenv()


class DataSetService:
    def __init__(self):
        self.mongo_uri = os.environ.get("HOST", '0.0.0.0')
        self.db_name = os.environ.get("DB_NAME", 'admin')
        self.box_coll = "boxes"
        self.supplier_box_coll = "supplier_boxes"
        self.data_set_file = environ.get("DATA_SET_FILE", "data_set.json")
        self.redis_service = BoxRedisearchService()

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
        """Fetch boxes from MongoDB and store them in `data_set.json`."""
        box_service = BoxRedisearchService()
        box_service.create_index()

        self.clear_data_set()
        self.ensure_data_set_file()
        client = self.get_mongo_connection()
        if not client:
            return {"error": "Could not connect to MongoDB"}

        db = client[self.db_name]
        box_collection = db[self.box_coll]
        supplier_box_collection = db[self.supplier_box_coll]
        response = []

        try:
            boxes_data_set = {}
            boxes = list(box_collection.find())
            names_dic = {}
            origin_names_dic = {}

            for box in boxes:
                sku = str(box.get("_id"))  # Use _id
                display_names = box.get("display_name", [])
                origin_names = box.get("display_name", [])

                name_list = self.extract_names(display_names)
                origin_name_list = self.extract_origin_names(origin_names)  # Extract origin names
                slug = box.get("slug", "")

                if sku:
                    boxes_data_set[sku] = {
                        "names": name_list,
                        "origin_names": origin_name_list,  # Store origin names inside same structure
                        "slug": slug
                    }
                names_dic[sku] = name_list
                origin_names_dic[sku] = origin_name_list

            with open(self.data_set_file, "w", encoding="utf-8") as file:
                json.dump(boxes_data_set, file, indent=4, ensure_ascii=False)

            # Insert basic boxes with the box itself as origin
            for sku, data in boxes_data_set.items():
                # Find the original box to get the name
                origin_box = box_collection.find_one({"_id": ObjectId(sku)})
                if origin_box:
                    self.insert_into_redis({sku: data}, origin_box)

            response.append(boxes_data_set)

        except Exception as e:
            print(f"error box {str(e)}")

        try:
            supplier_boxes_data_set = {}
            supplier_boxes = list(supplier_box_collection.find({"linked": {"$exists": True, "$ne": None}}))
            # print("supplier_boxes", supplier_boxes)
            names_dic = {}
            origin_names_dic = {}

            if not supplier_boxes:
                print("No supplier boxes found!")

            for box in supplier_boxes:
                try:
                    sku = str(box.get("linked", ""))  # Use linked
                    
                    # Skip if sku is empty or invalid
                    if not sku or sku.strip() == "":
                        print(f"Skipping box with empty linked value: {box.get('_id', 'unknown')}")
                        continue
                    
                    # Validate ObjectId format
                    try:
                        ObjectId(sku)
                    except (TypeError, ValueError) as e:
                        print(f"Skipping box with invalid ObjectId '{sku}': {e}")
                        continue
                    
                    display_names = box.get("display_name", [])

                    origin_box = box_collection.find_one({"_id": ObjectId(sku)})
                    
                    # Skip if origin_box is None
                    if not origin_box:
                        print(f"Origin box not found for SKU {sku}, skipping...")
                        continue
                    
                    slug = str(origin_box.get("slug", ""))
                    origin_names = box.get("display_name", [])
                    name_list = self.extract_names(display_names)
                    origin_name_list = self.extract_origin_names(origin_names)  # Extract origin names

                    if sku:
                        supplier_boxes_data_set[sku] = {
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
                    existing_data.update(supplier_boxes_data_set)

                    # Write the updated data back to the file
                    with open(self.data_set_file, "w", encoding="utf-8") as file:
                        json.dump(existing_data, file, indent=4, ensure_ascii=False)

                    print("DataSet JSON updated successfully!")

                    # Pass origin_box to get the actual name from collection
                    self.insert_into_redis({sku: supplier_boxes_data_set[sku]}, origin_box)

                    response.append(supplier_boxes_data_set)
                    
                except Exception as e:
                    print(f"Error processing supplier box {box.get('_id', 'unknown')}: {e}, skipping...")
                    continue

        except Exception as e:
            print(f"error supplier box {str(e)}")

        return {"data-set": response or []}

    def insert_into_redis(self, data_set, origin_box=None):
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
                
                # Get origin_name from origin box's display_name array for this iso
                # If iso not in origin, use the current display_name
                origin_name = ""
                if origin_box and origin_box.get("display_name"):
                    try:
                        origin_name = next((entry.get("display_name", "") for entry in origin_box.get("display_name", []) if isinstance(entry, dict) and entry.get("iso") == iso), "")
                    except (StopIteration, TypeError):
                        pass
                
                # Fallback to current display_name if origin doesn't have this iso
                if not origin_name:
                    origin_name = display_name
                
                key = f"box:{sku}:{iso}:{display_name}"

                # Check if existing data is in Redis
                existing_data = self.redis_service.client.json().get(key)

                if existing_data:
                    print(f"Box already exists for {key}. Skipping insertion.")
                    continue

                # Define linked inside the loop scope where sku is available
                linked = data.get("linked", sku)

                # Get the main collection name
                main_name = origin_box.get("name", "") if origin_box else ""
                
                product_data = {
                    "sku": sku,
                    "name": main_name,  # Main collection name (single string)
                    "display_name": display_name,  # Localized display_name for this iso
                    "origin_name": origin_name,  # Origin's display_name for this iso, or fallback to supplier's
                    "iso": iso,
                    "slug": slug,
                    "linked": linked
                }
                self.redis_service.client.json().set(key, "$", product_data)

        print("Boxes inserted into Redis successfully!")

    def clear_data_set(self):
        """Clear previous box index and JSON file before regeneration."""
        # Drop only the `idx:box` index and its data
        try:
            self.redis_service.client.execute_command("FT.DROPINDEX", "idx:box", "DD")
            print("Index idx:box dropped successfully.")
        except Exception as e:
            if "Unknown Index name" in str(e):
                print("Index idx:box does not exist. Skipping drop.")
            else:
                raise
        self.redis_service.create_index()

    def add_single_box(self, box_data):
        """Add a single box directly to Redis search engine."""
        try:
            # Process the box data similar to generate_data_set
            processed_box = self.process_box_data(box_data)

            # Add to Redis using the same logic as generate_data_set
            self.insert_single_box_to_redis(processed_box)

            return {
                "message": "Box added successfully",
                "box_id": processed_box.get("_id"),
                "status": "success"
            }
        except Exception as e:
            return {
                "message": f"Failed to add box: {str(e)}",
                "status": "error",
                "error": str(e)
            }

    def process_box_data(self, box_data):
        """Process box data for Redis storage."""
        try:
            # Use linked as the SKU
            sku = box_data.get("linked", "")
            
            # Extract names and origin names
            names = self.extract_names(box_data.get("display_names", []))
            # Handle both origin_names (plural) and origin_name (singular) for backward compatibility
            origin_names_data = box_data.get("origin_names", [])
            if not origin_names_data and box_data.get("origin_name"):
                # If origin_name is provided as a string, convert it to the expected format
                origin_name_str = box_data.get("origin_name", "")
                origin_names_data = [{"iso": "en", "display_name": origin_name_str}]
            origin_names = self.extract_origin_names(origin_names_data)

            # Process the box - no _id needed, use linked
            processed_box = {
                "_id": str(sku),  # Use linked as _id
                "slug": box_data.get("slug", ""),
                "names": names,
                "origin_names": origin_names,
                "display_names": names,  # Add this for backward compatibility
                "origin_name": box_data.get("origin_name", ""),  # Keep original for reference
                "linked": box_data.get("linked", ""),  # Preserve linked field from payload
                "created_at": box_data.get("created_at", ""),
                "updated_at": box_data.get("updated_at", "")
            }

            return processed_box
        except Exception as e:
            raise Exception(f"Error processing box data: {str(e)}")

    def insert_single_box_to_redis(self, box_data):
        """Insert a single box into Redis using the same logic as generate_data_set."""
        try:
            # Use linked as the SKU (from _id field which now contains linked)
            sku = box_data.get("_id", "")
            slug = box_data.get("slug", "")
            names = box_data.get("names", [])
            origin_names = box_data.get("origin_names", [])
            linked = box_data.get("linked", sku)  # Preserve linked from payload
            main_name = box_data.get("origin_name", "")  # Use origin_name from payload as main name

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
                
                key = f"box:{sku}:{iso}:{display_name}"

                # Check if existing data is in Redis
                existing_data = self.redis_service.client.json().get(key)

                if existing_data:
                    print(f"Box already exists for {key}. Skipping insertion.")
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

            print(f"Box {sku} inserted into Redis successfully!")

        except Exception as e:
            raise Exception(f"Error inserting box to Redis: {str(e)}")

    def delete_single_box(self, linked, display_names):
        """
        Delete box entries from Redis search engine based on linked and display_names.
        
        Args:
            linked: The linked value (box SKU/origin ID)
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
                
                # Build the exact key to delete: box:{linked}:{iso}:{display_name}
                key = f"box:{linked}:{iso}:{display_name}"
                
                # Check if key exists and delete it
                existing_data = self.redis_service.client.json().get(key)
                if existing_data:
                    self.redis_service.client.delete(key)
                    deleted_keys.append(key)
                    print(f"Deleted box key: {key}")

            if deleted_keys:
                print(f"Box deletion completed. Deleted {len(deleted_keys)} keys for linked={linked}.")
                return {
                    "message": "Box entries deleted successfully",
                    "linked": linked,
                    "deleted_keys": deleted_keys,
                    "deleted_count": len(deleted_keys),
                    "status": "success"
                }
            else:
                return {
                    "message": "No matching box entries found in search engine",
                    "linked": linked,
                    "deleted_count": 0,
                    "status": "not_found"
                }

        except Exception as e:
            return {
                "message": f"Failed to delete box: {str(e)}",
                "status": "error",
                "error": str(e)
            }

