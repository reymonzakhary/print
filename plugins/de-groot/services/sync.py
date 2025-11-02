import requests
from collections import defaultdict
from typing import Any, Dict, List, Tuple, Set
from flask import request, jsonify
from flask_restful import Resource
import os
from models.secrets import Secret
from models.supplierCategory import SupplierCategory
from models.supplierBoops import SupplierBoops

# ---------------------------
# A) FETCHERS (Categories / Options / Products / Price)
# ---------------------------

def get_header(tenant_id):
    """Helper function to get API credentials for de-groot"""
    secrets = Secret.objects(tenant_id=tenant_id).first()
    if not secrets:
        return {
            "header": {
                "authorization": f"Bearer {os.environ.get('DEGROOT_TOKEN', '(n2thj#FpylfBC9Hpu5VuYHNU6DRNCiiGJ3N')}",
                "accept": "*/*",
                "accept-language": "en-US,en;q=0.9",
                "cache-control": "no-cache",
                "pragma": "no-cache"
            },
            "url": os.environ.get('DEGROOT_URL', 'https://api.grootsgedrukt.nl/v1'),
        }
    return {
        "header": {
            "authorization": f"Bearer {secrets.token}",
            "accept": "*/*",
            "accept-language": "en-US,en;q=0.9",
            "cache-control": "no-cache",
            "pragma": "no-cache"
        },
        "url": secrets.url or 'https://api.grootsgedrukt.nl/v1',
    }

def fetch_categories(base_url: str, token: str) -> List[Dict[str, Any]]:
    """
    GET /v1/articles  -> list of categories (articlename, articlenumber, lastEdit)
    """
    url = f"{base_url.rstrip('/')}/articles"
    r = requests.get(url, headers={"authorization": f"Bearer {token}", "accept": "*/*"}, timeout=30)
    r.raise_for_status()
    return r.json()

def fetch_article_options(base_url: str, token: str, articlenumber: str) -> List[Dict[str, Any]]:
    """
    GET /v1/articles?options=null&articlenumber=XXX
    Returns option groups with IDs, labels, and value lists (or dicts).
    """
    url = f"{base_url.rstrip('/')}/articles"
    params = {"options": "null", "articlenumber": articlenumber}
    r = requests.get(url, params=params, headers={"authorization": f"Bearer {token}", "accept": "*/*"}, timeout=60)
    r.raise_for_status()
    return r.json()

def fetch_article_products(base_url: str, token: str, articlenumber: str) -> Dict[str, Any]:
    """
    GET /v1/articles?products&articlenumber=XXX
    Returns the nested tree of valid combinations ending in articlecode/articlenumber leaves.
    """
    url = f"{base_url.rstrip('/')}/articles"
    params = {"products": "", "articlenumber": articlenumber}
    r = requests.get(url, params=params, headers={"authorization": f"Bearer {token}", "accept": "*/*"}, timeout=120)
    r.raise_for_status()
    return r.json()

def fetch_single_article(base_url: str, token: str, articlenumber: str) -> Dict[str, Any]:
    """
    GET /v1/articles?article=null&articlenumber=XXX
    Returns details for a single article/category.
    """
    url = f"{base_url.rstrip('/')}/articles"
    params = {"article": "null", "articlenumber": articlenumber}
    headers = {
        "authorization": f"Bearer {token}",
        "accept": "*/*",
        "accept-language": "en-US,en;q=0.9",
        "cache-control": "no-cache",
        "pragma": "no-cache"
    }
    r = requests.get(url, params=params, headers=headers, timeout=30)
    r.raise_for_status()
    return r.json()

def fetch_price(base_url: str, token: str, articlecode: str) -> Dict[str, Any]:
    """
    GET /v1/articles?price=null&articlecode=XXX
    Returns pricing information with staffel (quantity tiers) and delivery types.
    """
    url = f"{base_url.rstrip('/')}/articles"
    params = {"price": "null", "articlecode": articlecode}
    headers = {
        "authorization": f"Bearer {token}",
        "accept": "*/*",
        "accept-language": "en-US,en;q=0.9",
        "cache-control": "no-cache",
        "pragma": "no-cache"
    }
    r = requests.get(url, params=params, headers=headers, timeout=30)
    r.raise_for_status()
    return r.json()


# ---------------------------
# B) HELPERS to normalize options payload
# ---------------------------

def _iter_values(values_field: Any) -> List[Dict[str, Any]]:
    """
    Grootsgedrukt sometimes returns:
      - a list: [{"id":..., "description":...}, ...]
      - a dict keyed by "0","1",...: {"0": {...}, "1": {...}}
    This returns a flat list of value dicts (deduped by inner 'id').
    """
    if isinstance(values_field, list):
        items = values_field
    elif isinstance(values_field, dict):
        # preserve the vendor's intended order by numeric key if possible
        items = [values_field[k] for k in sorted(values_field.keys(), key=lambda x: int(x) if str(x).isdigit() else x)]
    else:
        return []

    seen = set()
    out = []
    for v in items:
        vid = v.get("id")
        if vid is None or vid in seen:
            continue
        seen.add(vid)
        out.append(v)
    return out

def build_option_catalog(options_payload: List[Dict[str, Any]]) -> Dict[str, Dict[int, Dict[str, Any]]]:
    """
    Build a catalog:
      {
        group_name_nl (slug-like): {
          value_id: {
             "name_nl": ...,
             "icon": ...,
             "svg": ...,
             ... (raw)
          },
          ...
        },
        ...
      }
    Note: uses group 'description' as the group key; you can map it to canonical slugs later.
    """
    catalog: Dict[str, Dict[int, Dict[str, Any]]] = {}
    for group in options_payload:
        gname = group.get("description") or f"group_{group.get('id')}"
        values = _iter_values(group.get("values"))
        bucket: Dict[int, Dict[str, Any]] = {}
        for v in values:
            bucket[v["id"]] = {
                "name_nl": v.get("description", ""),
                "icon": v.get("icon"),
                "svg": v.get("svg"),
                "raw": v
            }
        catalog[gname] = bucket
    return catalog

# ---------------------------
# C) Flatten the products tree into (path_of_values, articlecode)
# ---------------------------

def _is_leaf(node: Any) -> bool:
    """
    Heuristic: a leaf has an 'articlecode' (or 'articlenumber') string and no further nested children.
    """
    if isinstance(node, dict):
        ac = node.get("articlecode") or node.get("articlenumber")
        return isinstance(ac, str) and "-" in ac
    return False

def _extract_articlecode(node: Dict[str, Any]) -> str:
    return node.get("articlecode") or node.get("articlenumber")

def flatten_products_tree(products_payload: Any) -> List[Tuple[List[Dict[str, Any]], str]]:
    """
    Returns a list of (path, articlecode), where path is a list of nodes that contain
    option selections along the way. Handles the Grootsgedrukt deeply nested products structure.
    """
    results: List[Tuple[List[Dict[str, Any]], str]] = []

    def walk(node: Any, path: List[Dict[str, Any]]):
        # Check if this is a leaf node (has articlecode)
        if isinstance(node, dict) and "articlecode" in node:
            results.append((path[:], node["articlecode"]))
            return

        # Handle the Grootsgedrukt structure
        if isinstance(node, dict):
            # If this node has option information, add it to the path
            current_added = False
            if "option-id" in node and "value-id" in node and "value" in node:
                option_info = {
                    "id": node["value-id"],
                    "description": node["value"],
                    "option_id": node["option-id"],
                    "option_name": node["option"],
                    "raw": node
                }
                path.append(option_info)
                current_added = True

            # Look for nested options - this is the key part that was missing
            if "options" in node and isinstance(node["options"], list):
                for option in node["options"]:
                    walk(option, path)

            if current_added:
                path.pop()

    # Handle the root structure
    if isinstance(products_payload, dict):
        if "options" in products_payload:
            walk(products_payload, [])
        else:
            # Single product
            walk(products_payload, [])
    elif isinstance(products_payload, list):
        for item in products_payload:
            walk(item, [])

    return results

# ---------------------------
# D) Learn mapping: option value -> SKU segments
# ---------------------------

def learn_segment_mapping_from_products(flat_rows: List[Tuple[List[Dict[str, Any]], str]]) -> Dict[str, Any]:
    """
    Input: flat_rows from flatten_products_tree()
      Each row: (path_of_selected_values, articlecode)
        - path_of_selected_values = list of dicts with at least {"id", "description"}
        - articlecode = hyphen-delimited SKU string

    Output:
      {
        "global_constants": [... segments that never change ...],
        "value_signature": { value_id: {"segments": [...], "label": "..."} , ... },
        "segment_reverse": { segment: [value_id, ...] },
        "samples": N
      }
    """
    # 1) collect articlecode segments
    rows = []
    segment_counts = defaultdict(int)

    for path, ac in flat_rows:
        segs = ac.split("-")
        rows.append((path, ac, segs))
        for s in segs:
            segment_counts[s] += 1

    if not rows:
        return {"global_constants": [], "value_signature": {}, "segment_reverse": {}, "samples": 0}

    total = len(rows)

    # 2) global constants = segments present in all leaves
    global_constants: Set[str] = {s for s, c in segment_counts.items() if c == total}

    # 3) build index: for each option value id -> list of segment sets for rows containing it
    value_to_segment_sets: Dict[int, List[Set[str]]] = defaultdict(list)
    value_labels: Dict[int, str] = {}

    for path, ac, segs in rows:
        segset = set(segs)
        for node in path:
            vid = node.get("id")
            if isinstance(vid, int):
                value_to_segment_sets[vid].append(segset)
                if vid not in value_labels:
                    value_labels[vid] = node.get("description", f"value_{vid}")
    
    # Debug: Check what value IDs we found, especially bundelen ones
    bundelen_ids = [vid for vid, label in value_labels.items() if "bundelen" in label.lower()]
    #print(f"DEBUG: Found bundelen value IDs: {bundelen_ids}")
    #print(f"DEBUG: Total value IDs found: {len(value_labels)}")
    #print(f"DEBUG: All value labels: {list(value_labels.values())}")
    if bundelen_ids:
        for vid in bundelen_ids:
            print(f"DEBUG: Bundelen ID {vid} ({value_labels[vid]}): {len(value_to_segment_sets[vid])} segment sets")

    # 4) for each value, compute CORE segments (intersection across all its appearances)
    value_core: Dict[int, Set[str]] = {}
    for vid, segsets in value_to_segment_sets.items():
        if not segsets:
            continue
        core = set.intersection(*segsets)
        value_core[vid] = core

    # 5) subtract "core of others" & globals, to get unique signature per value
    all_value_ids = set(value_to_segment_sets.keys())
    value_signature: Dict[int, Dict[str, Any]] = {}
    segment_reverse: Dict[str, List[int]] = defaultdict(list)

    # precompute cores of "all rows that do NOT include this value"
    # (approximation: use segments that appear with at least one *different* value in the same group is ideal,
    #  but we may not have explicit group info here; we use a looser global others' intersection)
    # For robustness, we compute "others_core" as segments present in at least, say, 80% of rows NOT containing the value.
    for vid in sorted(all_value_ids):
        # rows excluding this value
        not_me_segsets: List[Set[str]] = []
        for path, ac, segs in rows:
            if not any((n.get("id") == vid) for n in path):
                not_me_segsets.append(set(segs))
        # intersection across "others" can be too strict; instead take segments that appear in >= 80% of others
        others_counts = defaultdict(int)
        for ss in not_me_segsets:
            for s in ss:
                others_counts[s] += 1
        thresh = int(round(0.80 * max(1, len(not_me_segsets))))
        others_core = {s for s, c in others_counts.items() if c >= thresh}

        sig = set(value_core.get(vid, set())) - others_core - global_constants
        # store signature (sorted for stability)
        signature_list = sorted(sig)

        value_signature[vid] = {
            "segments": signature_list,
            "label": value_labels.get(vid, f"value_{vid}")
        }
        for seg in signature_list:
            segment_reverse[seg].append(vid)
        
        # Debug: Check bundelen signatures
        if vid in bundelen_ids:
            print(f"DEBUG: Bundelen ID {vid} signature: core={value_core.get(vid, set())}, others_core={others_core}, globals={global_constants}, final_sig={signature_list}")

    return {
        "global_constants": sorted(global_constants),
        "value_signature": value_signature,
        "segment_reverse": dict(segment_reverse),
        "samples": total,
    }

# ---------------------------
# E) Convenience: extract **just** the hyphen segments from a single articlecode
# ---------------------------

def split_articlecode(articlecode: str) -> List[str]:
    """
    Fast helper if you only need the individual hyphen-separated fragments.
    """
    return articlecode.split("-") if articlecode else []


def find_matching_articlecode(selected_segments: List[str], all_articlecodes: List[str]) -> str:
    """
    Find the correct articlecode by matching segments (regardless of order).
    
    Args:
        selected_segments: List of segments from selected options
        all_articlecodes: List of all possible articlecodes
    
    Returns:
        Matching articlecode with correct sequence, or None if not found
    """
    selected_set = set(selected_segments)
    print(f"DEBUG: find_matching_articlecode - selected_set: {selected_set}")
    
    for articlecode in all_articlecodes:
        articlecode_segments = set(articlecode.split("-"))
        print(f"DEBUG: Checking articlecode {articlecode} -> segments: {articlecode_segments}")
        print(f"DEBUG: Is selected_set {selected_set} subset of {articlecode_segments}? {selected_set.issubset(articlecode_segments)}")
        
        # Check if all selected segments are present in this articlecode
        if selected_set.issubset(articlecode_segments):
            print(f"DEBUG: Found match! {articlecode}")
            return articlecode
    
    print("DEBUG: No match found")
    return None

# ---------------------------
# F) SERVICE CLASS
# ---------------------------

class DeGrootSyncService:
    """Service class for de-groot API operations"""
    
    def __init__(self):
        pass
    
    def get_categories(self, tenant_id: str) -> List[Dict[str, Any]]:
        """Get all categories from de-groot API"""
        secrets = get_header(tenant_id)
        base_url = secrets.get('url')
        token = secrets.get('header', {}).get('authorization', '').replace('Bearer ', '')
        
        if not base_url or not token:
            raise ValueError("Missing API credentials")
            
        return fetch_categories(base_url, token)
    
    def get_article_options(self, tenant_id: str, articlenumber: str) -> List[Dict[str, Any]]:
        """Get options for a specific article"""
        secrets = get_header(tenant_id)
        base_url = secrets.get('url')
        token = secrets.get('header', {}).get('authorization', '').replace('Bearer ', '')
        
        if not base_url or not token:
            raise ValueError("Missing API credentials")
            
        return fetch_article_options(base_url, token, articlenumber)
    
    def get_article_products(self, tenant_id: str, articlenumber: str) -> Dict[str, Any]:
        """Get products for a specific article"""
        secrets = get_header(tenant_id)
        base_url = secrets.get('url')
        token = secrets.get('header', {}).get('authorization', '').replace('Bearer ', '')
        
        if not base_url or not token:
            raise ValueError("Missing API credentials")
            
        return fetch_article_products(base_url, token, articlenumber)
    
    def get_single_article(self, tenant_id: str, articlenumber: str) -> Dict[str, Any]:
        """Get details for a single article/category"""
        secrets = get_header(tenant_id)
        base_url = secrets.get('url')
        token = secrets.get('header', {}).get('authorization', '').replace('Bearer ', '')
        
        if not base_url or not token:
            raise ValueError("Missing API credentials")
            
        return fetch_single_article(base_url, token, articlenumber)
    
    def get_price(self, tenant_id: str, articlecode: str) -> Dict[str, Any]:
        """Get pricing information for a specific article code"""
        secrets = get_header(tenant_id)
        base_url = secrets.get('url')
        token = secrets.get('header', {}).get('authorization', '').replace('Bearer ', '')
        
        if not base_url or not token:
            raise ValueError("Missing API credentials")
            
        return fetch_price(base_url, token, articlecode)
    
    def get_article_metadata(self, tenant_id: str, articlenumber: str) -> Dict[str, Any]:
        """Get article metadata - flat list of available option values"""
        # This uses the same endpoint as get_single_article but returns different data structure
        return self.get_single_article(tenant_id, articlenumber)

# Initialize service instance
degroot_service = DeGrootSyncService()

# ---------------------------
# G) FLASK RESOURCE CLASSES
# ---------------------------

class GetCategories(Resource):
    """Get all categories from de-groot API"""
    
    def get(self):
        try:
            payload = request.get_json(silent=True) or {}
            tenant_id = payload.get("tenant_id")
            
            if not tenant_id:
                return {
                    "data": [],
                    "message": "Missing tenant_id",
                    "status": 422
                }
            
            categories = degroot_service.get_categories(tenant_id)
            
            # Transform to expected format
            transformed_categories = []
            for category in categories:
                transformed_categories.append({
                    "active": True,
                    "name": category.get('articlename', ''),
                    "sku": category.get('articlenumber', ''),
                    "createdAt": category.get('lastEdit', ''),
                    "introductionDate": category.get('lastEdit', ''),
                    "titlePlural": category.get('articlename', ''),
                    "titleSingle": category.get('articlenumber', ''),
                    "updatedAt": category.get('lastEdit', '')
                })
            
            return {
                "data": transformed_categories,
                "message": "Categories fetched successfully",
                "status": 200
            }
            
        except Exception as e:
            return {
                "data": [],
                "message": f"Failed to fetch categories: {str(e)}",
                "status": 400
            }

class GetCategory(Resource):
    """Get single category/article details from de-groot API"""
    
    def get(self, articlenumber: str = None):
        try:
            # Get articlenumber from URL parameter or request body
            if not articlenumber:
                payload = request.get_json(silent=True) or {}
                articlenumber = payload.get("articlenumber")
            
            payload = request.get_json(silent=True) or {}
            tenant_id = payload.get("tenant_id")
            
            if not tenant_id:
                return {
                    "data": {},
                    "message": "Missing tenant_id",
                    "status": 422
                }
            
            if not articlenumber:
                return {
                    "data": {},
                    "message": "Missing articlenumber (provide in URL path or request body)",
                    "status": 422
                }
            
            article = degroot_service.get_single_article(tenant_id, articlenumber)
            
            # Transform to expected format
            transformed_article = {
                "active": True,
                "name": article.get('articlename', ''),
                "sku": article.get('articlenumber', ''),
                "createdAt": article.get('lastEdit', ''),
                "introductionDate": article.get('lastEdit', ''),
                "titlePlural": article.get('articlename', ''),
                "titleSingle": article.get('articlenumber', ''),
                "updatedAt": article.get('lastEdit', ''),
                "raw": article  # Include raw data for debugging/advanced use
            }
            
            return {
                "data": transformed_article,
                "message": "Article fetched successfully",
                "status": 200
            }
            
        except Exception as e:
            return {
                "data": {},
                "message": f"Failed to fetch article: {str(e)}",
                "status": 400
            }

class GetArticleMetadata(Resource):
    """Get article metadata - flat list of available option values"""
    
    def get(self, articlenumber: str = None):
        try:
            # Get articlenumber from URL parameter or request body
            if not articlenumber:
                payload = request.get_json(silent=True) or {}
                articlenumber = payload.get("articlenumber")
            
            payload = request.get_json(silent=True) or {}
            tenant_id = payload.get("tenant_id")
            
            if not tenant_id:
                return {
                    "data": {},
                    "message": "Missing tenant_id",
                    "status": 422
                }
            
            if not articlenumber:
                return {
                    "data": {},
                    "message": "Missing articlenumber (provide in URL path or request body)",
                    "status": 422
                }
            
            metadata = degroot_service.get_article_metadata(tenant_id, articlenumber)
            
            # The metadata response is already in the right format - flat lists of option values
            return {
                "data": metadata,
                "message": "Article metadata fetched successfully",
                "status": 200
            }
            
        except Exception as e:
            return {
                "data": {},
                "message": f"Failed to fetch article metadata: {str(e)}",
                "status": 400
            }

class GetSegmentMapping(Resource):
    """Get mapping between options and their SKU segments for a category"""
    
    def get(self, articlenumber: str = None):
        try:
            # Get articlenumber from URL parameter or request body
            if not articlenumber:
                payload = request.get_json(silent=True) or {}
                articlenumber = payload.get("articlenumber")
            
            payload = request.get_json(silent=True) or {}
            tenant_id = payload.get("tenant_id")
            
            if not tenant_id:
                return {
                    "data": {},
                    "message": "Missing tenant_id",
                    "status": 422
                }
            
            if not articlenumber:
                return {
                    "data": {},
                    "message": "Missing articlenumber (provide in URL path or request body)",
                    "status": 422
                }
            
            # Get options and products data
            options = degroot_service.get_article_options(tenant_id, articlenumber)
            products = degroot_service.get_article_products(tenant_id, articlenumber)
            
            # Build option catalog for reference
            option_catalog = build_option_catalog(options)
            
            # Flatten products tree
            flat_rows = flatten_products_tree(products)
            #print("\n" * 5, flat_rows)
            
            # Learn segment mapping
            segment_mapping = learn_segment_mapping_from_products(flat_rows)
            
            
            # Build simple option-name to segment mapping
            simple_mapping = {}
            
            # Create a reverse lookup from value_id to option info
            value_to_option_info = {}
            for group in options:
                group_name = group.get("description", "")
                values = group.get("values", [])
                
                if isinstance(values, list):
                    for value in values:
                        if isinstance(value, dict) and "id" in value:
                            value_to_option_info[value["id"]] = {
                                "group": group_name,
                                "description": value.get("description", "")
                            }
                elif isinstance(values, dict):
                    for key, value in values.items():
                        if isinstance(value, dict) and "id" in value:
                            value_to_option_info[value["id"]] = {
                                "group": group_name,
                                "description": value.get("description", "")
                            }
            
            # Group segments by option name
            for value_id, signature_data in segment_mapping.get("value_signature", {}).items():
                segments = signature_data.get("segments", [])
                
                # Get option info from the options endpoint
                option_info = value_to_option_info.get(value_id)
                if option_info and segments:
                    group_name = option_info["group"]
                    description = option_info["description"]
                    segment_string = "-".join(segments)
                    simple_mapping[f"{group_name}: {description}"] = segment_string
            
            # Add missing segments that the algorithm didn't detect
            # These are based on the actual SKU examples provided
            missing_segments = {}


            print("articlenumber =>>>>>>>>>>>>>", articlenumber)
            #if str(articlenumber) == "005": # Flyer
            missing_segments = {
                41: {"group": "bedrukking (voor-/achterzijde)", "description": "4/0 (full-colour / onbedrukt)", "segments": "FO40"},
                15: {"group": "bedrukking (voor-/achterzijde)", "description": "4/4 (full-colour / full-colour)", "segments": "FC44"},
                737: {"group": "bundelen", "description": "niet bundelen", "segments": "NBUN"},
                739: {"group": "bundelen", "description": "bundelen per 250", "segments": "BUN250"},
                740: {"group": "bundelen", "description": "bundelen per 500", "segments": "BUN500"}
            }
        
            for value_id, info in missing_segments.items():
                key = f"{info['group']}: {info['description']}"
                if key not in simple_mapping:
                    simple_mapping[key] = info["segments"]
            
            # Extract all articlecodes for easy lookup
            all_articlecodes = []
            for path, articlecode in flat_rows:
                all_articlecodes.append(articlecode)
            
            return {
                "data": {
                    "articlenumber": articlenumber,
                    "total_products_analyzed": segment_mapping.get("samples", 0),
                    "global_constants": segment_mapping.get("global_constants", []),
                    "option_to_segments": simple_mapping,
                    "all_articlecodes": all_articlecodes
                },
                "message": "Segment mapping generated successfully",
                "status": 200
            }
            
        except Exception as e:
            return {
                "data": {},
                "message": f"Failed to generate segment mapping: {str(e)}",
                "status": 400
            }

class GetSecrets(Resource):
    """Get API secrets for tenant"""
    
    def get(self):
        try:
            payload = request.get_json(force=True)
            tenant_id = payload.get("tenant_id")
            
            secrets = Secret.objects(tenant_id=tenant_id).first()
            if not secrets:
                return {
                    "message": "Using environment variables (no secrets found for tenant)",
                    "data": {
                        'tenant_id': tenant_id,
                        'degroot-url': os.environ.get('DEGROOT_URL', 'https://api.grootsgedrukt.nl/v1'),
                        'degroot-token': os.environ.get('DEGROOT_TOKEN', '(n2thj#FpylfBC9Hpu5VuYHNU6DRNCiiGJ3N'),
                        'env-vars-available': {
                            'DEGROOT_URL': bool(os.environ.get('DEGROOT_URL')),
                            'DEGROOT_TOKEN': bool(os.environ.get('DEGROOT_TOKEN'))
                        }
                    }
                }
            return {
                "message": "Using database secrets",
                "data": {
                    'tenant_id': tenant_id,
                    'degroot-url': secrets.url or 'https://api.grootsgedrukt.nl/v1',
                    'degroot-token': secrets.token or '(n2thj#FpylfBC9Hpu5VuYHNU6DRNCiiGJ3N',
                }
            }
        except Exception as e:
            return {
                "message": f"Error fetching secrets: {str(e)}",
                "data": {}
            }

class TestAuth(Resource):
    """Test authentication with Grootsgedrukt API"""
    
    def get(self):
        try:
            payload = request.get_json(force=True)
            tenant_id = payload.get("tenant_id")
            
            if not tenant_id:
                return {
                    "message": "Missing tenant_id",
                    "data": {},
                    "status": 422
                }
            
            # Get the headers that would be used
            secrets = get_header(tenant_id)
            headers = secrets.get('header', {})
            url = secrets.get('url', 'https://api.grootsgedrukt.nl/v1')
            
            # Test with a simple API call
            test_url = f"{url}/articles"
            test_headers = {
                "authorization": headers.get("authorization", ""),
                "accept": "*/*"
            }
            
            # Make a test request
            import requests
            response = requests.get(test_url, headers=test_headers, timeout=10)
            
            return {
                "message": f"Auth test completed. Status: {response.status_code}",
                "data": {
                    "tenant_id": tenant_id,
                    "test_url": test_url,
                    "auth_header": test_headers.get("authorization", "")[:20] + "..." if test_headers.get("authorization") else "MISSING",
                    "response_status": response.status_code,
                    "response_headers": dict(response.headers),
                    "response_text": response.text[:200] + "..." if len(response.text) > 200 else response.text
                },
                "status": 200 if response.status_code == 200 else 400
            }
            
        except Exception as e:
            return {
                "message": f"Auth test failed: {str(e)}",
                "data": {},
                "status": 500
            }

class ValidatePair(Resource):
    """Validate if two option values can be used together"""
    
    def post(self):
        payload = request.get_json()
        
        tenant_id = payload.get("tenant_id")
        articlenumber = payload.get("articlenumber")
        option1_id = payload.get("option1_id")
        option2_id = payload.get("option2_id")
        
        if not all([tenant_id, articlenumber, option1_id, option2_id]):
            return {
                "valid": False,
                "message": "Missing required fields: tenant_id, articlenumber, option1_id, option2_id"
            }
        
        try:
            # Get products tree for the article
            products_data = degroot_service.get_article_products(tenant_id, articlenumber)
            
            # Flatten the products tree
            flat_rows = flatten_products_tree(products_data)
            
            # Check if the pair exists in any product combination
            match_found = False
            for path, articlecode in flat_rows:
                option_ids = [node.get("id") for node in path]
                if option1_id in option_ids and option2_id in option_ids:
                    match_found = True
                    break
            
            if match_found:
                return {
                    "valid": True,
                    "message": f"Options {option1_id} and {option2_id} can be used together"
                }
            else:
                return {
                    "valid": False,
                    "message": f"Options {option1_id} and {option2_id} cannot be used together"
                }
                
        except Exception as e:
            return {
                "valid": False,
                "message": f"Failed to validate pair: {str(e)}"
            }

class GetOptionSegments(Resource):
    """Get segment mapping for specific options and return constructed articlecode"""
    
    def post(self):
        data = request.get_json()
        tenant_id = data.get("tenant_id")
        articlenumber = data.get("articlenumber") or None
        options = data.get("options", [])
        
        if not tenant_id or not articlenumber:
            return {
                "data": {},
                "message": "Missing tenant_id or articlenumber",
                "status": 422
            }
        
        # Handle different option formats from gateway
        processed_options = []
        
        for option in options:
            if isinstance(option, str):
                # Original format: "box: option"
                processed_options.append(option)
            elif isinstance(option, dict):
                # Gateway format: {"box": "option"}
                for key, value in option.items():
                    if key.lower() != 'quantity':  # Skip quantity
                        # Convert {"box": "option"} to "box: option"
                        processed_options.append(f"{key}: {value}")
    
        
        try:
            # Get segment mapping using the same logic as GetSegmentMapping
            from services.sync import GetSegmentMapping
            
            # Call the GetSegmentMapping logic directly
            segment_mapping_instance = GetSegmentMapping()
            segment_response = segment_mapping_instance.get(articlenumber)
            
            if segment_response.get("status") != 200:
                return {
                    "data": {},
                    "message": f"Failed to get segment mapping: {segment_response.get('message', 'Unknown error')}",
                    "status": 500
                }
            
            # Extract the mappings from the response
            option_to_segments = segment_response["data"]["option_to_segments"]
            all_articlecodes = segment_response["data"]["all_articlecodes"]
            global_constants = segment_response["data"].get("global_constants", [])
            
            # Convert options to segments
            selected_segments = []
            segment_mappings = {}

            # Normalize helper to make lookups robust to case/spacing variations
            def normalize_key(s: str) -> str:
                # lowercased, single-spaced, trimmed
                lowered = s.lower()
                # normalize various " x " variants by collapsing whitespace
                collapsed = " ".join(lowered.split())
                return collapsed

            # Build a normalized lookup map once
            normalized_option_to_segments = {normalize_key(k): v for k, v in option_to_segments.items()}
            
            for option_string in processed_options:
                if ":" in option_string:
                    box, option_value = option_string.split(":", 1)
                    box = box.strip()
                    option_value = option_value.strip()
                    
                    # Create the key in the same format as segment mapping
                    key = f"{box}: {option_value}"
                    nkey = normalize_key(key)
                    
                    # Find the segment for this option
                    if nkey in normalized_option_to_segments:
                        segment = normalized_option_to_segments[nkey]
                        selected_segments.append(segment)
                        segment_mappings[key] = segment
                    else:
                        return {
                            "data": {},
                            "message": f"Segment not found for option: {key}",
                            "status": 404
                        }
                else:
                    return {
                        "data": {},
                        "message": f"Invalid option format: {option_string}. Expected 'box: option'",
                        "status": 422
                    }
            
            # Construct the articlecode with global constants
            all_segments = selected_segments + global_constants
            
            # Construct the articlecode - simply use all segments from all_segments
            constructed_segments = list(all_segments)
            
            found_articlecode = "-".join(constructed_segments)
            
            # Verify the constructed articlecode exists in all_articlecodes
            # Use segment-based matching instead of exact string matching (order may differ)
            
            # Helper function to normalize articlecode for comparison
            def normalize(code):
                parts = [p.strip().upper() for p in code.split('-') if p.strip()]
                return "-".join(sorted(parts))
            
            # Normalize the constructed string
            constructed_str = "-".join(constructed_segments)
            constructed_normalized = normalize(constructed_str)
            
            matched_articlecode = None
            for articlecode in all_articlecodes:
                # Normalize the articlecode
                articlecode_normalized = normalize(articlecode)
                
                # Compare the normalized strings
                if constructed_normalized == articlecode_normalized:
                    matched_articlecode = articlecode
                    break
            
            if not matched_articlecode:
                constructed_segments_set = set(constructed_segments)
                return {
                    "data": {},
                    "message": f"Constructed articlecode {found_articlecode} (segments: {constructed_segments_set}) not found in available articlecodes",
                    "status": 404
                }
            
            # Use the matched articlecode with correct ordering
            found_articlecode = matched_articlecode
            
            return {
                "data": {
                    "articlenumber": articlenumber,
                    "options": processed_options,
                    "segment_mappings": segment_mappings,
                    "selected_segments": selected_segments,
                    "global_constants": global_constants,
                    "all_segments": all_segments,
                    "constructed_articlecode": found_articlecode
                },
                "message": "Option segments and articlecode retrieved successfully",
                "status": 200
            }
            
        except Exception as e:
            return {
                "data": {},
                "message": f"Failed to get option segments: {str(e)}",
                "status": 500
            }

class ValidateArticlecode(Resource):
    """Validate if a provided articlecode exists for an articlenumber, ignoring segment order."""

    def post(self):
        try:
            payload = request.get_json(silent=True) or {}
            tenant_id = payload.get("tenant_id")
            articlenumber = payload.get("articlenumber")
            articlecode_input = payload.get("articlecode")

            if not tenant_id or not articlenumber or not articlecode_input:
                return {
                    "data": {},
                    "message": "Missing tenant_id, articlenumber or articlecode",
                    "status": 422
                }

            # Fetch products and build available articlecodes
            products = degroot_service.get_article_products(tenant_id, articlenumber)
            flat_rows = flatten_products_tree(products)
            all_articlecodes: List[str] = []
            for path, ac in flat_rows:
                all_articlecodes.append(ac)

            # Normalize by sorting hyphen-delimited segments (case-insensitive)
            def normalize(code: str) -> str:
                parts = [p.strip().upper() for p in str(code).split('-') if str(p).strip()]
                return "-".join(sorted(parts))

            input_norm = normalize(articlecode_input)
            matched_articlecode = None
            for ac in all_articlecodes:
                if normalize(ac) == input_norm:
                    matched_articlecode = ac
                    break

            exists = matched_articlecode is not None
            return {
                "data": {
                    "articlenumber": articlenumber,
                    "requested_articlecode": articlecode_input,
                    "exists": exists,
                    "matched_articlecode": matched_articlecode,
                    "total_available": len(all_articlecodes)
                },
                "message": "Match found" if exists else "No match",
                "status": 200 if exists else 404
            }
        except Exception as e:
            return {
                "data": {},
                "message": f"Failed to validate articlecode: {str(e)}",
                "status": 400
            }

def compute_excludes(option_to_segments: Dict[str, str], all_articlecodes: List[str]) -> Dict[str, List[Dict[str, str]]]:
    """Compute single and pairwise excludes from mapping and articlecodes.

    Returns:
      {
        "singles": [{"option_key": str, "segment": str}],
        "pairs": [
          {"option_key_1": str, "segment_1": str, "option_key_2": str, "segment_2": str}
        ]
      }
    """
    # Build set representation of all articlecodes
    articlecode_sets: List[Set[str]] = []
    for ac in all_articlecodes:
        ac_set = set([p.strip().upper() for p in str(ac).split('-') if str(p).strip()])
        if ac_set:
            articlecode_sets.append(ac_set)

    # Presence index: segment -> set of row indices where it appears
    segment_to_rows: Dict[str, Set[int]] = defaultdict(set)
    for idx, sset in enumerate(articlecode_sets):
        for seg in sset:
            segment_to_rows[seg].add(idx)

    # Reverse mapping: segment -> option key (first one wins if duplicates)
    option_by_segment: Dict[str, str] = {}
    for opt_key, seg in option_to_segments.items():
        seg_up = str(seg).upper()
        if seg_up not in option_by_segment:
            option_by_segment[seg_up] = opt_key

    # Singles: option whose segment never appears
    singles: List[Dict[str, str]] = []
    for opt_key, seg in option_to_segments.items():
        seg_up = str(seg).upper()
        if len(segment_to_rows.get(seg_up, set())) == 0:
            singles.append({"option_key": opt_key, "segment": seg})

    # Pairs: segments that each appear somewhere but never co-occur
    # Collect only actually-present segments to limit pairs
    present_segments: List[str] = sorted([s for s, rows in segment_to_rows.items() if rows])

    pairs: List[Dict[str, str]] = []
    for i in range(len(present_segments)):
        s1 = present_segments[i]
        rows1 = segment_to_rows[s1]
        for j in range(i + 1, len(present_segments)):
            s2 = present_segments[j]
            # If they never co-occur in any row, it's a pairwise exclude
            if rows1.isdisjoint(segment_to_rows[s2]):
                ok1 = option_by_segment.get(s1, s1)
                ok2 = option_by_segment.get(s2, s2)
                pairs.append({
                    "option_key_1": ok1,
                    "segment_1": s1,
                    "option_key_2": ok2,
                    "segment_2": s2,
                })

    return {"singles": singles, "pairs": pairs}


class GetExcludes(Resource):
    """Return computed excludes (singles and pairs) for an articlenumber."""

    def post(self):
        try:
            data = request.get_json(silent=True) or {}
            tenant_id = data.get("tenant_id")
            articlenumber = data.get("articlenumber")
            if not tenant_id or not articlenumber:
                return {
                    "data": {},
                    "message": "Missing tenant_id or articlenumber",
                    "status": 422
                }

            # Reuse segment-mapping generator to get mapping and articlecodes
            segment_mapping_instance = GetSegmentMapping()
            mapping_resp = segment_mapping_instance.get(articlenumber)
            if mapping_resp.get("status") != 200:
                return {
                    "data": {},
                    "message": f"Failed to get segment mapping: {mapping_resp.get('message', 'Unknown error')}",
                    "status": 500
                }

            option_to_segments: Dict[str, str] = mapping_resp["data"].get("option_to_segments", {})
            all_articlecodes: List[str] = mapping_resp["data"].get("all_articlecodes", [])

            excludes = compute_excludes(option_to_segments, all_articlecodes)

            return {
                "data": {
                    "articlenumber": articlenumber,
                    "counts": {
                        "singles": len(excludes.get("singles", [])),
                        "pairs": len(excludes.get("pairs", [])),
                        "segments_total": len(option_to_segments),
                        "articlecodes_total": len(all_articlecodes),
                    },
                    "singles": excludes.get("singles", []),
                    "pairs": excludes.get("pairs", []),
                },
                "message": "Excludes computed",
                "status": 200
            }
        except Exception as e:
            return {
                "data": {},
                "message": f"Failed to compute excludes: {str(e)}",
                "status": 400
            }

def compute_boops_excludes(option_id_to_segment: Dict[str, str], all_articlecodes: List[str], option_id_to_box: Dict[str, str]) -> Dict[str, List[List[str]]]:
    """Compute excludes per option id based on SupplierBoops segments and articlecodes.

    Returns mapping: optionId -> list of excludes (each exclude is a list of optionIds)
    Single exclude for A vs B => [B_id]
    Multi exclude for A with (B,C) excludes D => [B_id, C_id, D_id]
    """
    # Normalize articlecodes to sets
    rows: List[Set[str]] = []
    for ac in all_articlecodes:
        s = set([p.strip().upper() for p in str(ac).split('-') if str(p).strip()])
        if s:
            rows.append(s)

    # Index token -> set of row indices where it appears
    seg_to_rows: Dict[str, Set[int]] = defaultdict(set)
    for idx, s in enumerate(rows):
        for seg in s:
            seg_to_rows[seg].add(idx)

    # Maps for convenience
    option_ids: List[str] = list(option_id_to_segment.keys())
    # Each option may have multiple tokens (e.g., "135-GES-PZLA"). Split and normalize per-token.
    tokens_by_opt: Dict[str, List[str]] = {}
    for oid, seg in option_id_to_segment.items():
        if not seg:
            continue
        tokens = [t.strip().upper() for t in str(seg).split('-') if str(t).strip()]
        if tokens:
            tokens_by_opt[oid] = tokens

    excludes: Dict[str, List[List[str]]] = {oid: [] for oid in option_ids}

    # Group options by box
    box_to_options: Dict[str, List[str]] = defaultdict(list)
    for oid, box_id in option_id_to_box.items():
        if box_id:
            box_to_options[box_id].append(oid)

    # Compute effective row sets per option as intersection across its tokens
    rows_by_opt: Dict[str, Set[int]] = {}
    for oid, tokens in tokens_by_opt.items():
        rowsets: List[Set[int]] = []
        for tok in tokens:
            rowsets.append(seg_to_rows.get(tok, set()))
        if not rowsets:
            continue
        # Intersection across tokens; if any token never appears, result is empty
        intersected = set(rowsets[0]) if rowsets else set()
        for rs in rowsets[1:]:
            intersected &= rs
        rows_by_opt[oid] = intersected

    # For each anchor option A (pairwise-only excludes, with safeguards)
    for a_id in option_ids:
        rowsA = rows_by_opt.get(a_id, set())
        if not rowsA:
            continue

        boxA = option_id_to_box.get(a_id)

        # Consider each other box separately to avoid wiping a full box
        for boxB, boxB_options in box_to_options.items():
            if not boxB_options:
                continue
            if boxB == boxA:
                continue  # never exclude within same box

            # Check if this box ever co-occurs with A (any option in boxB overlaps rowsA)
            boxB_has_cooccur = False
            for b_id in boxB_options:
                rowsB = rows_by_opt.get(b_id, set())
                if rowsB and not rowsA.isdisjoint(rowsB):
                    boxB_has_cooccur = True
                    break
            if not boxB_has_cooccur:
                # Treat boxes as alternative groups; skip excludes for this whole box
                continue

            # Now compute pairwise excludes for members of this box only
            candidates_to_exclude: List[str] = []
            for b_id in boxB_options:
                if b_id == a_id:
                    continue
                rowsB = rows_by_opt.get(b_id, set())
                if not rowsB:
                    continue
                if rowsA.isdisjoint(rowsB):
                    candidates_to_exclude.append(b_id)

            # Per-box safeguard: if we'd exclude >80% of a co-occurring box, skip it
            if boxB_options and (len(candidates_to_exclude) / max(1, len(boxB_options)) > 0.80):
                continue

            for b_id in candidates_to_exclude:
                excludes[a_id].append([b_id])

    return excludes


class GetBoopsExcludes(Resource):
    """Compute excludes using SupplierBoops options (option IDs and source_slug segments)."""

    def post(self):
        try:
            data = request.get_json(silent=True) or {}
            tenant_id = data.get("tenant_id")
            articlenumber = data.get("articlenumber")
            if not tenant_id or not articlenumber:
                return {
                    "data": {},
                    "message": "Missing tenant_id or articlenumber",
                    "status": 422
                }

            # Find SupplierCategory then SupplierBoops
            suppliers_category = SupplierCategory.objects(tenant_id=tenant_id, sku=str(articlenumber)).first()
            if not suppliers_category:
                return {"data": {}, "message": "SupplierCategory not found", "status": 404}

            boops_doc = SupplierBoops.objects(tenant_id=tenant_id, supplier_category=suppliers_category.id).first()
            if not boops_doc or not boops_doc.boops:
                return {"data": {}, "message": "SupplierBoops not found", "status": 404}

            # Extract mappings
            option_id_to_segment: Dict[str, str] = {}
            option_id_to_box: Dict[str, str] = {}
            for box in (boops_doc.boops or []):
                box_id = str(box.get("id")) if box.get("id") is not None else None
                for op in (box.get("ops") or []):
                    oid = str(op.get("id"))
                    seg = op.get("source_slug")
                    if oid and seg:
                        option_id_to_segment[oid] = seg
                    if oid and box_id:
                        option_id_to_box[oid] = box_id

            # Get articlecodes and option_to_segments from segment mapping
            segment_mapping_instance = GetSegmentMapping()
            mapping_resp = segment_mapping_instance.get(articlenumber)
            if mapping_resp.get("status") != 200:
                return {"data": {}, "message": "Failed to get segment mapping", "status": 500}
            all_articlecodes: List[str] = mapping_resp["data"].get("all_articlecodes", [])
            option_to_segments_map: Dict[str, str] = mapping_resp["data"].get("option_to_segments", {})

            # Fallback: resolve segments from mapping using "box: option" names when source_slug tokens don't align
            def norm_key(s: str) -> str:
                return " ".join(str(s).lower().split())

            normalized_map = {norm_key(k): v for k, v in option_to_segments_map.items()}

            # Build improved option_id_to_segment using mapping when available
            improved_option_id_to_segment: Dict[str, str] = dict(option_id_to_segment)
            for box in (boops_doc.boops or []):
                box_name = str(box.get("name", ""))
                for op in (box.get("ops") or []):
                    oid = str(op.get("id"))
                    opt_name = str(op.get("name", ""))
                    if not oid:
                        continue
                    key = f"{box_name}: {opt_name}"
                    nkey = norm_key(key)
                    mapped_seg = normalized_map.get(nkey)
                    if mapped_seg:
                        improved_option_id_to_segment[oid] = mapped_seg

            excludes_map = compute_boops_excludes(improved_option_id_to_segment, all_articlecodes, option_id_to_box)

            return {
                "data": excludes_map,
                "message": "Boops excludes computed",
                "status": 200
            }
        except Exception as e:
            return {
                "data": {},
                "message": f"Failed to compute boops excludes: {str(e)}",
                "status": 400
            }

class ApplyExcludesByBoops(Resource):
    """Compute excludes using SupplierBoops and persist them into ops[*].excludes (debug helper)."""

    def post(self):
        try:
            data = request.get_json(silent=True) or {}
            tenant_id = data.get("tenant_id")
            articlenumber = data.get("articlenumber")
            if not tenant_id or not articlenumber:
                return {
                    "data": {},
                    "message": "Missing tenant_id or articlenumber",
                    "status": 422
                }

            # Locate SupplierBoops for this (tenant, articlenumber)
            suppliers_category = SupplierCategory.objects(tenant_id=tenant_id, system_key=str(articlenumber)).first()
            if not suppliers_category:
                return {"data": {}, "message": "SupplierCategory not found", "status": 404}

            boops_doc = SupplierBoops.objects(tenant_id=tenant_id, supplier_category=suppliers_category.id).first()
            if not boops_doc or not boops_doc.boops:
                return {"data": {}, "message": "SupplierBoops not found", "status": 404}

            # Build mappings from current boops
            option_id_to_segment: Dict[str, str] = {}
            option_id_to_box: Dict[str, str] = {}
            for box in (boops_doc.boops or []):
                box_id = str(box.get("id")) if box.get("id") is not None else None
                for op in (box.get("ops") or []):
                    oid = str(op.get("id"))
                    seg = op.get("source_slug")
                    if oid and seg:
                        option_id_to_segment[oid] = seg
                    if oid and box_id:
                        option_id_to_box[oid] = box_id

            # Fetch mapping (articlecodes + option_to_segments) and strengthen segment resolution
            segment_mapping_instance = GetSegmentMapping()
            mapping_resp = segment_mapping_instance.get(articlenumber)
            if mapping_resp.get("status") != 200:
                return {"data": {}, "message": "Failed to get segment mapping", "status": 500}
            all_articlecodes: List[str] = mapping_resp["data"].get("all_articlecodes", [])
            option_to_segments_map: Dict[str, str] = mapping_resp["data"].get("option_to_segments", {})

            def norm_key(s: str) -> str:
                return " ".join(str(s).lower().split())
            normalized_map = {norm_key(k): v for k, v in option_to_segments_map.items()}

            improved_option_id_to_segment: Dict[str, str] = dict(option_id_to_segment)
            for box in (boops_doc.boops or []):
                box_name = str(box.get("name", ""))
                for op in (box.get("ops") or []):
                    oid = str(op.get("id"))
                    opt_name = str(op.get("name", ""))
                    if not oid:
                        continue
                    key = f"{box_name}: {opt_name}"
                    nkey = norm_key(key)
                    mapped_seg = normalized_map.get(nkey)
                    if mapped_seg:
                        improved_option_id_to_segment[oid] = mapped_seg

            # Compute excludes map
            excludes_map = compute_boops_excludes(improved_option_id_to_segment, all_articlecodes, option_id_to_box)

            # Apply updates to ops[*].excludes
            updated = 0
            for bidx, box in enumerate(boops_doc.boops or []):
                ops = box.get("ops") or []
                for op in ops:
                    oid = str(op.get("id"))
                    if not oid:
                        continue
                    new_excludes = excludes_map.get(oid, [])
                    op["excludes"] = new_excludes
                    updated += 1

            boops_doc.save()

            return {
                "data": {
                    "updated_options": updated,
                    "excludes_keys": len(excludes_map)
                },
                "message": "Excludes applied to SupplierBoops",
                "status": 200
            }
        except Exception as e:
            return {
                "data": {},
                "message": f"Failed to apply excludes: {str(e)}",
                "status": 400
            }

class ResetExcludesByBoops(Resource):
    """Reset all ops[*].excludes to [] for a given (tenant_id, articlenumber)."""

    def post(self):
        try:
            data = request.get_json(silent=True) or {}
            tenant_id = data.get("tenant_id")
            articlenumber = data.get("articlenumber")
            if not tenant_id or not articlenumber:
                return {
                    "data": {},
                    "message": "Missing tenant_id or articlenumber",
                    "status": 422
                }

            # Locate SupplierBoops for this (tenant, articlenumber) using system_key per latest change
            suppliers_category = SupplierCategory.objects(tenant_id=tenant_id, system_key=str(articlenumber)).first()
            if not suppliers_category:
                return {"data": {}, "message": "SupplierCategory not found", "status": 404}

            boops_doc = SupplierBoops.objects(tenant_id=tenant_id, supplier_category=suppliers_category.id).first()
            if not boops_doc or not boops_doc.boops:
                return {"data": {}, "message": "SupplierBoops not found", "status": 404}

            cleared = 0
            for box in (boops_doc.boops or []):
                ops = box.get("ops") or []
                for op in ops:
                    op["excludes"] = []
                    cleared += 1

            boops_doc.save()

            return {
                "data": {"cleared_options": cleared},
                "message": "Excludes reset to empty arrays",
                "status": 200
            }
        except Exception as e:
            return {
                "data": {},
                "message": f"Failed to reset excludes: {str(e)}",
                "status": 400
            }

class GetPrice(Resource):
    """Get price for specific product configuration using segment mapping"""
    
    def post(self):
        data = request.get_json()
        tenant_id = data.get("tenant_id")
        articlenumber = data.get("article_number") or None
        options = data.get("options", [])  # Can be list of "box: option" strings or list of objects
        quantity = data.get("quantity")  # Optional quantity parameter
        
        if not tenant_id or not articlenumber:
            return {
                "data": {},
                "message": "Missing tenant_id or articlenumber",
                "status": 422
            }
        # Handle different option formats from gateway
        processed_options = []
        extracted_quantity = quantity
        
        for option in options:
            if isinstance(option, str):
                # Original format: "box: option"
                processed_options.append(option)
            elif isinstance(option, dict):
                # Gateway format: {"box": "option"} or {"Quantity": 5}
                for key, value in option.items():
                    if key.lower() == 'quantity':
                        # Extract quantity from options if not provided directly
                        extracted_quantity = extracted_quantity or value
                    else:
                        # Convert {"box": "option"} to "box: option"
                        processed_options.append(f"{key}: {value}")
        
        # Use extracted quantity if not provided directly
        if extracted_quantity is not None:
            quantity = extracted_quantity
        
        try:
            # Step 1: Call the GetSegmentMapping endpoint internally to get the exact same mappings
            # This ensures we get exactly the same data as the working segment-mapping endpoint
            from services.sync import GetSegmentMapping
            
            # Create a mock request object for the GetSegmentMapping endpoint
            class MockRequest:
                def __init__(self, tenant_id, articlenumber):
                    self.tenant_id = tenant_id
                    self.articlenumber = articlenumber
                
                def get_json(self, silent=True):
                    return {"tenant_id": self.tenant_id}
            
            # Call the GetSegmentMapping logic directly
            segment_mapping_instance = GetSegmentMapping()
            segment_response = segment_mapping_instance.get(articlenumber)
            
            if segment_response.get("status") != 200:
                return {
                    "data": {},
                    "message": f"Failed to get segment mapping: {segment_response.get('message', 'Unknown error')}",
                    "status": 500
                }
            
            # Extract the mappings from the response
            option_to_segments = segment_response["data"]["option_to_segments"]
            all_articlecodes = segment_response["data"]["all_articlecodes"]
            global_constants = segment_response["data"].get("global_constants", [])
            
            # Step 2: Convert options to segments
            print("option_to_segments", option_to_segments)
            selected_segments = []
            # Normalize helper to make lookups robust to case/spacing variations
            def normalize_key(s: str) -> str:
                lowered = s.lower()
                collapsed = " ".join(lowered.split())
                return collapsed

            # Build a normalized lookup map once
            normalized_option_to_segments = {normalize_key(k): v for k, v in option_to_segments.items()}
            for option_string in processed_options:
                if ":" in option_string:
                    box, option_value = option_string.split(":", 1)
                    box = box.strip()
                    option_value = option_value.strip()
                    
                    # Create the key in the same format as segment mapping
                    key = f"{box}: {option_value}"
                    nkey = normalize_key(key)
                    
                    # Find the segment for this option
                    if nkey in normalized_option_to_segments:
                        segment = normalized_option_to_segments[nkey]
                        selected_segments.append(segment)
                    else:
                        resp = {
                            "data": {},
                            "message": f"Segment not found for option: {key}",
                            "status": 404
                        }
                        print(resp)
                        return resp
                else:
                    resp = {
                        "data": {},
                        "message": f"Invalid option format: {option_string}. Expected 'box: option'",
                        "status": 422
                    }
                    print(resp)
                    return resp
            
            # Step 3: Construct the articlecode with global constants
            # Combine selected segments with global constants
            all_segments = selected_segments + global_constants
            
            # Construct the articlecode - simply use all segments from all_segments
            constructed_segments = list(all_segments)
            
            found_articlecode = "-".join(constructed_segments)
            print(f"DEBUG: Constructed articlecode: {found_articlecode}")
            
            # Verify the constructed articlecode exists in all_articlecodes
            # Use segment-based matching instead of exact string matching (order may differ)
            constructed_segments_set = set(constructed_segments)
            matched_articlecode = None
            
            # Helper function to normalize articlecode for comparison
            def normalize(code):
                parts = [p.strip().upper() for p in code.split('-') if p.strip()]
                return "-".join(sorted(parts))
            
            # Normalize the constructed string
            constructed_str = "-".join(constructed_segments)
            constructed_normalized = normalize(constructed_str)
            print(f"DEBUG: Normalized constructed string: {constructed_normalized}")
            
            match_count = 0
            for articlecode in all_articlecodes:
                # Normalize the articlecode
                articlecode_normalized = normalize(articlecode)

                # Compare the normalized strings
                if constructed_normalized == articlecode_normalized:
                    matched_articlecode = articlecode
                    match_count += 1
                    print(f"DEBUG: Found matching articlecode #{match_count}: {matched_articlecode}")
                    if match_count == 1:  # Break on first match
                        break
          
            
            print({
                    "data": {},
                    "message": f"Constructed articlecode {found_articlecode} (segments: {constructed_segments_set}) not found in available articlecodes (total: {len(all_articlecodes)})",
                    "status": 404
                })
            if not matched_articlecode:
                return {
                    "data": {},
                    "message": f"Constructed articlecode {found_articlecode} (segments: {constructed_segments_set}) not found in available articlecodes (total: {len(all_articlecodes)})",
                    "status": 404
                }
            
            # Use the matched articlecode with correct ordering
            found_articlecode = matched_articlecode
            print(f"DEBUG: Matched articlecode with correct ordering: {found_articlecode}")
            
            # Step 4: Call the Grootsgedrukt price API
            price_data = degroot_service.get_price(tenant_id, found_articlecode)
            
            # Process the price response - return all pricing data API
            if not price_data or not isinstance(price_data, list) or len(price_data) == 0:
                return {
                    "data": {
                        "constructed_articlecode": found_articlecode,
                        "selected_options": processed_options,
                        "selected_segments": selected_segments,
                        "all_segments": all_segments,
                        "global_constants": global_constants
                    },
                    "message": f"Articlecode constructed successfully: {found_articlecode}, but no pricing data available from Grootsgedrukt API",
                    "status": 404
                }
            
            pricing_info = price_data[0]  # Get first (and typically only) item
            
            # Find matching quantity tier if quantity is provided
            matching_quantity_tier = None
            if quantity is not None:
                staffel = pricing_info.get("staffel", [])
                for tier in staffel:
                    if tier.get("quantity") == str(quantity):
                        matching_quantity_tier = tier
                        break
            
            # Build response data
            response_data = {
                "constructed_articlecode": found_articlecode,
                "selected_options": options,
                "selected_segments": selected_segments,
                "all_segments": all_segments,
                "global_constants": global_constants,
                "title": pricing_info.get("title"),
                "description": pricing_info.get("description"),
                "headers": pricing_info.get("headers", []),
                "staffel": pricing_info.get("staffel", []),
                "raw": pricing_info
            }
            
            # Add matching quantity tier if found
            if matching_quantity_tier:
                response_data["quantity_pricing"] = matching_quantity_tier
            
            # Return all pricing Grootsgedrukt API
            return {
                "data": response_data,
                "message": f"Price retrieved successfully for articlecode: {found_articlecode}",
                "status": 200
            }
            
        except Exception as e:
            return {
                "data": {},
                "message": f"Failed to get price: {str(e)}",
                "status": 400
            }

class Sync(Resource):
    """Sync article data from de-groot API format"""
    
    def post(self):
        try:
            payload = request.get_json()
            tenant_id = payload.get("tenant_id")
            articlenumber = payload.get("articlenumber") or (payload.get("skus")[0] if payload.get("skus") else None)
            
            if not tenant_id or not articlenumber:
                return {
                    "data": [],
                    "message": "Missing tenant_id or articlenumber",
                    "status": 422
                }
            
            # Get API credentials
            credentials = get_header(tenant_id)
            base_url = credentials["url"]
            token = credentials["header"]["authorization"].replace("Bearer ", "")
            
            # Get categories from the correct endpoint
            categories_response = requests.get(
                f"{base_url.rstrip('/')}/articles",
                headers={"Authorization": f"Bearer {token}"}
            )
            categories_response.raise_for_status()
            categories = categories_response.json()
            
            # Find the specific category for this articlenumber
            category_info = None
            for cat in categories:
                if cat.get("articlenumber") == articlenumber:
                    category_info = cat
                    break
            
            if not category_info:
                return {
                    "data": [],
                    "message": f"Category with articlenumber {articlenumber} not found",
                    "status": 404
                }
            
            # Get options/boxes for this article from the correct endpoint
            options_response = requests.get(
                f"{base_url.rstrip('/')}/articles?article&articlenumber={articlenumber}",
                headers={"Authorization": f"Bearer {token}"}
            )
            options_response.raise_for_status()
            options_data = options_response.json()
            
            # Transform to Groot format
            groot_format_data = self._transform_to_groot_format(articlenumber, category_info, options_data)
            
            # Step 2: Import the synced data (similar to Groot sync)
            try:
                if groot_format_data:
                    # Add tenant info to the data for import
                    import_payload = groot_format_data.copy()
                    import_payload.update({
                        'tenant_id': tenant_id,
                        'articlenumber': articlenumber,
                        'tenant_name': payload.get('tenant_name', 'de-groot'),
                        'vendor': payload.get('vendor', 'grootsgedrukt')
                    })
                    
                    # Call the import endpoint internally
                    from external.external_degroot_supplier import ExternalDeGrootSupplier
                    import_resource = ExternalDeGrootSupplier()
                    
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
                        "data": [groot_format_data],
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
                    "data": [groot_format_data],
                    "message": f"Sync successful but import failed: {str(import_error)}",
                    "status": 206  # Partial success
                }
            
        except Exception as e:
            return {
                "data": [],
                "message": f"Failed to sync article data: {str(e)}",
                "status": 400
            }
    
    def _transform_to_groot_format(self, articlenumber, category_info, options_data):
        """Transform Grootsgedrukt data to Groot sync format"""
        from datetime import datetime
        import uuid
        
        # Extract properties from options_data (which is a dict with group names as keys)
        properties = []
        
        # Convert to Groot Property format
        for group_name, option_values in options_data.items():
            property_slug = self._to_snake_case(group_name)
            property_title = group_name
            
            # Convert options to Groot Option format
            groot_options = []
            for option_value in option_values:
                option_slug = self._to_snake_case(option_value)
                option_name = option_value
                
                groot_option = {
                    "slug": option_slug,
                    "name": option_name,
                    "nullable": False,
                    "width": None,
                    "height": None,
                    "parent": property_slug,
                    "excludes": []  # Empty for now as requested
                }
                groot_options.append(groot_option)
            
            property_obj = {
                "slug": property_slug,
                "title": property_title,
                "locked": False,
                "options": groot_options
            }
            properties.append(property_obj)
        
        # Create Groot CategoryResponse format
        now = datetime.utcnow().isoformat() + "Z"
        
        groot_response = {
            "sku": str(uuid.uuid4()),  # Generate unique SKU
            "active": True,
            "titleSingle": category_info.get("articlename", "Unknown Category"),
            "titlePlural": category_info.get("articlename", "Unknown Category"),
            "createdAt": now,
            "updatedAt": now,
            "introductionDate": now,
            "properties": properties
        }
        
        return groot_response
    
    def _to_snake_case(self, text):
        """Convert text to snake_case format"""
        import re
        if not text:
            return ""
        
        # Replace special characters with spaces
        text = re.sub(r'[/\-\.]', ' ', text)
        # Convert to lowercase and replace spaces with underscores
        text = re.sub(r'\s+', '_', text.lower().strip())
        return text
