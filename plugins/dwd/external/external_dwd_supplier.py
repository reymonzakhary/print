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


class ExternalDWDSupplier(Resource):

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

        data = request.get_json()
        tenant_id = data.get('tenant_id', "")

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

        # Iterate over a copy of the dictionary to avoid RuntimeError
        for key, value in data.copy().items():
            # Process 'sku' to create supplier category
            if key == 'sku':
                name = format_text(data.get('name') or data.get('titleSingle') or (value if isinstance(value, str) else ''))
                category_slug = slugify(name, to_lower=True)
                
                # Enhanced lookup with multiple safety conditions to avoid affecting unrelated records
                suppliers_category = SupplierCategory.objects(
                    slug=category_slug, 
                    tenant_id=tenant_id,
                    name__iexact=name,  # Case-insensitive name match
                    sku=data.get('sku')  # Match the SKU (Printdeal SKU)
                ).first()
                if not suppliers_category:
                    # Check for similar categories before creating new one
                    categories = [{
                        "name": name,
                        "sku": data.get('sku') or ""
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
                        sp = {
                            "name": name,
                            "slug": category_slug,
                            "tenant_id": tenant_id,
                            "tenant_name": supplier_data["tenant_name"],
                            "display_name": generate_display_names_no_translate(name),
                            "system_key": value,
                            "source_slug": name, # value,
                            "sku": str(data.get('sku') or value),
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
                            print(f"DEBUG: No main Category found with slug: {category_slug}")
                else:
                    # ensure sku stays in sync if provided
                    if data.get('sku') and suppliers_category.sku != str(data['sku']):
                        suppliers_category.sku = str(data['sku'])
                        suppliers_category.save()
                        print(f"DEBUG: updated SupplierCategory sku to {suppliers_category.sku}")

                # Store supplier_category_id in data **after the loop**
                if suppliers_category:
                    data['supplier_category_id'] = str(suppliers_category.id)

            # Process 'properties' if found

            if key == 'properties' and isinstance(value, list):
                for prop in value:
                    if prop.get('slug') == 'copies':
                        for printing_method_data in prop.get('rangeSets'):
                            for option_data in printing_method_data["options"]:
                                suppliers_category['ranges'].append({
                                    "name": printing_method_data["printingmethod"],
                                    "slug": printing_method_data["printingmethod"],
                                    "from": option_data['min'],
                                    "to": option_data['max'],
                                    "incremental_by": option_data['steps']
                                })


                    if prop.get('slug') == QUANTITY_BOX:
                        for option in prop.get('options', []):
                            QUANTITY_LIST.append(option['name'])

                    if prop.get('slug') in boxes_to_skip or len(prop.get('options', [])) == 0:
                        continue

                    box_name = prop.get('title')
                    box_slug = slugify(box_name, to_lower=True)

                    supplier_box = SupplierBox.objects(slug=slugify(box_name, to_lower=True),
                                                       tenant_id=tenant_id).first()
                    if not supplier_box:
                        data_to_store = {
                            "tenant_id": tenant_id,
                            "tenant_name": supplier_data["tenant_name"],
                            "sku": str(uuid.uuid4()),
                            "name": box_name,
                            "display_name": generate_display_names_no_translate(prop.get('title')),
                            "system_key": box_slug,
                            "input_type": '',
                            "incremental": False,
                            "select_limit": 0,
                            "option_limit": 0,
                            "sqm": False,
                            "media": [],
                            "description": "",
                            "shareable": False,
                            "source_slug": box_name, #prop.get('slug'),
                            "linked": None,
                            "start_cost": 0,
                            "published": True,
                        }

                        supplier_box = SupplierBox(**data_to_store).save()

                        boxes = [{
                            "name": prop.get('title'),
                            "sku": ""}]
                        url = 'http://assortments:5000/similarity/boxes'
                        obj = {'tenant': tenant_id, 'tenant_name': supplier_data["tenant_name"], 'boxes': boxes}

                        header = {"Content-type": "application/json"}
                        # return json.dumps(boxes)
                        res = requests.post(url, json=obj, headers=header)

                    # Ensure box display_name is not translated even for existing boxes
                    if supplier_box:
                        supplier_box.modify(display_name=generate_display_names_no_translate(prop.get('title')))

                    # Store supplier_category_id in data **after the loop**
                    if supplier_box:
                        prop['box_id'] = str(supplier_box.id)

                    for option in prop.get('options', []):  # ✅ Ensure 'options' key exists
                        option_name = option.get('name')
                        if not option_name:
                            option_name = format_text(option.get('slug'))
                        option_slug = slugify(option_name, to_lower=True)
                        supplier_option = SupplierOption.objects(tenant_id=tenant_id, slug=option_slug).first()

                        if not supplier_option:
                            unit = 'mm'
                            incremental_by = 0
                            minimum_width = 0
                            max_width = 0
                            minimum_height = 0
                            max_height = 0
                            custom = (option.get('custom') or {})
                            if custom.get('width'):
                                unit = custom.get('width').get('unitOfMeasure') or 'mm'
                                incremental_by = custom.get('width').get('increment') or 0
                                minimum_width = custom.get('width').get('minimum') or 0
                                max_width = custom.get('width').get('maximum') or 0
                            if custom.get('height'):
                                unit = custom.get('height').get('unitOfMeasure') or 'mm'
                                incremental_by = custom.get('height').get('increment') or 0
                                minimum_height = custom.get('height').get('minimum') or 0
                                max_height = custom.get('height').get('maximum') or 0
                            option_to_store = {
                                "tenant_id": tenant_id,
                                "tenant_name": supplier_data["tenant_name"],
                                "sku": str(uuid.uuid4()),
                                "sort": 0,
                                "media": [],
                                "name": option_name,
                                "system_key": option_slug,
                                "display_name": generate_display_names_no_translate(option_name),
                                "description": '',
                                "information": '',
                                "additional": {},
                                "width": minimum_width,
                                "maximum_width": max_width,
                                "minimum_width": minimum_width,
                                "height": minimum_height,
                                "maximum_height": max_height,
                                "minimum_height": minimum_height,
                                "length": 0,
                                "maximum_length": 0,
                                "minimum_length": 0,
                                "dynamic_type": 'format' if option.get('slug') == 'custom' else '',
                                "unit": unit,
                                "dynamic": True if option.get('slug') == 'custom' else False,
                                "configure": [
                                    {
                                        "category_id": str(suppliers_category.id),
                                        "configure": {
                                            "incremental_by": incremental_by,
                                            "dimension": '2d',
                                            "dynamic": True if option.get('slug') == 'custom' else False,
                                            "dynamic_keys": [],
                                            "start_on": 0,
                                            "end_on": 0,
                                            "generate": False,
                                            "dynamic_type": 'format' if option.get('slug') == 'custom' else '',
                                            "unit": unit,
                                            "start_cost": 0,
                                            "calculation_method": 'qty',
                                            "width": minimum_width,
                                            "maximum_width": max_width,
                                            "minimum_width": minimum_width,
                                            "height": minimum_height,
                                            "maximum_height": max_height,
                                            "minimum_height": minimum_height,
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
                                "source_slug": option_name
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
                            # Overwrite as direct string (model expects StringField)
                            supplier_option['source_slug'] = option_name

                            unit = 'mm'
                            incremental_by = 0
                            minimum_width = 0
                            max_width = 0
                            minimum_height = 0
                            max_height = 0
                            custom = (option.get('custom') or {})
                            if custom.get('width'):
                                unit = custom.get('width').get('unitOfMeasure') or 'mm'
                                incremental_by = custom.get('width').get('increment') or 0
                                minimum_width = custom.get('width').get('minimum') or 0
                                max_width = custom.get('width').get('maximum') or 0
                            elif custom.get('height'):
                                unit = custom.get('height').get('unitOfMeasure') or 'mm'
                                incremental_by = custom.get('height').get('increment') or 0
                                minimum_height = custom.get('height').get('minimum') or 0
                                max_height = custom.get('height').get('maximum') or 0

                            configure = {
                                "category_id": str(suppliers_category.id),
                                "configure": {
                                    "incremental_by": incremental_by,
                                    "dimension": '2d',
                                    "dynamic": True if option.get('slug') == 'custom' else False,
                                    "dynamic_keys": [],
                                    "start_on": 0,
                                    "end_on": 0,
                                    "generate": False,
                                    "dynamic_type": 'format' if option.get('slug') == 'custom' else '',  # ✅ Fixed syntax
                                    "unit": unit,
                                    "start_cost": 0,
                                    "calculation_method": 'qty',
                                    "width": minimum_width,
                                    "maximum_width": max_width,
                                    "minimum_width": minimum_width,
                                    "height": minimum_height,
                                    "maximum_height": max_height,
                                    "minimum_height": minimum_height,
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
                            option['option_id'] = str(supplier_option.id)  # ✅ Store option_id inside each option

        # Step 1: Build mapping of slugs to option IDs
        slug_to_id_map = build_slug_to_id_map(data["properties"])

        # Step 2: Replace slugs in excludes arrays with option IDs
        data["properties"] = replace_exclusions_with_ids(data["properties"], slug_to_id_map)

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

        # build the manifest and replace the ides
        for key, value in data.items():
            if key == 'sku':
                # Use the same category_slug that was used for creation/lookup earlier
                name = format_text(data.get('name') or data.get('titleSingle') or (value if isinstance(value, str) else ''))
                category_slug = slugify(name, to_lower=True)
                
                # Enhanced lookup with multiple safety conditions to avoid affecting unrelated records
                suppliers_category = SupplierCategory.objects(
                    slug=category_slug, 
                    tenant_id=tenant_id,
                    name__iexact=name,  # Case-insensitive name match
                    sku=data.get('sku')  # Match the SKU (Printdeal SKU)
                ).first()
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
            suppliers_category.modify(ranges=ranges)

        # Fix: Directly iterate over `properties`
        for prop in data.get('properties', []):

            if ((prop.get('slug') == 'copies' or len(prop.get('options', [])) == 0) or
                    is_reseller_dropdown(data, prop.get('slug')) or has_nullable_option(data, prop.get('slug')) or prop.get('slug') in boxes_to_skip):
                continue
            box_name = prop.get('title')
            supplier_box = SupplierBox.objects(slug=slugify(box_name, to_lower=True),
                                               tenant_id=tenant_id).no_dereference().first()

            if not supplier_box:  # Skip if no supplier box found
                continue

            ops = []
            for option in prop.get('options', []):
                option_name = option.get('name', format_text(option.get('slug')))
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
                    "excludes": option.get('excludes'),
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
        return jsonify(data)
