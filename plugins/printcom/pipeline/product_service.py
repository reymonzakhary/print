from datetime import datetime
from api import APIService
from config import DB_NAME, CATEGORY_COLL
from database import get_mongo_connection
from pipeline.property_service import PropertyService
from pipeline.policy_service import PolicyService
from pipeline.probo_service import ProboService

class ProductService:
    def __init__(self, client):
        """Initialize ProductService with a MongoDB client."""
        self.client = client
        self.db = client[DB_NAME]
        self.collection = self.db[CATEGORY_COLL]
        self.property_service = PropertyService(client)
        self.policy_service = PolicyService(client)
        self.api_service = APIService(client)
        self.probo_service = ProboService(client)

    def modify_product(self, product, tenant_name, tenant_id, vendor="print.com"):
        """Modify product before inserting into MongoDB."""

        en = ""
        de = ""
        nl = ""
        fr = ""

        name = "Unnamed Product"
        sku = "default"
        status = "inactive"
        if vendor == "print.com":
            name = str(product.get("titleSingle", "Unnamed Product")).replace("-", " ").capitalize()
            sku = product.get("sku", "default")
            status = "active" if product.get("active", False) else "inactive"

        elif vendor == "probo.com":
            name = str(product.get("code", "Unnamed Product")).replace("-", " ").capitalize()
            sku = product.get("code", "default")
            status = "active" if product.get("active", False) else "inactive"
            en = product.get("translations", {}).get("en", "").get("title", "")
            de = product.get("translations", {}).get("de", "").get("title", "")
            nl = product.get("translations", {}).get("nl", "").get("title", "")
            fr = product.get("translations", {}).get("fr", "").get("title", "")

        return {
            "status": status,
            "name": name,
            "tenant_name": tenant_name,
            "tenant_id": tenant_id,
            "system_key": sku,
            "sort": 0,
            "countries": [],
            "display_name": [
                {"iso": "en", "display_name": en},
                {"iso": "fr", "display_name": fr},
                {"iso": "nl", "display_name": nl},
                {"iso": "de", "display_name": de}
            ],
            "slug": sku,
            "sku": sku,
            "shareable": False,
            "price_build": {
                "external": True,
                "full_calculation": False,
                "semi_calculation": False,
                "collection": False
            },
            "published": True,
            "media": [],
            "has_products": False,
            "has_manifest": False,
            "calculation_method": [],
            "dlv_days": [],
            "printing_method": [],
            "production_days": [
                {"day": "mon", "active": True, "deliver_before": "12:00"},
                {"day": "tue", "active": True, "deliver_before": "12:00"},
                {"day": "wed", "active": True, "deliver_before": "12:00"},
                {"day": "thu", "active": True, "deliver_before": "12:00"},
                {"day": "fri", "active": True, "deliver_before": "12:00"},
                {"day": "sat", "active": False, "deliver_before": "12:00"},
                {"day": "sun", "active": False, "deliver_before": "12:00"},
            ],
            "ref_id": vendor,
            "ref_category_name": "",
            "start_cost": 0,
            "created_at": datetime.utcnow().isoformat(),
            "checked": False,
            "additional": [],
            "vat": 0,
            "bleed": 0,
            "production_dlv": [],
            "linked": None,
            "suppliers": None,
            "matches": None,
            "range_list": [],
            "limits": [],
            "free_entry": [],
            "range_around": 10,
            "boops": [],
        }

    def store_products_in_mongo(self, products, tenant_name, tenant_id, sku_list=None, vendor="print.com"):
        """Insert or update products in MongoDB based on SKU."""
        print(f"Processing {vendor} products...")

        code = "code" if vendor == "probo.com" else "sku"
        if not products:
            print(" No products to process.")
            return

        if sku_list is None:
            sku_list = []

        for product in products:
            if sku_list and product.get(code) not in sku_list:
                continue
            modified_product = self.modify_product(product, tenant_name, tenant_id, vendor)  # Modify product data

            # Use `update_one` with `upsert=True` to CreateOrUpdate based on SKU
            self.collection.update_one(
                {"sku": modified_product["sku"]},
                {"$set": modified_product},
                upsert=True
            )

            product_id = self.collection.find_one({"system_key": modified_product["system_key"]})["_id"]

            # Fetch product details from API
            returned_products = self.api_service.fetch_origin_products(modified_product["sku"])
            origin_product = returned_products.get("products", {})
            products_details = returned_products.get("products_details", {})

            if vendor == "print.com":
                # Store properties for this product
                self.property_service.store_properties_in_mongo(
                    origin_product, products_details, product_id, modified_product["name"], modified_product["tenant_id"]
                )

                # Sync excludes
                self.policy_service.sync_excludes(modified_product["sku"])
            elif vendor == "probo.com":
                print("Processing ProboPrints products...", modified_product['sku'])
                data =  self.probo_service.fetch_product_options(sku_list)
                return data
