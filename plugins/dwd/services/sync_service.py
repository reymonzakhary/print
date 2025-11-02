import json
import os

from flask import request, jsonify
from flask_restful import Resource
from collections import defaultdict
from datetime import datetime
import requests
import re
from models.supplierBoops import SupplierBoops
from models.secrets import Secret

# --- Service and constants inside the same file ---

def get_header(tenant_id):
    secrets = Secret.objects(tenant_id=tenant_id).first()
    if not secrets:
        return {
            "header": {
                "API-Secret": os.environ.get('API_SECRET'),
                "User-ID": os.environ.get('USER_ID'),
                "accept": "application/vnd.printdeal-api.v2"
            },
            "url": os.environ.get('URL') or 'https://api.printdeal.com/api',
        }
    header = {
        "API-Secret": secrets.dwd_secret,
        "User-ID": secrets.dwd_user_id,
        "accept": "application/vnd.printdeal-api.v2"
    }
    return {
        "header": header,
        "url": secrets.dwd_url,
    }

class DwdSyncService:
    def __init__(self):
        pass

    def get_category_uuid_by_name(self, name: str, tenant_id: str) -> dict:
        secrets = get_header(tenant_id)
        headers = secrets.get('header')
        url = secrets.get('url')
        response = requests.get(f"{url}/products/categories", headers=headers)
        response.raise_for_status()
        categories = response.json()
        for cat in categories:
            if cat["name"].lower() == name.lower():
                return cat
        raise ValueError(f"Category '{name}' not found")

    def get_attributes(self, uuid: str, tenant_id: str) -> dict:
        secrets = get_header(tenant_id)
        url = secrets.get('url'),
        headers = secrets.get('header')
        response = requests.get(
            f"{url}/products/{uuid}/attributes",
            headers=headers
        )
        response.raise_for_status()
        return response.json()

    def get_combinations(self, uuid: str, tenant_id: str) -> list:
        secrets = get_header(tenant_id)
        url = secrets.get('url'),
        header = secrets.get('header')

        response = requests.get(
            f"{url}/products/{uuid}/combinations",
            headers=header
        )
        response.raise_for_status()
        return response.json()

    def to_camel_case(self, s: str) -> str:
        s = re.sub(r'[^a-zA-Z0-9 ]', '', s)
        parts = s.strip().split(" ")
        return parts[0].lower() + ''.join(word.capitalize() for word in parts[1:]) if parts else s.lower()

    def to_snake_case(self, s: str) -> str:
        s = re.sub(r'[^a-zA-Z0-9 ]', '', str(s)).replace(' ', '_')
        return s.lower()


dwd_service = DwdSyncService()


# --- Resource Classes (your endpoints) ---

class ValidatePair(Resource):
    def post(self):
        payload = request.get_json()

        uuid = payload.get("uuid")
        attr1 = payload.get("attr1")
        val1 = payload.get("val1")
        attr2 = payload.get("attr2")
        val2 = payload.get("val2")
        tenant_id = payload.get("tenant_id")

        if not all([uuid, attr1, val1, attr2, val2]):
            return {
                "valid": False,
                "message": "Missing one or more required fields: uuid, attr1, val1, attr2, val2"
            }

        try:
            combinations = dwd_service.get_combinations(uuid, tenant_id)
        except Exception as e:
            return {
                "valid": False,
                "message": f"Failed to load combinations: {str(e)}"
            }

        match_found = False

        for combo in combinations:
            try:
                attributes = combo["product"]["attributes"]
                combo_dict = {item["attribute"]: item["value"] for item in attributes}
                if combo_dict.get(attr1) == val1 and combo_dict.get(attr2) == val2:
                    match_found = True
                    break
            except Exception as e:
                print(f"Skipping invalid combo: {e}")
                continue

        if match_found:
            return {
                "valid": True,
                "message": f'"{val1}" can be used with "{val2}"'
            }
        else:
            return {
                "valid": False,
                "message": f'"{val1}" cannot be used with "{val2}"'
            }


class GetPrice(Resource):
    dwd_service = DwdSyncService()

    def post(self):
        data = request.get_json()
        sku = data.get("sku")
        tenant_id = data.get("tenant_id")
        secrets = get_header(tenant_id)
        attributes = data.get("options")

        header = secrets.get("header")
        url = secrets.get("url")

        if not sku or not attributes or not tenant_id:
            print("[GetPrice] Missing required fields:", {
                "sku": bool(sku),
                "attributes": bool(attributes),
                "tenant_id": bool(tenant_id)
            })
            return {
                "data": [],
                "message": "Missing sku or attributes",
                "status": 422
            }

        if str(sku).lower() == 'mugs':
            for attr in attributes['attributes']:
                if attr.get('attribute') == 'Type Of Glass / Porcelain':
                    attributes['attributes'].append({
                        'attribute': 'Type Of Glass Or Porcelain',
                        'value': attr['value']
                    })
                    break

        price_url = url + "/products/{}"
        print("[GetPrice] Base price URL:", price_url)

        # List of delivery types
        delivery_types = ["Normal", "Express", "Next Day"]
        results = []

        try:
            category_info = self.dwd_service.get_category_uuid_by_name(sku, tenant_id)
            print("[GetPrice] Category lookup result:", category_info)
            uuid = category_info['sku']
            print("[GetPrice] Using UUID:", uuid)

            for delivery_type in delivery_types:
                # Clone attributes to avoid modifying the same dict
                current_attributes = {
                    "attributes": list(attributes["attributes"])  # shallow copy
                }

                # Remove existing 'Delivery Type' if exists
                current_attributes["attributes"] = [
                    attr for attr in current_attributes["attributes"]
                    if attr["attribute"] != "Delivery Type"
                ]

                # Append the new delivery type
                current_attributes["attributes"].append({
                    "attribute": "Delivery Type",
                    "value": delivery_type
                })

                try:
                    # Make the POST request
                    response = requests.post(
                        price_url.format(uuid),
                        headers=header,
                        json=current_attributes
                    )

                    response.raise_for_status()

                    result_data = response.json()

                    # Only append if data is not empty or invalid
                    if result_data and isinstance(result_data, dict):
                        result_data["DeliveryType"] = delivery_type
                        results.append(result_data)

                except requests.RequestException as e:
                    print(f"Request failed for {delivery_type}: {str(e)}")
                except ValueError as e:
                    print(f"JSON parse failed for {delivery_type}: {str(e)}")

            return results

        except requests.HTTPError as e:
            return {
                "data": [],
                "message": str(e),
                "status": response.status_code
            }

class GetSecrets(Resource):
    def get(self):
        data = request.get_json(force=True)
        tenant_id = data.get("tenant_id")
        secrets = Secret.objects(tenant_id=tenant_id).first()
        if not secrets:
            return {
                "message": "",
                "data": {
                    'dwd-url': os.environ.get('URL'),
                    'dwd-secret': os.environ.get('API_SECRET'),
                    'dwd-user-id': os.environ.get('USER_ID'),
                }
            }
        return {
            "message": "",
            "data": {
                'dwd-url': secrets.dwd_url,
                'dwd-secret': secrets.dwd_secret,
                'dwd-user-id': secrets.dwd_user_id,
            }
        }

class GetCategory(Resource):
    def post(self):
        try:
            payload = request.get_json()
            
            # Step 1: Sync data from Go API
            go_api_url = "http://dwd-exclude:5000/sync"
            sync_response = requests.post(go_api_url, json=payload)
            
            if sync_response.status_code != 200:
                return {
                    "data": [],
                    "message": "Failed to fetch category from Go API",
                    "details": sync_response.text,
                    "status": sync_response.status_code
                }

            # Ensure the response has a 'data' key for consistency
            sync_data = sync_response.json()
            if 'data' not in sync_data:
                sync_data = {"data": [sync_data]}

            # Step 2: Import the synced data
            try:
                if sync_data.get('data') and len(sync_data['data']) > 0:
                    import_payload = sync_data['data'][0]

                    # Normalize critical fields from upstream response
                    import_payload['name'] = import_payload.get('name') or import_payload.get('titleSingle')
                    import_payload['sku'] = import_payload.get('sku') or import_payload.get('id') or import_payload.get('name')

                    import_payload.update({
                        'tenant_id': payload.get('tenant_id'),
                        'tenant_name': payload.get('tenant_name'),
                        'vendor': payload.get('vendor')
                    })
                    
                    # Call the import endpoint internally
                    from external.external_dwd_supplier import ExternalDWDSupplier
                    import_resource = ExternalDWDSupplier()
                    
                    # Temporarily replace request data for import
                    original_data = request.get_json()
                    request._cached_json = {False: import_payload}
                    
                    import_result = import_resource.post()
                    
                    # Extract JSON data from response if it's a Response object
                    if hasattr(import_result, 'get_json'):
                        import_data = import_result.get_json()
                    elif hasattr(import_result, 'data'):
                        import_data = import_result.data
                    else:
                        import_data = str(import_result)
                    
                    # Restore original request data
                    request._cached_json = {False: original_data}
                    
                    return {
                        "data": sync_data['data'],
                        "import_result": import_data,
                        "message": "Data synced and imported successfully",
                        "status": 200
                    }
                else:
                    return {
                        "data": [],
                        "message": "No data to import",
                        "status": 400
                    }
                    
            except Exception as import_error:
                return {
                    "data": sync_data['data'],
                    "message": f"Sync successful but import failed: {str(import_error)}",
                    "status": 206  # Partial success
                }
            
        except Exception as e:
            return {
                "data": [],
                "message": f"Exception occurred: {str(e)}",
                "status": 500
            }



class GetCategories(Resource):
    def get(self):
        try:
            payload = request.get_json()
            tenant_id = payload.get("tenant_id")
            secrets = get_header(tenant_id)
            url = secrets.get('url')
            headers = secrets.get('header')
            response = requests.get(f"{url}/products/categories",data={}, headers=headers)
            response.raise_for_status()
            categories = []
            for category in response.json():
                categories.append({
                    "active": True,
                    "name": category['name'],
                    "sku": category.get('sku') or category.get('name'),
                    "createdAt": category['combinationsModifiedAt'],
                    "introductionDate": category['combinationsModifiedAt'],
                    "titlePlural": category['name'],
                    "titleSingle": category['sku'],
                    "updatedAt": category['combinationsModifiedAt']

                })
            return {"data": categories}
        except Exception as e:
            return {
                "data": [],
                "message": f"Failed to fetch categories: {str(e)}",
                "status": 400
            }
