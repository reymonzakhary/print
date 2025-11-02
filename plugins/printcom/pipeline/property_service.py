from datetime import datetime

import requests

from config import DB_NAME, BOX_COLL, OPTION_COLL, BOOBS_COLL, POLICY_COLL, PROBO_API_URL, PROBO_API_TOKEN
from database import get_mongo_connection
from pipeline.option_service import OptionService

class PropertyService:
    def __init__(self, client):
        """Initialize PropertyService with a MongoDB client."""
        self.client = client
        self.db = client[DB_NAME]
        self.properties_collection = self.db[BOX_COLL]
        self.policies_collection = self.db[POLICY_COLL]
        self.option_service = OptionService(client)

    def modify_property(self, product_sku, property_data, product_id, tenant_name, tenant_id):
        """Modify property data before inserting into MongoDB."""
        slug = property_data.get("slug", "unknown_slug")
        name = property_data.get("title", slug)

        if slug == 'copies' or slug == 'printingmethod':
            return None  # Skip 'copies' and 'printingmethod' properties

        return {
            "product_sku": product_sku,
            "slug": slug,
            "title": property_data.get("title", "Untitled Property"),
            "name": name,
            "display_name": [
                {"iso": 'en', "display_name": name},
                {"iso": 'fr', "display_name": name},
                {"iso": 'nl', "display_name": name},
                {"iso": 'de', "display_name": name}
            ],
            "locked": property_data.get("locked", False),
            "created_at": datetime.utcnow().isoformat(),
            "sku": slug,
            "system_key": slug,
            "description": "",
            "media": [],
            "sqm": False,
            "appendage": False,
            "calculation_type": "",
            "published": True,
            "input_type": "",
            "incremental": False,
            "select_limit": 0,
            "option_limit": 0,
            "shareable": True,
            "categories": [product_id],
            "additional": {},
            "tenant_name": tenant_name,
            "tenant_id": tenant_id,
            "includes": [],
            "excludes": [],
        }

    # def store_excludes(self, excludes, product_slug):
    #     """
    #     Store the excludes as a grouped document in the 'policies' collection.
    #     """
    #     if excludes and isinstance(excludes, list):
    #         self.policies_collection.update_one(
    #             {"product_slug": product_slug},  # Filter condition
    #             {
    #                 "$set": {
    #                     "synced": False,
    #                     "excludes": excludes
    #                 }
    #             },
    #             upsert=True  # Insert if not exists
    #         )

    def store_excludes(self, excludes, product_slug):
        """
        Store the excludes as a grouped document in the 'policies' collection.
        """
        if excludes and isinstance(excludes, list):
            # Remove any exclusions with property = 'copies' or 'printingmethod'
            excludes = [
                [exclude_dict for exclude_dict in exclude_list if
                 exclude_dict.get("property") not in ["printingmethod", "copies"]]
                for exclude_list in excludes
            ]

            # Now store the filtered exclusions in the database
            self.policies_collection.update_one(
                {"product_slug": product_slug},  # Filter condition
                {
                    "$set": {
                        "synced": False,
                        "excludes": excludes
                    }
                },
                upsert=True  # Insert if not exists
            )

    def store_properties_in_mongo(self, product_data, products_details, product_id, tenant_name, tenant_id):
        """
        Extract and store product properties in the 'properties' collection, including product_id.
        """
        if not product_data or "properties" not in product_data:
            print(f" No properties found for {product_data.get('sku', 'unknown_sku')}. Skipping...")
            return

        product_sku = product_data["sku"]
        excludes = products_details.get("excludes", [])

        # Store exclude policies
        self.store_excludes(excludes, product_sku)

        for prop in product_data["properties"]:
            modified_property = self.modify_property(product_sku, prop, product_id, tenant_name, tenant_id)

            if modified_property:
                modified_property["product_id"] = product_id

                # Upsert the property data
                result = self.properties_collection.update_one(
                    {"product_sku": modified_property["product_sku"], "slug": modified_property["slug"]},
                    {"$set": modified_property},
                    upsert=True
                )

                # Fetch property ID
                property_id = result.upserted_id if result.upserted_id else modified_property.get("_id")

                # Pass product_id and property_id to store options
                self.option_service.store_options_in_mongo(
                    product_sku, modified_property["slug"], prop.get("options", []),
                    prop.get("excludes", []), prop.get("includes", []), product_id, property_id, tenant_name,
                    tenant_id, prop
                )
