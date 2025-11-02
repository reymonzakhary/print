import os
import re

import requests
from dotenv import load_dotenv

from config import (DB_NAME, CATEGORY_COLL, BOX_COLL, OPTION_COLL, POLICY_COLL, API_TOKEN, API_URL, LOGIN_URL,
                    EMAIL, ENV_FILE, PASSWORD)
from database import get_mongo_connection

class UtilsService:
    def __init__(self, client):
        """Initialize UtilsService with a MongoDB client."""
        self.client = client
        self.db = client[DB_NAME]
        self.category_collection = self.db[CATEGORY_COLL]

    @staticmethod
    def sanitize_slugs(data):
        """
        Recursively traverse a dictionary or list and replace special characters
        in all values corresponding to the key 'slug'.
        """
        if isinstance(data, dict):  # If it's a dictionary
            for key, value in data.items():
                if key == "slug" and isinstance(value, str):
                    data[key] = UtilsService.replace_special_chars(value)
                else:
                    data[key] = UtilsService.sanitize_slugs(value)  # Recur for nested dicts/lists
        elif isinstance(data, list):  # If it's a list
            data = [UtilsService.sanitize_slugs(item) for item in data]

        return data
    @staticmethod
    def replace_special_chars(text: str) -> str:
        """
        Replaces all special characters in a string with '-',
        ensuring multiple dashes are reduced to a single '-'.
        """
        text = re.sub(r'[^a-zA-Z0-9]', '-', text)  # Replace special chars with '-'
        return re.sub(r'-+', '-', text).strip('-')  # Remove duplicate '-' and trim edges


    def delete_all_data(self):
        """Delete all collections in MongoDB."""
        collections_to_clear = [
            CATEGORY_COLL,  # Products
            BOX_COLL,       # Properties
            OPTION_COLL,    # Options
            POLICY_COLL     # Policies
        ]

        for collection_name in collections_to_clear:
            collection = self.db[collection_name]
            result = collection.delete_many({})
            print(f"Deleted {result.deleted_count} documents from {collection_name} collection.")

    def set_range_set(self):
        """Insert or update products in MongoDB based on SKU."""
        headers = {"Authorization": f"Bearer {API_TOKEN}"}

        # Fetch all products from the collection
        products = list(self.category_collection.find())

        if not products:
            print("No products to process.")
            return

        for product in products:
            sku = product.get("sku")
            products_details_url = f"{API_URL}/{sku}?fields=rangeSets"

            # Make a request to the external API to get properties and rangeSets
            response = requests.get(products_details_url, headers=headers)

            if response.status_code == 200:
                properties_data = response.json().get("properties", [])
                ranges = []

                for property_item in properties_data:
                    if "rangeSets" in property_item:
                        for range_set in property_item["rangeSets"]:
                            printing_method = range_set.get("printingmethod")
                            options = range_set.get("options", [])

                            for option in options:
                                # Map the fields to the required format
                                range_item = {
                                    "name": printing_method,
                                    "slug": printing_method.lower(),
                                    "from": option.get("min"),
                                    "to": option.get("max"),
                                    "incremental_by": option.get("steps")
                                }
                                ranges.append(range_item)

                # Now update the product's rangeSets field with the new data
                result = self.category_collection.update_one(
                    {"sku": sku}, {"$set": {"ranges": ranges}}  # Update rangeSets field
                )

                if result.matched_count > 0:
                    print(f"Updated rangeSets for product with SKU {sku}")
                else:
                    print(f"Product with SKU {sku} not found or rangeSets not updated.")

            else:
                print(f"Failed to fetch properties for SKU {sku}. Status Code: {response.status_code}")

    def update_env_var(self, key, value):
        """Update .env file with a new key-value pair."""
        env_lines = []

        with open(ENV_FILE, "r") as file:
            env_lines = file.readlines()

        # Update existing key or add if missing
        key_found = False
        for i, line in enumerate(env_lines):
            if line.startswith(f"{key}="):
                env_lines[i] = f"{key}={value}\n"
                key_found = True
                break

        if not key_found:
            env_lines.append(f"{key}={value}\n")

        # Write back to .env
        with open(ENV_FILE, "w") as file:
            file.writelines(env_lines)

        # Reload the .env variables
        load_dotenv(override=True)

    def get_new_api_token(self):
        """Fetch a new API token using login credentials."""
        payload = {
            "credentials": {
                "username": EMAIL,
                "password": PASSWORD
            }
        }
        headers = {"Content-Type": "application/json"}

        response = requests.post(LOGIN_URL, json=payload, headers=headers)

        if response.status_code == 200:
            new_token = response.json()
            if new_token:
                print(f"✅ New API Token obtained: {new_token[:10]}...")  # Show only part of the token
                self.update_env_var("API_TOKEN", new_token)  # Update .env
                return new_token
            else:
                print("❌ Failed to retrieve new token.")
        else:
            print(f"❌ Login failed! Status Code: {response.status_code}, Response: {response.text}")

        return None

    def make_api_request(self, url):
        """Make an API request and refresh token if unauthorized."""
        api_token = os.getenv("API_TOKEN")
        headers = {"Authorization": f"Bearer {api_token}"}

        response = requests.get(url, headers=headers)

        if response.status_code == 401:  # Unauthorized, need new token
            print("🔄 API Token expired. Fetching a new one...")
            new_token = self.get_new_api_token()
            if new_token:
                headers["Authorization"] = f"Bearer {new_token}"
                response = requests.get(url, headers=headers)  # Retry with new token

        return response.json()


