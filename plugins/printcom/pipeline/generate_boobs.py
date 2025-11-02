import json

from bson import ObjectId
from config import DB_NAME, CATEGORY_COLL, BOX_COLL, OPTION_COLL, BOOBS_COLL
from database import get_mongo_connection
from slugify import slugify, Slugify, UniqueSlugify

class GenerateBoobsService:
    def __init__(self, client):
        """Initialize GenerateBoobsService with MongoDB client."""
        self.client = client
        self.db = client[DB_NAME]
        self.products_collection = self.db[CATEGORY_COLL]
        self.properties_collection = self.db[BOX_COLL]
        self.options_collection = self.db[OPTION_COLL]
        self.oops_collection = self.db[BOOBS_COLL]

    def store_all_in_oops(self, vendor="print.com"):
        """Retrieve products, properties, and options from DB where source = 'print.com',
        and store in 'oops' collection in a hierarchical structure."""

        # Retrieve all products from the database where source = 'print.com'
        products = list(self.products_collection.find({"ref_id": vendor}))

        for product in products:
            product_sku = product.get("sku", "default_sku")
            product_id = product.get("_id")
            product_name = product.get("name")
            d = product.get("name")
            tenant_id = product.get("tenant_id")
            tenant_name = product.get("tenant_name")

            # Retrieve all properties associated with this product based on categories
            properties = list(self.properties_collection.find({"categories": product_id}))

            # Create a hierarchical document for the product
            product_data = product.copy()
            product_data["source_slug"] = product_data.get("slug")
            product_data["slug"] = slugify(product_data.get("slug"))
            product_data.pop("_id", None)

            # Process each property associated with the product
            for property in properties:
                property_slug = property.get("slug", "unknown_slug")

                property_data = property.copy()
                property_data["source_slug"] = property.get("slug")
                property_data["slug"] = slugify(property.get("slug"), to_lower=True)
                property_data["slugify_name"] = slugify(property.get("name"), to_lower=True)
                property_data["ops"] = []

                property_id_to_search = str(property.get("_id"))

                # Fetch options related to this property
                # options = list(
                #     self.options_collection.find({"property_id": {"$in": [ObjectId(property_id_to_search)]}}))

                options = list(
                    self.options_collection.find({
                        "$and": [
                            {"property_id": {"$in": [ObjectId(property_id_to_search)]}},
                            {"property_slug": property_slug},
                            {"product_sku": product_sku},
                        ]
                    })
                )

                if options:
                    for option in options:
                        option_data = option.copy()
                        option_data["source_slug"] = option_data.get("slug")
                        option_data["slug"] = slugify(option_data.get("slug"))
                        option_data["excludes"] = []
                        property_data["ops"].append(option_data)

                    # Apply exclusions if available
                    excludes = property.get("excludes", [])
                    if excludes:
                        for exclude_set in excludes:
                            property_data["excludes"].append([exclude_set])

                    # Add the processed property to the product
                    product_data["boops"].append(property_data)

            # Store only products with valid properties and options
            if product_data["boops"]:
                self.oops_collection.update_one(
                    {"sku": product_data["sku"]},  # Check by SKU
                    {"$set": product_data},  # Insert or update the product and its properties
                    upsert=True  # Create if missing
                )

                # print(f"✅ Product '{product_data['sku']}' stored in 'oops' collection.")

    def get_oops_by_skus(self, skus):
        """
        Fetch documents from 'oops_collection' where 'sku' is in the given list.

        Args:
            skus (list): A list of SKU values to search for.

        Returns:
            list: A list of dictionaries containing matching documents.
        """
        if not isinstance(skus, list):
            return {"error": "Invalid input, expected a list of SKUs"}

        try:
            # Query MongoDB with skus list
            results = list(self.oops_collection.find({"sku": {"$in": skus}}))

            # Recursively convert ObjectId to string
            def serialize_document(doc):
                """Recursively convert MongoDB ObjectId to string in all nested fields."""
                if isinstance(doc, dict):
                    return {k: serialize_document(v) for k, v in doc.items()}
                elif isinstance(doc, list):
                    return [serialize_document(v) for v in doc]
                elif isinstance(doc, ObjectId):
                    return str(doc)  # Convert ObjectId to string
                else:
                    return doc  # Return other types as they are

            formatted_results = [serialize_document(doc) for doc in results]

            return formatted_results  # Return JSON-serializable response

        except Exception as e:
            return {"error": str(e)}

# Example Usage:
# client = get_mongo_connection()
# generate_boobs_service = GenerateBoobsService(client)
# generate_boobs_service.store_all_in_oops()
