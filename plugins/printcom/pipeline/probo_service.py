import requests
from config import API_URL, API_TOKEN
from datetime import datetime
from config import DB_NAME, BOX_COLL, OPTION_COLL, BOOBS_COLL, POLICY_COLL, PROBO_API_URL, PROBO_API_TOKEN

class ProboService:
    def __init__(self, client):
        """Initialize APIService with base headers."""
        self.headers = {"Authorization": f"Bearer {API_TOKEN}"}
        self.client = client
        self.db = client[DB_NAME]
        self.opts_collection = self.db[OPTION_COLL]
        self.oops_collection = self.db[BOOBS_COLL]
        self.properties_collection = self.db[BOX_COLL]
        self.policies_collection = self.db[POLICY_COLL]

    def fetch_proboprints_products(self):
        """Fetch product data from ProboPrints API."""
        response = requests.get(API_URL, headers=self.headers)
        return response.json() if response.status_code == 200 else None

    def fetch_product_options(self, products=None):
        """
        Fetches product configuration options from the API and extracts the relevant data.

        Returns:
            list: A list of dictionaries containing the extracted options.
        """
        products_codes = []
        if not products is None:
            for product in products:
                products_codes.append({"code": product})


        url = f"{PROBO_API_URL}/configure"
        headers = {
            "Authorization": f"Bearer {PROBO_API_TOKEN}",
            "Content-Type": "application/json"
        }
        payload = {
            "products": products_codes or []
        }

        response = requests.post(url, json=payload, headers=headers)

        if response.status_code == 200:
            data = response.json()
            extracted_data =  self.extract_probo_data(data)
            self.store_probo_properties_in_mongo(extracted_data, "555", "abdel", "102030")

        else:
            raise Exception(f"API request failed with status code {response.status_code}: {response.text}")

    def extract_probo_data(self, data):
        """
        Extracts product properties and their associated options in a structured format.

        Args:
            data (dict): The API response JSON.

        Returns:
            list: A list of dictionaries where each product has its properties, and each property contains its associated options.
        """
        extracted_data = []

        if "products" in data:
            for product in data["products"]:
                product_entry = {
                    "Product Code": product.get("code", "Unknown"),
                    "data": {}
                }

                # Extract available options (as separate property groups)
                if "available_options" in product:
                    for option in product["available_options"]:
                        property_data = {
                            "name": option.get("name", None),
                            "label": option.get("label", None),
                            "code": option.get("code", None),
                            "can_order": option.get("can_order", None),
                            "amount": option.get("amount", None),
                            "width": option.get("width", None),
                            "height": option.get("height", None),
                            "length": option.get("length", None),
                            "available": option.get("available", None),
                            "unit_code": option.get("unit_code", None),
                            "price": option.get("price", None),
                            "options": []  # This will hold the child options
                        }

                        # Extract associated options (children)
                        for child in option.get("children", []):
                            property_data["options"].append({
                                "type_code": child.get("type_code", None),
                                "name": child.get("name", None),
                                "label": child.get("label", None),
                                "description": child.get("description", None),
                                "value": child.get("value", None),
                                "code": child.get("code", None),
                                "default_value": child.get("default_value", None),
                                "min_value": child.get("min_value", None),
                                "max_value": child.get("max_value", None),
                                "step_size": child.get("step_size", None),
                                "scale": child.get("scale", None),
                                "reversible": child.get("reversible", None),
                                "last_option": child.get("last_option", None),
                                "available": child.get("available", None),
                                "unit_code": child.get("unit_code", None),
                                "price": child.get("price", None),
                                "images": child.get("images", [])
                            })

                        # Store this property under 'data'
                        product_entry["data"][option.get("code", "unknown_property")] = property_data

                extracted_data.append(product_entry)

        return extracted_data

    def store_probo_properties_in_mongo(self, probo_product_data, product_id, tenant_name, tenant_id):
        """
        Extract and store Probo product properties and options in MongoDB.

        Args:
            probo_product_data (list): The extracted product data from Probo.
            product_id (str): The product ID.
            tenant_name (str): The tenant name.
            tenant_id (str): The tenant ID.
        """

        if not probo_product_data:
            print("No product data found. Skipping...")
            return

        for product in probo_product_data:
            product_sku = product.get("Product Code", "unknown_sku")
            properties_data = product.get("data", {})

            if not properties_data:
                print(f"No properties found for {product_sku}. Skipping...")
                continue

            print("properties_data", properties_data)
            for property_code, property_details in properties_data.items():
                # Convert property to MongoDB format
                modified_property = {
                    "product_sku": product_sku,
                    "slug": property_details.get("code", "unknown_slug"),
                    "title": property_details.get("name", "Untitled Property"),
                    "name": property_details.get("name", "Untitled Property"),
                    "display_name": [
                        {"iso": 'en', "display_name": property_details.get("name", "Untitled Property")},
                        {"iso": 'fr', "display_name": property_details.get("name", "Untitled Property")},
                        {"iso": 'nl', "display_name": property_details.get("name", "Untitled Property")},
                        {"iso": 'de', "display_name": property_details.get("name", "Untitled Property")}
                    ],
                    "locked": False,
                    "created_at": datetime.utcnow().isoformat(),
                    "sku": property_details.get("code", "unknown_slug"),
                    "system_key": property_details.get("code", "unknown_slug"),
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

                # Upsert property in MongoDB
                result = self.properties_collection.update_one(
                    {"product_sku": modified_property["product_sku"], "slug": modified_property["slug"]},
                    {"$set": modified_property},
                    upsert=True
                )

                # Fetch property ID (if upserted, otherwise reuse existing)
                property_id = result.upserted_id if result.upserted_id else modified_property.get("_id")

                # Extract options under this property
                options_data = property_details.get("options", [])

                for option in options_data:
                    option_code = option.get("code", False)
                    nullable = option.get("nullable", False)
                    value = option.get("value", "")
                    incremental_by = option.get("scale", "")

                    modified_option = {
                        "product_sku": product_sku,
                        "property_slug": modified_property["slug"],
                        "sku": product_sku,
                        "slug": option.get("code", "unknown_option"),
                        "name": option.get("name", "Unknown Option"),
                        "nullable": nullable,
                        "value": value,
                        "created_at": datetime.utcnow().isoformat(),
                        "input": option,
                        "sort": 0,
                        "rpm": 0,
                        "information": "",
                        "additional": {},
                        "sheet_runs": [],
                        "parent": True,
                        "has_children": False,
                        "dynamic": False,
                        "shareable": True,
                        "extended_fields": {},
                        "calculation_method": [],
                        "dimension": "2d",
                        "tenant_name": tenant_name,
                        "tenant_id": tenant_id,
                        "media": "",
                        "dynamic_keys": [],
                        "generate": False,
                        "width": option.get("min_value", 0),
                        "maximum_width": option_code == "width" if option.get("max_value", 0) else 0,
                        "minimum_width": option_code == "width" if option.get("min_value", 0) else 0,
                        "incremental_by": incremental_by,
                        "maximum_height": option_code == "height" if option.get("minHeight", 0) else 0,
                        "minimum_height": option_data.get("customSizes", {}).get("maxHeight", 0),
                        "unit": option.get("unit_code", ""),
                        "published": True,
                        "display_name": [
                            {"iso": 'en', "display_name": option.get("name", "Unknown Option")},
                            {"iso": 'fr', "display_name": option.get("name", "Unknown Option")},
                            {"iso": 'nl', "display_name": option.get("name", "Unknown Option")},
                            {"iso": 'de', "display_name": option.get("name", "Unknown Option")},
                        ],
                        "product_id": [product_id],
                        "property_id": [property_id],
                        "excludes": [],
                        "includes": [],
                    }

                    # Upsert option in MongoDB
                    self.opts_collection.update_one(
                        {"product_sku": modified_option["product_sku"],
                         "property_slug": modified_option["property_slug"], "slug": modified_option["slug"]},
                        {"$set": modified_option},
                        upsert=True
                    )
