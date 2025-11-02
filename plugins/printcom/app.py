import os
from itertools import product
from pprint import pprint
import random

import requests
import pymongo
from datetime import datetime
from dotenv import load_dotenv
from fastapi import FastAPI
from bson import ObjectId
from pydantic import BaseModel

app = FastAPI()

# Load environment variables from .env file
load_dotenv()

# MongoDB Connection Details
MONGO_URI = os.getenv("MONGO_URI")
DB_NAME = os.getenv("DB_NAME")

# API Details
API_URL = os.getenv("API_URL")
API_TOKEN = os.getenv("API_TOKEN")

CATEGORY_COLL = os.getenv("CATEGORY_COLL")
BOX_COLL = os.getenv("BOX_COLL")
OPTION_COLL = os.getenv("OPTION_COLL")
BOOBS_COLL = os.getenv("BOOBS_COLL")

def get_mongo_connection():
    """Establish a MongoDB connection with connection pooling."""
    try:
        client = pymongo.MongoClient(MONGO_URI, serverSelectionTimeoutMS=5000)
        client.admin.command("ping")  # Check connection
        print("  Connected to MongoDB")
        return client
    except pymongo.errors.ServerSelectionTimeoutError:
        print("❌ Failed to connect to MongoDB. Check URI and MongoDB server.")
        exit(1)

def fetch_products():
    """Fetch product data from the API."""
    headers = {"Authorization": f"Bearer {API_TOKEN}"}
    response = requests.get(API_URL, headers=headers)

    if response.status_code == 200:
        products = response.json()
        if isinstance(products, list):  # Ensure response is an array
            return products
        else:
            print("❌ Unexpected API response format. Expected a list.")
            return []
    else:
        print(f"❌ Error: Failed to fetch products. Status Code: {response.status_code}")
        return []


def modify_product(product, tenant_name: str, tenant_id: str):
    """Modify product data before inserting into MongoDB."""
    name = str(product.get("titleSingle", "Unnamed Product")).replace("-", " ").capitalize()

    print("product name", name)

    return {
        "source": "print.com",
        "status": "active" if product.get("active", False) else "inactive",
        "name": name,
        "tenant_name": tenant_name,
        "tenant_id": tenant_id,
        "system_key": product.get("sku", "default"),
        "sort": 0,
        "countries": [],
        "display_name": [
            {"iso": "en", "display_name": name},
            {"iso": "fr", "display_name": name},
            {"iso": "nl", "display_name": name},
            {"iso": "de", "display_name": name}
        ],
        "slug": product.get("sku", "default"),
        "sku": product.get("sku", "default"),
        "shareable": False,
        "published": True,
        "media": [],
        "price_build": {},
        "has_products": False,
        "has_manifest": False,
        "calculation_method": [],
        "dlv_days": [],
        "printing_method": [],
        "production_days": [],
        "ref_id": "",
        "ref_category_name": "",
        "start_cost": 0,
        "created_at": datetime.utcnow().isoformat(),
        "checked": False,
        "additional": {}
    }

def modify_property(product_sku, property_data, product_id, tenant_name: str, tenant_id: str):
    """Modify property data before inserting into MongoDB."""
    slug = property_data.get("slug", "unknown_slug")
    name = property_data.get("slug", "Untitled Name")
    rand = random.randrange(10000, 100000)
    return {
        "product_sku": product_sku,  # Link property to product
        "slug": "{}-{}".format(slug, rand),
        "title": property_data.get("title", "Untitled Property"),
        "name": name,
        "display_name": [
            {
                "iso": 'en',
                "display_name": name
            },
            {
                "iso": 'fr',
                "display_name": name
            },
            {
                "iso": 'nl',
                "display_name": name
            },
            {
                "iso": 'de',
                "display_name": name
            }
        ],
        "locked": property_data.get("locked", False),
        "created_at": datetime.utcnow().isoformat(),
        "sku"  : slug,
        "system_key"  : slug,
        "description"  : "",
        "media": [],
        "sqm"  : False,
        "appendage"  : False,
        "calculation_type"  : "",
        "published"  : True,
        "input_type"  : "",
        "incremental"  : False,
        "select_limit"  : 0,
        "option_limit"  : 0,
        "shareable"  : True,
        "categories"  : [
            product_id
        ],
        "additional"  : {},
        "tenant_name": tenant_name,
        "tenant_id": tenant_id,
        "includes": [],
        "excludes": [],
    }

def modify_option(product_sku, property_slug, option_data, product_id, property_id, tenant_name: str, tenant_id: str):
    """Modify option data before inserting into MongoDB."""
    slug = str(option_data.get("slug", "unknown_option"))
    title = option_data.get("name", slug)
    required = option_data.get("nullable", False)
    rand = random.randrange(10000, 100000)
    return {
        "product_sku": product_sku,
        "property_slug": property_slug,  # Link option to the property
        "sku": product_sku,
        "slug": "{}-{}".format(slug, rand),
        "name": title,
        "nullable": required,
        "eco": option_data.get("eco", False) if "eco" in option_data else None,
        "customSizes": option_data.get("customSizes", None),  # Store custom sizes if available
        "created_at": datetime.utcnow().isoformat(),
        "input": option_data,
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
        "start_on": 0,
        "end_on": 0,
        "generate": False,
        "dynamic_type": '',
        "width": 0,
        "maximum_width": 0,
        "minimum_width": 0,
        "height": 0,
        "maximum_height": 0,
        "minimum_height": 0,
        "length": 0,
        "maximum_length": 0,
        "minimum_length": 0,
        "unit": '',
        "incremental_by": 0,
        "published": True,
        "display_name": [
            {
                "iso": 'en',
                "display_name": title
            },
            {
                "iso": 'fr',
                "display_name": title
            },
            {
                "iso": 'nl',
                "display_name": title
            },
            {
                "iso": 'de',
                "display_name": title
            }
        ],
        "product_id":  [product_id],
        "property_id": [property_id],
        "excludes": [],
        "includes": [],
    }

def store_options_in_mongo(client, product_sku, property_slug, options, excludes, includes,
                           product_id, property_id, tenant_name: str, tenant_id: str):
    """Store options in the 'opts' collection."""
    if not options:
        print(f" No options found for property '{property_slug}' of product '{product_sku}'. Skipping...")
        return

    db = client[DB_NAME]
    opts_collection = db[OPTION_COLL]

    for option in options:
        modified_option = modify_option(product_sku, property_slug, option, product_id, property_id, tenant_name, tenant_id)  # Format option data

        # If exclusions exist, add them to the option data
        for exclude_set in excludes:
            if exclude_set.get("property") == property_slug:
                # Add excludes to the option data
                modified_option["excludes"] = exclude_set["options"]

                print("*" * 50)
                print("property_slug_ex", property_slug)
                print("*" * 50)

        # Upsert option in MongoDB (Create if missing, update if exists)
        result = opts_collection.update_one(
            {"product_sku": modified_option["product_sku"], "property_slug": modified_option["property_slug"], "slug": modified_option["slug"]},
            {"$set": modified_option},
            upsert=True
        )

        if result.upserted_id:
            print(f"  Inserted new option '{modified_option['slug']}' for property '{property_slug}' of product '{product_sku}'")
        else:
            print(f" Updated existing option '{modified_option['slug']}' for property '{property_slug}' of product '{product_sku}'")



def fetch_product_box(sku):
    """Fetch product options from /printed-letterheads/{sku} API."""
    options_url = f"{API_URL}/{sku}?view=reseller"
    # print(options_url)
    headers = {"Authorization": f"Bearer {API_TOKEN}"}

    response = requests.get(options_url, headers=headers)

    if response.status_code == 200:
        return response.json()  # Expecting options as JSON
    else:
        print(f"❌ Error fetching options for {sku}. Status: {response.status_code}")
        return None  # Return None if request fails


def store_products_in_mongo(client, products, tenant_name: str, tenant_id: str, limit=0):
    """Insert or update products in MongoDB based on SKU."""
    db = client[DB_NAME]
    collection = db[CATEGORY_COLL]

    if not products:
        print(" No products to process.")
        return

    index = 0
    for product in products:
        index += 1
        modified_product = modify_product(product, tenant_name, tenant_id)  # Modify product data

        # Use `update_one` with `upsert=True` to CreateOrUpdate based on SKU
        result = collection.update_one(
            {"sku": modified_product["sku"]},  # Find by SKU
            {"$set": modified_product},  # Update existing or insert new
            upsert=True
        )

        product_id = collection.find_one({"system_key": modified_product["system_key"]})["_id"]

        if result.upserted_id:
            print(f"  Inserted new product: {modified_product['sku']}")
        else:
            print(f" Updated existing product: {modified_product['sku']}")


        product_properties = fetch_product_box(modified_product["sku"])
        store_properties_in_mongo(client, product_properties, product_id, modified_product["name"], modified_product["tenant_id"])

        # pprint(product_properties)

        if index > limit:
            break


def store_properties_in_mongo(client, product_data, product_id, tenant_name: str, tenant_id: str):
    """Extract and store product properties in the 'properties' collection, including product_id."""
    if not product_data or "properties" not in product_data:
        print(f" No properties found for {product_data.get('sku', 'unknown_sku')}. Skipping...")
        return

    db = client[DB_NAME]
    properties_collection = db[BOX_COLL]
    opts_collection = db[OPTION_COLL]
    oops_collection = db[BOOBS_COLL]

    excludes = product_data.get("excludes", [])


    for prop in product_data["properties"]:
        modified_property = modify_property(product_data["sku"], prop, product_id, tenant_name, tenant_id)
        modified_property["product_id"] = product_id  # 🟢 Add product_id to properties

        ###################################################################################################################3
        """
        options = list(opts_collection.find({"property_slug": prop["slug"]}))
        # If there are any exclusions for this property, apply them
        for exclude_set in excludes:
            if "property" in exclude_set and exclude_set["property"] == prop["slug"]:
                # Filter options based on the exclusion list
                excluded_option_slugs = exclude_set.get("options", [])
                options = [option for option in options if option["slug"] not in excluded_option_slugs]

        # Add only the IDs of the filtered options to the "excludes" field
        modified_property["excludes"] = [option["_id"] for option in options]
        print("*" * 50)
        print("excludes set:", modified_property["excludes"])
        print("*" * 50)
        """
        ###################################################################################################################3

        # Upsert into MongoDB (Create if missing, update if exists)
        result = properties_collection.update_one(
            {"product_sku": modified_property["product_sku"], "slug": modified_property["slug"]},
            {"$set": modified_property},
            upsert=True
        )

        property_doc = properties_collection.find_one({"product_sku": modified_property["product_sku"], "slug": modified_property["slug"]})

        if result.upserted_id:
            print(f"  Inserted new property '{modified_property['slug']}' for product {product_data['sku']}")
        else:
            print(f" Updated existing property '{modified_property['slug']}' for product {product_data['sku']}")

        # 🟢 Capture the _id from the result
        property_id = result.upserted_id if result.upserted_id else modified_property.get("_id")


        if property_doc:
            property_id = property_doc["_id"]
            print(f"Property ID: {property_id}")
        else:
            print(f"Property not found for {modified_property['slug']}")


        # Pass product_id and _id of the property to options as well
        store_options_in_mongo(client, product_data["sku"], modified_property["slug"], prop.get("options", []),
                               prop.get("excludes", []),prop.get("includes", []),
                               product_id, property_id, tenant_name, tenant_id)


def store_all_in_oops(client):
    """Retrieve products, properties, and options from DB where source = 'print.com', and store in 'oops' collection in a hierarchical structure."""
    db = client[DB_NAME]
    products_collection = db[CATEGORY_COLL]  # Your products collection
    properties_collection = db[BOX_COLL]  # Properties collection
    options_collection = db[OPTION_COLL]  # Options collection
    oops_collection = db[BOOBS_COLL]

    # Retrieve all products from the database where source = 'print.com'
    products = list(products_collection.find({"source": "print.com"}))

    for product in products:
        product_sku = product.get("sku", "default_sku")
        product_id = product.get("_id")
        product_name = product.get("titleSingle")
        tenant_id = product.get("tenant_id")
        tenant_name = product.get("tenant_name")

        # Retrieve all properties associated with this product based on categories (product's _id must be in the property categories)
        properties = list(properties_collection.find({"categories": product_id}))

        # Create a hierarchical document for the product
        product_data = {
            "sku": product_sku,
            "name": product.get("name", "Unnamed Product"),
            "status": product.get("status", "inactive"),
            "system_key": product.get("sku", "default"),
            "boops": [],
            "tenant_id": tenant_id,
            "ref_id": "",
            "ref_boops_name": "",
            "tenant_name": tenant_name,
            "supplier_category": product_id,
            "linked": "",
            "display_name": [
                {"iso": 'en', "display_name": product_name},
                {"iso": 'fr', "display_name": product_name},
                {"iso": 'nl', "display_name": product_name},
                {"iso": 'de', "display_name": product_name}
            ],
            "shareable": False,
            "published": True,
            "generated": True,
            "divided": False,
            "slug": product_name,
        }

        # For each property associated with the product, add options
        for property in properties:
            property_slug = property.get("slug", "unknown_slug")
            property_data = property.copy()
            property_data["ops"] = []

            property_id_to_search = str(property.get("_id"))

            # Query the options collection where property_id contains the specific ObjectId
            options = list(options_collection.find({"property_id": {"$in": [ObjectId(property_id_to_search)]}}))

            # Only process the property if it has options
            if options:
                # Add the options to the property data
                for option in options:
                    option_data = option.copy()
                    option_data["excludes"] = []
                    property_data["ops"].append(option_data)

                # If there are any exclusions for this property, apply them
                excludes = property.get("excludes", [])
                if excludes:
                    for exclude_set in excludes:
                        property_data["excludes"].append([exclude_set])

                # Add the property to the product document
                product_data["boops"].append(property_data)

                # Only insert or update the product if it has properties with options
                if product_data["boops"]:
                    result = oops_collection.update_one(
                        {"sku": product_data["sku"]},  # Check by SKU
                        {"$set": product_data},  # Insert or update the product and its properties and options
                        upsert=True  # Create if missing
                    )

                    if result.upserted_id:
                        print(f"  Inserted new product '{product_data['sku']}' with properties and options.")
                    else:
                        print(f" Updated existing product '{product_data['sku']}'.")




@app.get("/")
async def root():
    return {"message": "Wilkommen Du bist here in Print.com Intergration"}


@app.get("/sync")
async def sync(tenant_name: str, tenant_id: str, limit):
    print("tenant_name", tenant_name)
    print("tenant_id", tenant_id)

    client = get_mongo_connection()
    products = fetch_products()

    if products:
        store_products_in_mongo(client, products, tenant_name, tenant_id, int(limit))
    else:
        print("❌ No valid products received from API.")

    store_all_in_oops(client)
    client.close()

    return {"message": "Synced Success"}


# Define the request model (optional, depending on what you want in the request)
class PriceRequest(BaseModel):
    sku: str
    options: dict

# Define the POST endpoint for getting a random price
@app.post("/get-price")
async def get_price(request: PriceRequest):
    """Fetch product price data from the API."""

    headers = {"Authorization": f"Bearer {API_TOKEN}"}

    response = requests.post(
        "{}/{}/price".format(API_URL, request.sku),
        headers=headers,
        json={
            "sku": request.sku,
            "options": request.options
        }
    )

    if response.status_code == 200:
        return response.json()
    else:
        print(f"❌ Error: Failed to fetch product price. Status Code: {response.status_code}")

        return []
