import uuid
from flask import Response, request, jsonify
from models.supplierCategory import SupplierCategory
from models.supplierBoops import SupplierBoops
from models.supplierOption import SupplierOption
from models.supplierBox import SupplierBox
from models.category import Category
from slugify import slugify
from flask_restful import Resource, fields, marshal
import json
import requests
from helper.helper import (format_text, build_slug_to_id_map, replace_exclusions_with_ids,
                           has_nullable_option, is_reseller_dropdown, DEFAULT_LANGUAGES)
from services.sync import degroot_service, build_option_catalog, flatten_products_tree, learn_segment_mapping_from_products, ApplyExcludesByBoops


class ExternalDeGrootSupplier(Resource):
    """External import endpoint for de-groot supplier data"""

    @staticmethod
    def post():
        def get_quantity_ranges(quantity_list):
            # Convert string list to int
            quantities = sorted(set(map(int, quantity_list)))

            if len(quantities) < 2:
                return [{"range": f"{quantities[0]}", "difference": 0}] if quantities else []

            groups = []
            start_index = 0

            while start_index < len(quantities) - 1:
                current_diff = quantities[start_index + 1] - quantities[start_index]
                end_index = start_index + 1

                # Expand group as long as the same difference continues
                while (end_index + 1 < len(quantities) and
                       quantities[end_index + 1] - quantities[end_index] == current_diff):
                    end_index += 1

                group_range = f"{quantities[start_index]}-{quantities[end_index]}"
                groups.append({
                    "range": group_range,
                    "from": quantities[start_index],
                    "to": quantities[end_index],
                    "difference": current_diff
                })

                start_index = end_index

            return groups

        def to_oid(val):
            try:
                # DBRef has .id, mongoengine Document has .id
                return getattr(val, 'id', val.id)
            except Exception:
                return getattr(val, 'id', val)

        def generate_display_names_no_translate(display_name: str):
            """
            Build display_name list using the same ISO keys as DEFAULT_LANGUAGES,
            but without translation; return the original text for each iso.
            """
            return [{"iso": iso, "display_name": display_name} for iso in DEFAULT_LANGUAGES.keys()]

        def get_similarity_display_name(kind: str, name: str):
            """
            Try to fetch display_name list from the similarity service.
            kind: one of 'categories', 'boxes', 'options'
            Returns list[ { iso, display_name } ] or None.
            """
            try:
                url = f'http://assortments:5000/similarity/{kind}'
                payload_key = {
                    'categories': 'categories',
                    'boxes': 'boxes',
                    'options': 'options',
                }.get(kind)
                if not payload_key:
                    return None
                items = [{"name": name, "sku": ""}]
                obj = {'tenant': tenant_id, 'tenant_name': supplier_data["tenant_name"], payload_key: items}
                header = {"Content-type": "application/json"}
                res = requests.post(url, json=obj, headers=header)
                if res.status_code == 200:
                    body = res.json()
                    data_arr = body.get('data') or []
                    if len(data_arr) > 0:
                        dn = data_arr[0].get('display_name')
                        if dn:
                            return dn
            except Exception:
                return None
            return None

        data = request.get_json()
        tenant_id = data.get('tenant_id', "")
        articlenumber = data.get('articlenumber', "")

        # List of boxes to skip from the importing process
        boxes_to_skip = [
            'quantity',
            'delivery_days',
            'delivery_type'
        ]

        QUANTITY_BOX = 'quantity'
        QUANTITY_LIST = []

        # start to convert the object to our boops manifest
        boop_manifest = {}

        # TEMP
        supplier_data = request.get_json()

        # Fetch article data from de-groot API
        try:
            # Get categories, options, and products for the article
            categories = degroot_service.get_categories(tenant_id)
            options = degroot_service.get_article_options(tenant_id, articlenumber)
            products = degroot_service.get_article_products(tenant_id, articlenumber)
            
            # Build option catalog and flatten products
            option_catalog = build_option_catalog(options)
            flat_rows = flatten_products_tree(products)
            # Learn segments mapping once per article
            segment_mapping = learn_segment_mapping_from_products(flat_rows)
            value_signatures = segment_mapping.get("value_signature", {})
            
            # Find the specific article in categories
            article_data = None
            for category in categories:
                if category.get('articlenumber') == articlenumber:
                    article_data = category
                    break
            
            if not article_data:
                return {
                    "data": [],
                    "message": f"Article {articlenumber} not found",
                    "status": 404
                }
            
        except Exception as e:
            return {
                "data": [],
                "message": f"Failed to fetch article data: {str(e)}",
                "status": 400
            }

        # Process the article data
        name = format_text(article_data.get('articlename', ''))
        category_slug = slugify(name, to_lower=True)
        
        # Enhanced lookup with multiple safety conditions to avoid affecting unrelated records
        suppliers_category = SupplierCategory.objects(
            slug=category_slug, 
            tenant_id=tenant_id,
            name__iexact=name,  # Case-insensitive name match
            sku=articlenumber  # Match the article number
        ).first()
        
        if not suppliers_category:
            # Check for similar categories before creating new one
            categories = [{
                "name": name,
                "sku": articlenumber or ""
            }]
            url = 'http://assortments:5000/similarity/categories'
            obj = {'tenant': tenant_id, 'tenant_name': supplier_data["tenant_name"], 'categories': categories}

            header = {"Content-type": "application/json"}
            try:
                res = requests.post(url, json=obj, headers=header)
                if res.status_code == 200:
                    similar_data = res.json()
                    if similar_data.get('data') and len(similar_data['data']) > 0:
                        similar_category = similar_data['data'][0]
                        suppliers_category = SupplierCategory.objects(id=similar_category.get('id')).first()
            except Exception as e:
                pass
            
            if not suppliers_category and supplier_data:  # Ensure supplier_data exists
                # Prefer display_name from similarity service, fallback to no-translate
                category_display_name = get_similarity_display_name('categories', name) or \
                    generate_display_names_no_translate(name)

                sp = {
                    "name": name,
                    "slug": category_slug,
                    "tenant_id": tenant_id,
                    "tenant_name": supplier_data["tenant_name"],
                    "display_name": category_display_name,
                    "system_key": articlenumber,
                    "source_slug": articlenumber,
                    "sku": str(articlenumber),
                    "description": "",
                    "has_manifest": False,
                    "shareable": False,
                    "media": [],
                    "published": True,
                    "vat": 0,
                    "linked": to_oid(data.get('linked')) or None,
                    "production_days": [
                        {"day": "mon", "active": True, "deliver_before": "12:00"},
                        {"day": "tue", "active": True, "deliver_before": "12:00"},
                        {"day": "wed", "active": True, "deliver_before": "12:00"},
                        {"day": "thu", "active": True, "deliver_before": "12:00"},
                        {"day": "fri", "active": True, "deliver_before": "12:00"},
                        {"day": "sat", "active": False, "deliver_before": "12:00"},
                        {"day": "sun", "active": False, "deliver_before": "12:00"},
                    ],
                    'price_build': {
                        'collection': False,
                        'semi_calculation': False,
                        'full_calculation': False,
                        'external_calculation': False
                    },
                    'has_products': False,
                    'calculation_method': [
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
                    ]
                }

                suppliers_category = SupplierCategory(**sp).save()
                
                # Find the main Category by slug and set linked field
                main_category = Category.objects(slug=category_slug).first()
                if main_category:
                    suppliers_category.linked = main_category.id
                    suppliers_category.save()
                    print(f"DEBUG: Set linked field to main Category ID: {main_category.id}")
                else:
                    print(f"DEBUG: No main Category found with: {category_slug}")
        else:
            # ensure sku stays in sync if provided
            if articlenumber and suppliers_category.sku != str(articlenumber):
                suppliers_category.sku = str(articlenumber)
                suppliers_category.save()
                print(f"DEBUG: updated SupplierCategory sku to {suppliers_category.sku}")

        # Store supplier_category_id in data **after the loop**
        if suppliers_category:
            data['supplier_category_id'] = str(suppliers_category.id)

        # Process options from the option catalog
        for group_name, option_values in option_catalog.items():
            if group_name in boxes_to_skip or len(option_values) == 0:
                continue

            box_name = group_name
            box_slug = slugify(box_name, to_lower=True)

            supplier_box = SupplierBox.objects(slug=slugify(box_name, to_lower=True),
                                               tenant_id=tenant_id).first()
            if not supplier_box:
                # Prefer display_name from similarity service, fallback to no-translate
                box_display_name = get_similarity_display_name('boxes', group_name) or \
                    generate_display_names_no_translate(group_name)
                data_to_store = {
                    "tenant_id": tenant_id,
                    "tenant_name": supplier_data["tenant_name"],
                    "sku": str(uuid.uuid4()),
                    "name": box_name,
                    "display_name": box_display_name,
                    "system_key": box_slug,
                    "input_type": '',
                    "incremental": False,
                    "select_limit": 0,
                    "option_limit": 0,
                    "sqm": False,
                    "media": [],
                    "description": "",
                    "shareable": False,
                    "source_slug": box_name,
                    "linked": None,
                    "start_cost": 0,
                    "published": True,
                }

                supplier_box = SupplierBox(**data_to_store).save()

                boxes = [{
                    "name": group_name,
                    "sku": ""
                }]
                url = 'http://assortments:5000/similarity/boxes'
                obj = {'tenant': tenant_id, 'tenant_name': supplier_data["tenant_name"], 'boxes': boxes}

                header = {"Content-type": "application/json"}
                res = requests.post(url, json=obj, headers=header)

            # Ensure box display_name uses similarity display_name if available, otherwise no-translate
            if supplier_box:
                existing_box_display_name = (
                    get_similarity_display_name('boxes', group_name) or
                    generate_display_names_no_translate(group_name)
                )
                supplier_box.modify(display_name=existing_box_display_name)

            # Store supplier_category_id in data **after the loop**
            if supplier_box:
                data['box_id'] = str(supplier_box.id)

            # Process options within this group
            for value_id, value_data in option_values.items():
                option_name = value_data.get('name_nl', f'option_{value_id}')
                option_slug = slugify(option_name, to_lower=True)
                supplier_option = SupplierOption.objects(tenant_id=tenant_id, slug=option_slug).first()
                # Determine segment string for this value_id, if available
                seg_list = []
                try:
                    sig = value_signatures.get(value_id) or {}
                    seg_list = sig.get("segments") or []
                except Exception:
                    seg_list = []
                segment_str = "-".join(sorted(seg_list)) if seg_list else None

                if not supplier_option:
                    # Prefer display_name from similarity service, fallback to no-translate
                    option_display_name = get_similarity_display_name('options', option_name) or \
                        generate_display_names_no_translate(option_name)

                    option_to_store = {
                        "tenant_id": tenant_id,
                        "tenant_name": supplier_data["tenant_name"],
                        "sku": str(uuid.uuid4()),
                        "sort": 0,
                        "media": [],
                        "name": option_name,
                        "system_key": option_slug,
                        "display_name": option_display_name,
                        "description": '',
                        "information": '',
                        "additional": {},
                        "width": 0,
                        "maximum_width": 0,
                        "minimum_width": 0,
                        "height": 0,
                        "maximum_height": 0,
                        "minimum_height": 0,
                        "length": 0,
                        "maximum_length": 0,
                        "minimum_length": 0,
                        "dynamic_type": '',
                        "unit": 'mm',
                        "dynamic": False,
                        "configure": [
                            {
                                "category_id": str(suppliers_category.id),
                                "configure": {
                                    "incremental_by": 0,
                                    "dimension": '2d',
                                    "dynamic": False,
                                    "dynamic_keys": [],
                                    "start_on": 0,
                                    "end_on": 0,
                                    "generate": False,
                                    "dynamic_type": '',
                                    "unit": 'mm',
                                    "start_cost": 0,
                                    "calculation_method": 'qty',
                                    "width": 0,
                                    "maximum_width": 0,
                                    "minimum_width": 0,
                                    "height": 0,
                                    "maximum_height": 0,
                                    "minimum_height": 0,
                                    "length": 0,
                                    "maximum_length": 0,
                                    "minimum_length": 0,
                                }
                            },
                        ],
                        "published": True,
                        "input_type": 'radio',
                        "shareable": False,
                        "parent": False,
                        "rpm": 0,
                        "sheet_runs": [],
                        "runs": [],
                        "dynamic_keys": [],
                        "end_on": 0,
                        "generate": False,
                        "start_on": 0,
                        "source_slug": (segment_str or option_name)
                    }

                    supplier_option = SupplierOption(**option_to_store).save()

                    options = [{
                        "name": option_name,
                        "sku": ""
                    }]
                    url = 'http://assortments:5000/similarity/options'
                    obj = {'tenant': tenant_id, 'tenant_name': supplier_data["tenant_name"], 'options': options}
                    header = {"Content-type": "application/json"}
                    res = requests.post(url, json=obj, headers=header)
                else:
                    # Update source_slug to segment string if available, otherwise keep option name
                    supplier_option['source_slug'] = (segment_str or option_name)

                    configure = {
                        "category_id": str(suppliers_category.id),
                        "configure": {
                            "incremental_by": 0,
                            "dimension": '2d',
                            "dynamic": False,
                            "dynamic_keys": [],
                            "start_on": 0,
                            "end_on": 0,
                            "generate": False,
                            "dynamic_type": '',
                            "unit": 'mm',
                            "start_cost": 0,
                            "calculation_method": 'qty',
                            "width": 0,
                            "maximum_width": 0,
                            "minimum_width": 0,
                            "height": 0,
                            "maximum_height": 0,
                            "minimum_height": 0,
                            "length": 0,
                            "maximum_length": 0,
                            "minimum_length": 0,
                        }
                    }
                    if "configure" not in supplier_option:
                        supplier_option['configure'] = []

                    # Append configure to the option
                    supplier_option['configure'].append(configure)
                    supplier_option.modify(
                        configure=supplier_option['configure'],
                        source_slug=supplier_option['source_slug']
                    )

                if supplier_option:
                    data[f'option_{value_id}_id'] = str(supplier_option.id)

        # Build the manifest and replace the IDs
        name = format_text(article_data.get('articlename', ''))
        category_slug = slugify(name, to_lower=True)
        
        # Enhanced lookup with multiple safety conditions to avoid affecting unrelated records
        suppliers_category = SupplierCategory.objects(
            slug=category_slug, 
            tenant_id=tenant_id,
            name__iexact=name,  # Case-insensitive name match
            sku=articlenumber  # Match the article number
        ).first()
        
        ranges = []
        if QUANTITY_LIST:
            rngs = get_quantity_ranges(sorted(QUANTITY_LIST))
            for rng in rngs:
                ranges.append({
                    'name': 'All',
                    'slug': 'all',
                    'from': rng.get('from'),
                    'to': rng.get('to'),
                    'incremental_by': rng.get('difference'),
                })

        boop_manifest = {
            "tenant_id": suppliers_category.tenant_id,
            "ref_id": "",
            "ref_boops_name": "",
            "tenant_name": suppliers_category.tenant_name,
            "supplier_category": suppliers_category.id,
            "linked": suppliers_category.linked,
            "display_name": suppliers_category.display_name,
            "system_key": suppliers_category.system_key,
            "shareable": False,
            "published": True,
            "generated": True,
            "divided": False,
            "name": suppliers_category.name,
            "slug": suppliers_category.slug,
            "boops": []
        }
        
        if suppliers_category:
            suppliers_category.modify(ranges=ranges)

        # Build boops from option catalog
        for group_name, option_values in option_catalog.items():
            if group_name in boxes_to_skip or len(option_values) == 0:
                continue
                
            box_name = group_name
            supplier_box = SupplierBox.objects(slug=slugify(box_name, to_lower=True),
                                               tenant_id=tenant_id).no_dereference().first()

            if not supplier_box:  # Skip if no supplier box found
                continue

            ops = []
            for value_id, value_data in option_values.items():
                option_name = value_data.get('name_nl', f'option_{value_id}')
                option_slug = slugify(option_name, to_lower=True)

                supplier_option = SupplierOption.objects(tenant_id=tenant_id, slug=option_slug).no_dereference().first()
                if not supplier_option:  # Skip if no supplier option found
                    continue

                ops.append({
                    "id": supplier_option.id,
                    "ref_option": '',
                    "name": supplier_option.name,
                    "display_name": supplier_option.display_name,
                    "system_key": supplier_option.system_key,
                    "slug": supplier_option.slug,
                    "source_slug": getattr(supplier_option, 'source_slug', ''),
                    "description": supplier_option.description,
                    "media": supplier_option.media,
                    "dimension": supplier_option.dimension,
                    "dynamic": supplier_option.dynamic,
                    "sheet_runs": supplier_option.sheet_runs,
                    "unit": supplier_option.unit,
                    "width": supplier_option.width,
                    "maximum_width": supplier_option.maximum_width,
                    "minimum_width": supplier_option.minimum_width,
                    "height": supplier_option.height,
                    "maximum_height": supplier_option.maximum_height,
                    "minimum_height": supplier_option.minimum_height,
                    "length": supplier_option.length,
                    "maximum_length": supplier_option.maximum_length,
                    "minimum_length": supplier_option.minimum_length,
                    "start_cost": supplier_option.start_cost,
                    "rpm": supplier_option.rpm,
                    "additional": supplier_option.additional,
                    "information": supplier_option.information,
                    "input_type": supplier_option.input_type,
                    "linked": to_oid(supplier_option.linked),
                    "excludes": [],
                    "dynamic_keys": supplier_option.dynamic_keys,
                    "start_on": supplier_option.start_on,
                    "end_on": supplier_option.end_on,
                    "dynamic_type": supplier_option.dynamic_type,
                    "generate": supplier_option.generate,
                    "dynamic_object": None
                })

            boop_manifest['boops'].append({
                "id": supplier_box.id,
                "name": supplier_box.name,
                "display_name": supplier_box.display_name,
                "system_key": supplier_box.system_key,
                "slug": supplier_box.slug,
                "source_slug": supplier_box.name,
                "description": supplier_box.description,
                "ref_box": '',
                "sqm": supplier_box.sqm,
                "appendage": supplier_box.appendage,
                "calculation_type": supplier_box.calculation_type,
                "media": supplier_box.media,
                "input_type": supplier_box.input_type,
                "linked": to_oid(supplier_box.linked),
                "published": supplier_box.published,
                "ops": ops,
                "divider": ""
            })

        SupplierBoops(**boop_manifest).save()


        # Apply excludes-by-boops after successful import
        try:           
            # Create a temporary mock get_json function for ApplyExcludesByBoops
            exclude_payload = {
                'tenant_id': tenant_id,
                'articlenumber': articlenumber
            }
            
            # Store original get_json method
            original_get_json = request.get_json
            
            # Monkey-patch get_json to return our payload
            def mock_get_json(silent=False):
                if silent:
                    return exclude_payload
                return exclude_payload
            
            request.get_json = mock_get_json
            
            try:
                apply_excludes_resource = ApplyExcludesByBoops()
                apply_excludes_result = apply_excludes_resource.post()
                
                # Extract JSON data from response (it's already a dict)
                apply_excludes_data = apply_excludes_result
                
                # Add apply_excludes_result to response data
                if isinstance(data, dict):
                    data['apply_excludes_result'] = apply_excludes_data
            finally:
                # Restore original get_json method
                request.get_json = original_get_json
                
        except Exception as excludes_error:
            # Log error but don't fail the import
            print("ERROR in ApplyExcludesByBoops:", str(excludes_error))
            import traceback
            print(traceback.format_exc())
            if isinstance(data, dict):
                data['apply_excludes_result'] = {
                    "message": f"Failed to apply excludes: {str(excludes_error)}",
                    "status": "error"
                }
        
        return jsonify(data)
