from anyio import sleep
from config import API_URL, API_TOKEN, PROBO_API_URL, PROBO_API_TOKEN, DB_NAME, PASS_THROUGH_PROXY, PROXY_HOSTNAME
from pipeline.secrets_service import SecretService
from pipeline.utils import UtilsService
import requests
from time import sleep
from fastapi import HTTPException

PROXIES = {
    "http": PROXY_HOSTNAME,
    "https": PROXY_HOSTNAME,
}

# Create a session and set the proxy
requestsCustom = requests.Session()

# Attach a proxy to the requests object
if 'True' == PASS_THROUGH_PROXY:
    requestsCustom.proxies.update(PROXIES)
    requestsCustom.verify = False

class APIService:
    """Handles API requests for fetching product data."""

    def __init__(self, client, tenant_id):
        """Initialize APIService with necessary headers."""
        self.client = client
        self.secret_service = SecretService(client)
        self.tenant_id = tenant_id
        self.db = client[DB_NAME]
        self.plugin_secrets = self.db["secrets"]

        # Fetch secret data
        secret_data = self.plugin_secrets.find_one({"tenant_id": self.tenant_id})

        if not secret_data:
            raise HTTPException(status_code=404, detail=f"Tenant {self.tenant_id} not found in secrets collection.")

        # Retrieve API URL and Token safely
        self.api_url = secret_data.get("url")
        self.api_token = secret_data.get("token")

        self.retried = False  # Prevent infinite loops
        self.api_token = None  # Store token to avoid unnecessary refreshes

        # Fetch API credentials once (without forcing refresh)
        self.load_credentials()

    def fetch_products(self):
        """Fetch all product data from the API with token refresh handling."""
        response = requests.get(f"{self.api_url}/products", headers=self.print_com_headers)

        if response.status_code == 401 and not self.retried:
            print("❌ API token expired. Fetching new token...")

            secret_data = self.secret_service.login(self.tenant_id, True)
            self.api_url = secret_data.url
            self.api_token = secret_data.token

            # sleep(3)  # Small delay to allow the API to refresh
            if self.api_token:

                self.print_com_headers["Authorization"] = f"Bearer {self.api_token}"
                self.retried = True  # ✅ Prevent infinite loops
                return self.fetch_products()  # ✅ Retry once

        return response.json() if response.status_code == 200 else []

    def fetch_price(self, product_sku, product_options):
            """Fetch product price data from the API."""

            headers = {"Authorization": f"Bearer {self.api_token}"}

            product_price_response = requestsCustom.post(
                "{}/products/{}/price".format(self.api_url, product_sku),
                headers=headers,
                json={
                    "sku": product_sku,
                    "options": product_options
                }
            )

            if product_price_response.status_code == 401 and not self.retried:
                print("❌ API token expired. Fetching new token...")

                secret_data = self.secret_service.login(self.tenant_id, True)
                self.api_url = secret_data.url
                self.api_token = secret_data.token

                # sleep(3)  # Small delay to allow the API to refresh
                if self.api_token:
                    self.print_com_headers["Authorization"] = f"Bearer {self.api_token}"
                    self.retried = True  # ✅ Prevent infinite loops
                    return self.fetch_price(product_sku, product_options)  # ✅ Retry once

            json_data = product_price_response.json()

            if product_price_response.status_code != 200:
                raise Exception(json_data["errorMessage"])

            return json_data

    def place_order_printcom(self, order_payload: dict):
        """Place order to printcom via API."""
        url = f"{self.api_url}/orders"

        headers = {
            "Authorization": f"Bearer {self.api_token}",
            "Accept": "application/json",
            "Content-Type": "application/json"
        }

        response = requestsCustom.post(
            url,
            headers=headers,
            json=order_payload
        )

        try:
            data = response.json()
        except Exception:
            data = {"raw": response.text}

        if response.status_code != 200:
            raise HTTPException(
                status_code=response.status_code,
                detail=f"Print.com ORDER ERROR: {data.get('message', data)}"
            )

        return data

    def fetch_shipping_possibilities(self, product_sku, product_options, address_data):
            """Fetch product shipping-possibilities data from the API."""

            headers = {"Authorization": f"Bearer {self.api_token}"}

            shipping_possibilities_response = requestsCustom.post(
                "{}/products/{}/shipping-possibilities".format(self.api_url, product_sku),
                headers=headers,
                json={
                    "item": {
                        "options": product_options,
                        "sku": product_sku
                    },

                    "address": address_data
                }
            )

            json_data = shipping_possibilities_response.json()

            if shipping_possibilities_response.status_code != 200:
                raise Exception(json_data["errorMessage"])

            return json_data["results"]

    def load_credentials(self):
            """Load credentials from the database (without refreshing token)."""
            secret_data = self.secret_service.login(self.tenant_id)
            self.api_url = secret_data.url
            self.api_token = secret_data.token
            self.print_com_headers = {"Authorization": f"Bearer {self.api_token}"}

    def refresh_token(self):
            """Fetch a new token when the old one expires."""
            secret_data = self.secret_service.login(self.tenant_id)

            new_secret_data = self.secret_service.check_auth(
                secret_data.username,
                secret_data.password,
                secret_data.url,
            )

            new_token = new_secret_data["token"]
            print("✅ New Token Retrieved:", new_token)

            # Update token in database
            self.secret_service.plugin_secrets.update_one(
                {"tenant_id": self.tenant_id},
                {"$set": {"token": new_token}}
            )

            # Update local headers
            self.api_token = new_token
            self.print_com_headers["Authorization"] = f"Bearer {self.api_token}"

    def fetch_origin_products(self, sku):
            """Fetch product options and details from API."""
            products_url = f"{self.api_url}/products/{sku}"
            products_details_url = f"{self.api_url}/products/{sku}?view=reseller"

            products_response = requests.get(products_url, headers=self.print_com_headers)
            details_response = requests.get(products_details_url, headers=self.print_com_headers)

            # If token expired (401) and hasn't been retried yet
            if (products_response.status_code == 401 or details_response.status_code == 401) and not self.retried:
                print("❌ API token expired. Fetching new token...")
                sleep(2)  # Small delay to allow API to sync

                try:
                    self.refresh_token()  # 🔄 Only refresh if needed
                    self.retried = True  # ✅ Prevent infinite loops
                    return self.fetch_origin_products(sku)  # ✅ Retry once

                except Exception as e:
                    raise HTTPException(status_code=401, detail=f"Token refresh failed: {str(e)}")

            if products_response.status_code == 200 and details_response.status_code == 200:
                return {
                    "products": products_response.json(),
                    "products_details": details_response.json(),
                }

            return {"products": [], "products_details": []}  # Return empty if request fails

    def sync_print_com(self, data):
        properties_list = data["properties"]
        excludes_list = data["excludes"]

        # Lookup tables
        option_lookup = {}  # Maps option slugs to their objects
        option_to_property = {}  # Maps option slugs to their parent property
        property_groups = {}  # Tracks options by property

        # Initialize lookup tables
        for prop in properties_list:
            property_slug = prop["slug"]
            for option in prop.get("options", []):
                option_slug = option["slug"]
                option_lookup[option_slug] = option  # Store reference
                option_to_property[option_slug] = property_slug  # Track parent property
                property_groups.setdefault(property_slug, []).append(option_slug)  # Group options by property
                option.setdefault("excludes", [])  # Ensure excludes key exists

        # Process exclusions
        for exclude_group in excludes_list:
            group_options = {}
            seen_properties = set()

            for prop in exclude_group:
                property_name = prop["property"]
                seen_properties.add(property_name)
                for option_slug in prop.get("options", []):
                    if option_slug in option_lookup:
                        group_options.setdefault(property_name, []).append(option_slug)

            # If only two properties involved, apply reciprocal exclusions
            if len(seen_properties) == 2:
                prop_list = list(group_options.keys())
                if len(prop_list) < 2:
                    continue  # Ensure we have two properties before proceeding
                prop_a, prop_b = prop_list[0], prop_list[1]

                for option_a in group_options[prop_a]:
                    for option_b in group_options[prop_b]:
                        if option_b not in option_lookup[option_a]["excludes"]:
                            option_lookup[option_a]["excludes"].append([option_b])
                        if option_a not in option_lookup[option_b]["excludes"]:
                            option_lookup[option_b]["excludes"].append([option_a])

            # If more than two properties involved, apply grouped exclusions
            elif len(seen_properties) > 2:
                options_per_property = list(group_options.values())
                all_excluded_combinations = []

                for i in range(len(options_per_property)):
                    for j in range(i + 1, len(options_per_property)):
                        for option_a in options_per_property[i]:
                            for option_b in options_per_property[j]:
                                all_excluded_combinations.append([option_a, option_b])

                for prop, options in group_options.items():
                    for option in options:
                        for exclusion in all_excluded_combinations:
                            if option not in exclusion:
                                if exclusion not in option_lookup[option]["excludes"]:
                                    option_lookup[option]["excludes"].append(exclusion)

        # Ensure no option excludes itself or other options in the same property
        for option_slug, option in option_lookup.items():
            property_name = option_to_property[option_slug]
            option["excludes"] = [ex for ex in option["excludes"] if
                                  option_slug not in ex and not any(e in property_groups[property_name] for e in ex)]

        return data

    """ Start Propo """
    def fetch_proboprints_products(self):
        """Fetch product data from ProboPrints API."""
        response = requests.get(PROBO_API_URL, headers=self.probo_com_headers)
        return response.json() if response.status_code == 200 else None
