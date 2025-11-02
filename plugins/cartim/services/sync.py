import os
from flask import request, jsonify
from flask_restful import Resource
import json
import uuid
from datetime import datetime, timezone
from slugify import slugify
import requests
from database.db import db
from models.supplierCategory import SupplierCategory
from models.supplierBox import SupplierBox
from models.supplierOption import SupplierOption
from models.supplierBoops import SupplierBoops
from helper.helper import generate_display_names
import time
import re
import locale
from datetime import datetime, date

def _parse_exclude_token(token: str):
    """Parse tokens like 'nBackPMS:1' into (key, typed_value)."""
    if not isinstance(token, str) or ':' not in token:
        return token, None
    key, raw = token.split(':', 1)
    val = raw
    if isinstance(raw, str):
        low = raw.strip().lower()
        if low == 'true':
            val = True
        elif low == 'false':
            val = False
        elif raw.isdigit():
            val = int(raw)
    return key, val


def build_excludes_from_rules(rules: dict) -> dict:
    """
    Build a normalized excludes map from the provided rules JSON.

    Returns a dict: { category_slug: { box_key: { option_value: [ {key, value}, ... ] } } }
    """
    excludes_map: dict = {}
    for category in (rules or {}).get('categories', []):
        slug = category.get('slug')
        if not slug:
            continue
        cat_map = excludes_map.setdefault(slug, {})
        for box in category.get('boxes', []):
            box_key = box.get('key')
            if not box_key:
                continue
            opt_map = cat_map.setdefault(box_key, {})
            for opt in box.get('options', []):
                value = opt.get('value')
                tokens = opt.get('excludes', []) or []
                parsed_tokens = []
                for tk in tokens:
                    k, v = _parse_exclude_token(tk)
                    parsed_tokens.append({ 'key': k, 'value': v })
                opt_map[value] = parsed_tokens
    return excludes_map


def get_header(tenant_id: str) -> dict:
    """Placeholder for tenant-scoped headers (env fallback)."""
    return {
        "header": {
            "API-Secret": os.environ.get('API_SECRET'),
            "User-ID": os.environ.get('USER_ID'),
            "accept": "application/json"
        },
        "url": os.environ.get('URL'),
    }


def _flatten_boxes(boxes: list) -> list:
    """Flatten group-type boxes by lifting their children to top-level boxes."""
    flattened = []
    for box in boxes or []:
        if box.get('type') == 'group':
            for child in box.get('children', []):
                flattened.append({
                    'key': child.get('key'),
                    'label': child.get('label'),
                    'type': child.get('type'),
                    'description': child.get('description'),
                    'options': child.get('options'),
                    'default': child.get('default'),
                    'item_schema': child.get('item_schema'),
                })
        else:
            flattened.append(box)
    return flattened


def _get_skip_boxes() -> set:
    """Hardcoded boxes to skip when building properties (align with DWD)."""
    return {
        'quantity',
        'delivery_days',
        'delivery_type',
        'shipping.shipments',
        'shipping.priority', 'shipping-priority',
        'shipping.tailLift', 'shipping-taillift',
        'reference',
        'file',
        'nFrontPMS',
        'versions',
        'shipping-countryCode',
        'shipping-method',
        'shipping-packageType',
        'shipping-postalCode',
        'fiveSampleCopies',
        'digitalProof',
        'shipping',
        'shipping-shipments',
        'shipping.shipments[].quantity',
        'shipping-shipments-quantity',
        'shipping-shipments-addressant',
        'shipping.shipments[].address',
        'shipping.shipments[].postalCode',
        'shipping.shipments[].city',
        'versions[].quantity',
        'shipping.shipments[].company',
        'shipping-shipments-packageQuantity',
        'shipping-shipments-packageType'

    }


def normalize_categories_payload(payload: dict) -> dict:
    """Normalize incoming categories payload into a predictable shape."""
    categories_out = []

    raw_categories = payload.get('categories', []) or []

    for category in raw_categories:
        # Case 1: Already in normalized/old shape with explicit slug/boxes
        if isinstance(category, dict) and (
            'slug' in category or 'boxes' in category or 'display_name' in category or 'name' in category
        ):
            categories_out.append({
                'slug': category.get('slug') or slugify(category.get('name') or category.get('display_name') or ''),
                'display_name': category.get('display_name') or category.get('name') or category.get('slug'),
                'boxes': _flatten_boxes(category.get('boxes', [])),
            })
            continue

        # Case 2: New shape: { "poster": { "boxKey": [ {value,label,...}, ... ], ... } }
        if isinstance(category, dict) and len(category.keys()) == 1:                                                                                            
            cat_key = next(iter(category.keys()))
            cat_body = category.get(cat_key) or {}

            boxes = []
            if isinstance(cat_body, dict):
                for box_key, options in cat_body.items():
                    # options expected to be a list of dicts with value/label/default/api_available
                    normalized_options = []
                    if isinstance(options, list):
                        for opt in options:
                            if isinstance(opt, dict):
                                # ensure non-empty label; fallback later in mapping uses slug
                                normalized_options.append({
                                    'value': opt.get('value'),
                                    'label': opt.get('label'),
                                    'default': opt.get('default'),
                                    'api_available': opt.get('api_available'),
                                })
                            else:
                                # Primitive option fallback
                                normalized_options.append({
                                    'value': opt,
                                    'label': str(opt),
                                    'default': False,
                                    'api_available': True,
                                })
                    # Build a simple select-type box
                    boxes.append({
                        'key': box_key,
                        'label': box_key,
                        'type': 'select',
                        'description': '',
                        'options': normalized_options,
                        'default': None,
                        'item_schema': None,
                    })

            categories_out.append({
                'slug': slugify(str(cat_key)),
                'display_name': str(cat_key),
                'boxes': _flatten_boxes(boxes),
            })
            continue

        # Fallback: Unrecognized item; skip safely
        continue

    return {'categories': categories_out}


def _sanitize_for_json(value):
    if value is None:
        return None
    # Flask Response
    if hasattr(value, 'get_json'):
        try:
            return value.get_json()
        except Exception:
            pass
    if hasattr(value, 'data'):
        try:
            return value.data.decode('utf-8') if isinstance(value.data, (bytes, bytearray)) else value.data
        except Exception:
            return str(value.data)
    if isinstance(value, (str, int, float, bool)):
        return value
    if isinstance(value, list):
        return [_sanitize_for_json(v) for v in value]
    if isinstance(value, dict):
        return {str(k): _sanitize_for_json(v) for k, v in value.items()}
    try:
        return str(value)
    except Exception:
        return repr(value)


def _option_slug_from_value(box_key: str, value) -> str:
    # Build a stable slug for an option value similar to examples
    if value is None:
        return slugify(f"{box_key}_none")
    if isinstance(value, (int, float)):
        if box_key == 'quantity':
            return f"{int(value)}_qty"
        return slugify(str(value))
    return slugify(str(value))


def _map_boxes_to_properties(cat_slug: str, boxes: list, excludes_map: dict) -> list:
    properties = []
    cat_excludes = (excludes_map or {}).get(cat_slug, {})
    skip_keys = _get_skip_boxes()

    # Build an index of available option slugs per (normalized) box key
    available_option_slugs_by_box: dict[str, set] = {}
    for b in boxes:
        raw_key_index = (b.get('key') or '')
        if raw_key_index in skip_keys or slugify(raw_key_index) in skip_keys:
            continue
        normalized_key_index = slugify(raw_key_index)
        option_slugs: set = set()
        for o in (b.get('options') or []):
            # derive the slug the same way mapping does
            value = o.get('value') if 'value' in o else (o.get('name') if 'name' in o else None)
            option_slugs.add(_option_slug_from_value(raw_key_index, value))
        available_option_slugs_by_box[normalized_key_index] = option_slugs

    for box in boxes:
        # skip unwanted boxes (check raw and slugified key)
        raw_key = (box.get('key') or '')
        slug_key = slugify(raw_key)
        if raw_key in skip_keys or slug_key in skip_keys:
            continue
        prop_slug = slugify(box.get('key') or '')
        title = box.get('label') or box.get('key')
        prop = {
            'slug': prop_slug,
            'title': title,
            'locked': False,
            'options': []
        }

        # Array/group boxes have item_schema only; skip option mapping
        if box.get('type') in {'array'} and not box.get('options'):
            properties.append(prop)
            continue

        for opt in box.get('options') or []:
            value = opt.get('value')
            raw_label = opt.get('label')
            
            # Skip options with empty label or value
            if (raw_label is None or str(raw_label).strip() == '') or (value is None or str(value).strip() == ''):
                continue
                
            opt_slug = _option_slug_from_value(box.get('key'), value)
            # derive name: prefer non-empty label, else fallback to slug
            name_value = raw_label if (raw_label is not None and str(raw_label).strip() != '') else opt_slug

            # Skip options without a valid slug or name
            if (opt_slug is None or str(opt_slug).strip() == '') or (name_value is None or str(name_value).strip() == ''):
                continue

            mapped = {
                'slug': opt_slug,
                'name': name_value,
                'nullable': False,
                'width': None,
                'height': None,
                'parent': prop_slug,
                'excludes': []
            }

            # Build excludes as list of arrays of slugs
            by_box = cat_excludes.get(box.get('key'), {})
            tokens = by_box.get(value) or []
            ex_list = []
            for t in tokens:
                other_key = t.get('key')
                other_val = t.get('value')
                if other_key is None and other_val is None:
                    continue
                # Drop excludes that reference skipped boxes (raw or slug)
                other_slug = slugify(str(other_key)) if other_key is not None else None
                if other_key in skip_keys or other_slug in skip_keys:
                    continue
                # Only include excludes that reference an existing option
                target_box_key = slugify(str(other_key))
                candidate_opt_slug = _option_slug_from_value(other_key, other_val)
                available = available_option_slugs_by_box.get(target_box_key, set())
                if candidate_opt_slug in available:
                    ex_list.append([candidate_opt_slug])
                # If no value provided (None), we cannot point to a concrete option, skip
            mapped['excludes'] = ex_list
            prop['options'].append(mapped)

        # Skip boxes with no valid options
        if len(prop['options']) == 0:
            continue

        properties.append(prop)

    return properties


class Sync(Resource):
    def post(self):
        """
        Normalize input to DWD-compatible category shape and attach excludes.
        - Accepts: { categories: [...] } or { skus: [...] } with tenant context
        - Returns: { data: [ { sku, active, titleSingle, titlePlural, createdAt, updatedAt, introductionDate, properties: [...] } ], status, message }
        - Does NOT perform importing/persistence.
        """
        payload = request.get_json(force=True)
        if not isinstance(payload, dict):
            return jsonify({
                "data": [],
                "message": "Invalid payload",
                "status": 422
            }), 422

        tenant_id = payload.get('tenant_id')
        tenant_name = payload.get('tenant_name')
        vendor = payload.get('vendor')

        # Support DWD-style input with skus list by loading categories from file
        categories_payload = None
        if 'categories' in payload:
            categories_payload = payload
        elif 'skus' in payload and isinstance(payload.get('skus'), list):
            try:
                path = os.environ.get('CARTIMPRINT_CATEGORIES_PATH', '/var/www/categories-boxes-options.json')
                with open(path, 'r') as f:
                    raw = json.load(f)
                categories_payload = raw
            except Exception as exc:
                return jsonify({
                    "data": [],
                    "message": f"Failed to load categories file: {str(exc)}",
                    "status": 500
                }), 500
        else:
            return jsonify({
                "data": [],
                "message": "Payload must include 'categories' or 'skus'",
                "status": 422
            }), 422

        normalized = normalize_categories_payload(categories_payload)

        # Excludes disabled for performance during initial import
        excludes = {}

        # Build a DWD-like single category object as sync data
        sync_items = []
        cats = normalized.get('categories', [])
        # Filter by requested skus if provided
        requested_skus = payload.get('skus') if isinstance(payload.get('skus'), list) else None
        if requested_skus:
            cats = [c for c in cats if c.get('slug') in requested_skus]
        if len(cats) > 0:
            cat = cats[0]  # return single normalized entry
            now_iso = datetime.now(timezone.utc).isoformat().replace('+00:00', 'Z')
            props = _map_boxes_to_properties(cat.get('slug'), cat.get('boxes', []), excludes)
            sync_items.append({
                'sku': cat.get('slug') or cat.get('display_name'),
                'active': True,
                'titleSingle': (cat.get('display_name') or cat.get('slug')),
                'titlePlural': (cat.get('display_name') or cat.get('slug')),
                'createdAt': now_iso,
                'updatedAt': now_iso,
                'introductionDate': now_iso,
                'properties': props
            })
        return jsonify({
            "data": sync_items,
            "message": "Data synced successfully" if sync_items else "No data to sync",
            "status": 200 if sync_items else 400
        })


class Import(Resource):
    def post(self):
        """
        Import endpoint: accepts DWD-style sync output and persists to database.
        Creates/updates SupplierCategory, SupplierBox, SupplierOption, and SupplierBoops.
        """
        payload = request.get_json(force=True)
        if not isinstance(payload, dict):
            return {
                "data": [],
                "message": "Invalid payload",
                "status": 422
            }, 422

        if 'data' not in payload or not isinstance(payload['data'], list) or len(payload['data']) == 0:
            return {
                "data": [],
                "message": "Missing or empty 'data' array",
                "status": 422
            }, 422

        tenant_id = payload.get('tenant_id')
        tenant_name = payload.get('tenant_name')
        vendor = payload.get('vendor')
        
        if not tenant_id or not tenant_name:
            return {
                "data": [],
                "message": "Missing tenant_id or tenant_name",
                "status": 422
            }, 422

        try:
            # Process each item in data array
            results = []
            for item in payload['data']:
                sku = item.get('sku')
                title_single = item.get('titleSingle') or sku
                title_plural = item.get('titlePlural') or title_single
                props = item.get('properties') or []

                # Create/update SupplierCategory
                supplier_category = SupplierCategory.objects(
                    tenant_id=tenant_id,
                    sku=sku
                ).first()
                
                if not supplier_category:
                    supplier_category = SupplierCategory(
                        tenant_id=tenant_id,
                        tenant_name=tenant_name,
                        sku=sku,
                        name=title_single,
                        system_key=sku,
                        display_name=generate_display_names(title_single),
                        slug=slugify(title_single or sku or ''),
                        source_slug=title_single,
                        description="",
                        has_manifest=True,
                        shareable=True,
                        media=[],
                        published=True,
                        vat=0,
                        production_days=[
                            {"day": "mon", "active": True, "deliver_before": "12:00"},
                            {"day": "tue", "active": True, "deliver_before": "12:00"},
                            {"day": "wed", "active": True, "deliver_before": "12:00"},
                            {"day": "thu", "active": True, "deliver_before": "12:00"},
                            {"day": "fri", "active": True, "deliver_before": "12:00"},
                            {"day": "sat", "active": False, "deliver_before": "12:00"},
                            {"day": "sun", "active": False, "deliver_before": "12:00"},
                        ],
                        price_build={
                            'collection': False,
                            'semi_calculation': False,
                            'full_calculation': False,
                            'external_calculation': False
                        },
                        has_products=False,
                        calculation_method=[
                            {
                                'name': 'Fixed price',
                                'slug': 'fixed-price',
                                'active': True
                            },
                            {
                                'name': 'Sliding scale',
                                'slug': 'sliding-scale',
                                'active': False
                            }
                        ],
                        start_cost=0,
                        created_at=datetime.now(),
                        updated_at=datetime.now()
                    )
                else:
                    supplier_category.name = title_single
                    supplier_category.display_name = generate_display_names(title_single)
                    supplier_category.slug = slugify(title_single or sku or '')
                    supplier_category.source_slug = title_single
                    supplier_category.updated_at = datetime.now()
                
                supplier_category.save()

                # Process properties (boxes and options)
                supplier_boxes = []
                supplier_options = []
                
                for prop in props:
                    # Skip boxes with no options (same as DWD logic)
                    if len(prop.get('options', [])) == 0:
                        continue
                        
                    box_name = prop.get('title') or prop.get('slug')
                    readable_name = re.sub(r'(?<!^)(?=[A-Z])', ' ', box_name)

                    source_slug = prop.get('slug') or box_name
                    box_slug = slugify(f"{box_name or box_name}")
                    box_locked = prop.get('locked', False)
                    
                    # Create/update SupplierBox
                    supplier_box = SupplierBox.objects(
                        tenant_id=tenant_id,
                        slug=box_slug
                    ).first()
                    
                    if not supplier_box:
                        supplier_box = SupplierBox(
                            sort=0,
                            tenant_id=tenant_id,
                            tenant_name=tenant_name,
                            sku=str(uuid.uuid4()),
                            name=readable_name,
                            display_name=generate_display_names(readable_name),
                            system_key=box_slug,
                            input_type='',
                            calc_ref='',
                            incremental=False,
                            select_limit=0,
                            option_limit=0,
                            sqm=False,
                            appendage=False,
                            calculation_type='',
                            slug=box_slug,
                            source_slug=source_slug,
                            description='',
                            media=[],
                            shareable=True,
                            start_cost=0,
                            published=True,
                            created_at=datetime.now(),
                            additional={},
                            linked=None
                        )
                    else:
                        supplier_box.name = box_name
                        supplier_box.display_name = generate_display_names(box_name)
                        supplier_box.source_slug = source_slug
                        supplier_box.updated_at = datetime.now()
                    
                    supplier_box.save()
                    supplier_boxes.append(supplier_box)

                    # Process options for this box
                    for opt in prop.get('options') or []:
                        opt_name = opt.get('name') or opt.get('slug')
                        source_slug = opt.get('slug') or opt_name
                        opt_slug = slugify(f"{opt_name or source_slug}")
                        excludes_pairs = opt.get('excludes') or []
                        
                        # Create/update SupplierOption
                        supplier_option = SupplierOption.objects(
                            tenant_id=tenant_id,
                            slug=opt_slug
                        ).first()
                        
                        if not supplier_option:
                            supplier_option = SupplierOption(
                                sort=0,
                                tenant_id=tenant_id,
                                tenant_name=tenant_name,
                                name=opt_name,
                                display_name=generate_display_names(opt_name),
                                slug=opt_slug,
                                source_slug=source_slug,
                                system_key=opt_slug,
                                description='',
                                information='',
                                media=[],
                                incremental_by=0,
                                published=True,
                                has_children=False,
                                input_type='radio',
                                extended_fields=[],
                                shareable=False,
                                sku=str(uuid.uuid4()),
                                children=[],
                                parent=False,
                                rpm=0,
                                sheet_runs=[],
                                runs=[],
                                boxes=[supplier_box],
                                additional={'excludes': excludes_pairs},
                                configure=[{
                                    'category_id': str(supplier_category.id),
                                    'configure': {
                                        'incremental_by': 0,
                                        'dimension': '2d',
                                        'dynamic': False,
                                        'dynamic_keys': [],
                                        'start_on': 0,
                                        'end_on': 0,
                                        'generate': False,
                                        'dynamic_type': '',
                                        'unit': 'mm',
                                        'start_cost': 0,
                                        'calculation_method': 'qty',
                                        'width': opt.get('width') or 0,
                                        'maximum_width': 0,
                                        'minimum_width': 0,
                                        'height': opt.get('height') or 0,
                                        'maximum_height': 0,
                                        'minimum_height': 0,
                                        'length': 0,
                                        'maximum_length': 0,
                                        'minimum_length': 0
                                    }
                                }],
                                created_at=datetime.now(),
                                dimension='2d',
                                dynamic=False,
                                dynamic_type='',
                                dynamic_keys=[],
                                start_on=0,
                                end_on=0,
                                generate=False,
                                unit='mm',
                                width=opt.get('width') or 0,
                                maximum_width=0,
                                minimum_width=0,
                                height=opt.get('height') or 0,
                                maximum_height=0,
                                minimum_height=0,
                                length=0,
                                maximum_length=0,
                                minimum_length=0,
                                start_cost=0,
                                calculation_method='qty',
                                linked=None
                            )
                        else:
                            supplier_option.name = opt_name
                            supplier_option.display_name = generate_display_names(opt_name)
                            supplier_option.source_slug = source_slug
                            supplier_option.additional = {'excludes': excludes_pairs}
                            supplier_option.width = opt.get('width') or 0
                            supplier_option.height = opt.get('height') or 0
                            if supplier_box not in supplier_option.boxes:
                                supplier_option.boxes.append(supplier_box)
                        
                        supplier_option.save()
                        supplier_options.append(supplier_option)

                # Create SupplierBoops with DWD-compatible structure
                boops_data = []
                for box in supplier_boxes:
                    box_options = [opt for opt in supplier_options if box in opt.boxes]
                    
                    # Create ops array with full DWD structure
                    ops = []
                    for opt in box_options:
                        ops.append({
                            "id": str(opt.id),
                            "ref_option": '',
                            "name": opt.name,
                            "display_name": opt.display_name,
                            "system_key": opt.system_key,
                            "slug": opt.slug,
                            "source_slug": opt.source_slug,
                            "description": opt.description,
                            "media": opt.media,
                            "dimension": opt.dimension,
                            "dynamic": opt.dynamic,
                            "sheet_runs": opt.sheet_runs,
                            "unit": opt.unit,
                            "width": opt.width,
                            "maximum_width": getattr(opt, 'maximum_width', 0),
                            "minimum_width": getattr(opt, 'minimum_width', 0),
                            "height": opt.height,
                            "maximum_height": getattr(opt, 'maximum_height', 0),
                            "minimum_height": getattr(opt, 'minimum_height', 0),
                            "length": getattr(opt, 'length', 0),
                            "maximum_length": getattr(opt, 'maximum_length', 0),
                            "minimum_length": getattr(opt, 'minimum_length', 0),
                            "start_cost": opt.start_cost,
                            "rpm": opt.rpm,
                            "additional": opt.additional,
                            "information": opt.information,
                            "input_type": opt.input_type,
                            "linked": getattr(opt, 'linked', None),
                            "excludes": opt.additional.get('excludes', []),
                            "dynamic_keys": getattr(opt, 'dynamic_keys', []),
                            "start_on": getattr(opt, 'start_on', 0),
                            "end_on": getattr(opt, 'end_on', 0),
                            "dynamic_type": getattr(opt, 'dynamic_type', ''),
                            "generate": getattr(opt, 'generate', False),
                            "dynamic_object": None
                        })
                    
                    boops_data.append({
                        "id": str(box.id),
                        "name": box.name,
                        "display_name": box.display_name,
                        "system_key": box.system_key,
                        "slug": box.slug,
                        "source_slug": box.source_slug,
                        "description": box.description,
                        "ref_box": '',
                        "sqm": box.sqm,
                        "appendage": box.appendage,
                        "calculation_type": box.calculation_type,
                        "media": box.media,
                        "input_type": box.input_type,
                        "linked": getattr(box, 'linked', None),
                        "published": box.published,
                        "ops": ops,
                        "divider": ""
                    })

                supplier_boops = SupplierBoops.objects(
                    tenant_id=tenant_id,
                    system_key=sku
                ).first()
                
                if not supplier_boops:
                    supplier_boops = SupplierBoops(
                        tenant_id=tenant_id,
                        ref_id="",
                        ref_boops_name="",
                        tenant_name=tenant_name,
                        supplier_category=supplier_category,
                        linked=getattr(supplier_category, 'linked', None),
                        display_name=supplier_category.display_name,
                        system_key=sku,
                        shareable=True,
                        published=True,
                        generated=True,
                        name=title_single,
                        slug=slugify(title_single or sku or ''),
                        divided=False,
                        boops=boops_data,
                        additional={}
                    )
                else:
                    supplier_boops.name = title_single
                    supplier_boops.display_name = supplier_category.display_name
                    supplier_boops.slug = slugify(title_single or sku or '')
                    supplier_boops.boops = boops_data
                    supplier_boops.supplier_category = supplier_category
                
                supplier_boops.save()

                # Call similarity services
                try:
                    categories = [{"name": title_single, "sku": sku}]
                    requests.post(
                        'http://assortments:5000/similarity/categories',
                        json={'tenant': tenant_id, 'tenant_name': tenant_name, 'categories': categories},
                        headers={"Content-type": "application/json"}, timeout=10
                    )
                except Exception:
                    pass

                try:
                    boxes = [{"name": box.name, "sku": ""} for box in supplier_boxes]
                    requests.post(
                        'http://assortments:5000/similarity/boxes',
                        json={'tenant': tenant_id, 'tenant_name': tenant_name, 'boxes': boxes},
                        headers={"Content-type": "application/json"}, timeout=10
                    )
                except Exception:
                    pass

                try:
                    options = [{"name": opt.name, "sku": ""} for opt in supplier_options]
                    if options:
                        requests.post(
                            'http://assortments:5000/similarity/options',
                            json={'tenant': tenant_id, 'tenant_name': tenant_name, 'options': options},
                            headers={"Content-type": "application/json"}, timeout=10
                        )
                except Exception:
                    pass

                results.append({
                    'supplier_category': {
                        'id': str(supplier_category.id),
                        'name': supplier_category.name,
                        'slug': supplier_category.slug,
                        'sku': supplier_category.sku
                    },
                    'supplier_boxes_count': len(supplier_boxes),
                    'supplier_options_count': len(supplier_options),
                    'boops': boops_data
                })

            return {
                "data": results,
                "message": f"Successfully imported {len(results)} items",
                "status": 200
            }

        except Exception as e:
            return {
                "data": [],
                "message": f"Import failed: {str(e)}",
                "status": 500
            }, 500


class GetPrice(Resource):
    def post(self):
        try:
            payload = request.get_json(force=True)

            # Extract data from the PHP plugin manager request
            tenant_id = payload.get('tenant_id')
            sku = payload.get('sku')
            options = payload.get('options', [])
            address = payload.get('address', {})

            # Format the request for Cartim API
            cartim_request = self._format_cartim_request(options)

            # Make request to Cartim API
            cartim_response = self._make_cartim_request(cartim_request, sku)

            if cartim_response is None:
                return {
                    "data": [],
                    "message": "Failed to get price from Cartim API",
                    "status": 500
                }, 500

            # Format response for PHP plugin manager
            formatted_response = self._format_response(cartim_response)

            return {
                "data": formatted_response,
                "message": "Price retrieved successfully",
                "status": 200
            }, 200

        except Exception as e:
            return {
                "data": [],
                "message": f"Error getting price: {str(e)}",
                "status": 500
            }, 500

    def _format_cartim_request(self, options):
        """Format the options array to Cartim API format"""
        cartim_data = {}

        # Convert options array to flat dictionary
        for option in options:
            if isinstance(option, dict):
                for key, value in option.items():
                    # Convert string numbers to integers where appropriate
                    if key == 'Quantity':
                        cartim_data['quantity'] = int(value)
                    elif key == 'paperWeight':
                        cartim_data['paperWeight'] = int(value)
                    else:
                        cartim_data[key] = value

        return cartim_data

    def _make_cartim_request(self, cartim_request, sku):
        """Make request to Cartim API"""
        try:
            # Get Cartim API configuration from environment
            cartim_url = os.environ.get('CARTIM_API_URL', 'https://api.cartimprint.be/api/v1')
            cartim_auth = os.environ.get('CARTIM_API_AUTH', 'cHJpbmR1c3RyeV9kZXY6VnUyX1JlYjJ9V2lyNw==')

            # Use sku as product parameter, default to 'poster'
            product = sku or 'poster'

            url = f"{cartim_url}/get-price?product={product}"
            headers = {
                'accept': '*/*',
                'authorization': f'Basic {cartim_auth}',
                'content-type': 'application/json'
            }

            print(f"Making request to Cartim API: {url}")
            print(f"Request data: {cartim_request}")

            response = requests.post(url, json=cartim_request, headers=headers, timeout=30)
            response.raise_for_status()

            # Check if response is JSON
            print(f"Response status: {response.status_code}")
            print(f"Response headers: {dict(response.headers)}")
            print(f"Response content: {response.text}")

            try:
                json_data = response.json()
                print(f"Cartim API JSON response: {json_data}")
                return json_data
            except ValueError as e:
                # If not JSON, return the text content
                print(f"Cartim API non-JSON response: {response.text}")
                print(f"JSON decode error: {str(e)}")
                return {"raw_response": response.text, "status_code": response.status_code}

        except requests.exceptions.RequestException as e:
            print(f"Cartim API request failed: {str(e)}")
            return None
        except Exception as e:
            print(f"Unexpected error in Cartim request: {str(e)}")
            return None

    def _format_response(self, cartim_response):
        """Format Cartim API response for PHP plugin manager"""
        try:
            print(f"Formatting response: {cartim_response} (type: {type(cartim_response)})")

            # Handle None response
            if cartim_response is None:
                return {"error": "No response from Cartim API"}

            # Extract price information from Cartim response
            if isinstance(cartim_response, dict):
                # Check if it's a direct price response (new format)
                if 'price' in cartim_response:
                    price = cartim_response.get('price', 0)
                    delivery_days = cartim_response.get('deliveryDays', 0)
                    promised_arrival = cartim_response.get('promisedArrivalDate')
                    delivery_type = cartim_response.get('DeliveryType', 'standard')
                    shipping_date = None
                    if 'configuration' in cartim_response:
                        configuration = cartim_response.get('configuration', [])
                        shipping_date = dict(configuration.get('shipping', {})).get('deliveryDate') if configuration.get(
                            'shipping') else None

                    return {
                        "price": float(price) if str(price).replace('.', '', 1).isdigit() else 0,
                        "deliveryDays": int(delivery_days) if delivery_days else 0,
                        "promisedArrivalDate": str(parse_nl_date(shipping_date)) or datetime.now(timezone.utc).isoformat(),
                        "DeliveryType": delivery_type
                    }
                
                # Check if it's a configuration response (legacy format)
                elif 'configuration' in cartim_response:
                    configuration = cartim_response.get('configuration', [])
                    shipping_date = dict(configuration.get('shipping', {})).get('deliveryDate') if configuration.get('shipping') else None
                    diff_days = 0
                    if shipping_date:
                        try:
                            delivery_date = parse_nl_date(shipping_date)
                            today = date.today()
                            diff_days = (delivery_date - today).days
                        except (ValueError, TypeError) as e:
                            print(f"Error parsing date: {e}")
                            diff_days = 0

                    return {
                        "price": float(cartim_response.get('price', 0)) if str(cartim_response.get('price', '')).replace('.', '', 1).isdigit() else 0,
                        "deliveryDays": diff_days,
                        "promisedArrivalDate": str(parse_nl_date(shipping_date)) or datetime.now(timezone.utc).isoformat(),
                        "DeliveryType": "standard"
                    }

                else:
                    return []
            else:
                # For any other type, return as string
                return {"raw_response": str(cartim_response)}

        except Exception as e:
            print(f"Error formatting response: {str(e)}")
            return {"error": f"Error formatting response: {str(e)}"}
    



class ValidatePair(Resource):
    def post(self):
        payload = request.get_json(force=True)
        return jsonify({
            "valid": False,
            "message": "Validate pair placeholder",
            "status": 501
        }), 501


class GetCategories(Resource):
    def get(self):
        try:
            path = os.environ.get('CARTIMPRINT_CATEGORIES_PATH', '/var/www/categories-boxes-options.json')
            with open(path, 'r') as f:
                raw = json.load(f)

            normalized = normalize_categories_payload(raw)

            # Map to DWD-compatible category shape
            dwd_like = []
            for cat in normalized.get('categories', []):
                name = cat.get('display_name') or cat.get('slug')
                slug = cat.get('slug')
                dwd_like.append({
                    "active": True,
                    "name": name,
                    "sku": slug,
                    "createdAt": None,
                    "introductionDate": None,
                    "titlePlural": name,
                    "titleSingle": slug,
                    "updatedAt": None
                })

            # Optional: return only slugs if fields=slug(s)
            fields = (request.args.get('fields') or '').lower()
            if fields in {'slug', 'slugs'}:
                slugs = [c.get('sku') for c in dwd_like]
                return jsonify({
                    "data": slugs,
                    "message": "Category slugs",
                    "status": 200
                })

            return jsonify({
                "data": dwd_like,
                "message": "Categories",
                "status": 200
            })
        except FileNotFoundError:
            return jsonify({
                "data": [],
                "message": "Categories file not found",
                "status": 404
            }), 404
        except Exception as exc:
            return jsonify({
                "data": [],
                "message": str(exc),
                "status": 500
            }), 500


class GetSecrets(Resource):
    def get(self):
        return jsonify({
            "data": {
                'url': os.environ.get('URL'),
                'api-secret': os.environ.get('API_SECRET'),
                'user-id': os.environ.get('USER_ID'),
            },
            "message": "Secrets placeholder",
            "status": 200
        })


NL_MONTHS = {
    "januari": 1, "februari": 2, "maart": 3, "april": 4, "mei": 5, "juni": 6,
    "juli": 7, "augustus": 8, "september": 9, "oktober": 10, "november": 11, "december": 12
}

def parse_nl_date(s: str) -> date:
    """Parse strings like 'donderdag 2 oktober 2025' → date(2025,10,2)."""
    s = s.strip().lower()
    # Grab "<day> <month> <year>" regardless of weekday or commas
    m = re.search(r'(\d{1,2})\s+([a-z]+)\s+(\d{4})', s)
    if not m:
        raise ValueError(f"Cannot parse Dutch date from: {s!r}")
    d = int(m.group(1))
    month_name = m.group(2)
    y = int(m.group(3))
    if month_name not in NL_MONTHS:
        raise ValueError(f"Unknown Dutch month: {month_name!r}")
    return date(y, NL_MONTHS[month_name], d)