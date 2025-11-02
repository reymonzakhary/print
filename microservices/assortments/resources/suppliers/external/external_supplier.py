import uuid

from flask import Response, request, jsonify
from models.supplierCategory import SupplierCategory
from models.supplierBoops import SupplierBoops
from models.supplierOption import SupplierOption
from models.supplierBox import SupplierBox
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource, fields, marshal
from bson.json_util import dumps
import json
import datetime
import math
import requests
from bson import ObjectId
from helper.helper import (format_text, generate_display_names, build_slug_to_id_map, replace_exclusions_with_ids,
                           has_nullable_option, is_reseller_dropdown)


class ExternalSupplier(Resource):
    @staticmethod
    def post(tenant_id):
        data = request.get_json()

        # List of boxes to skip from the importing process
        boxes_to_skip = [
            'copies',
            'sorted_by_design',
            'packed_by_design',
            'specific_customer_options'
        ]

        # start to convert the object to our boops manifest
        boop_manifest = {}

        # TEMP
        supplier_data = request.get_json()

        # Iterate over a copy of the dictionary to avoid RuntimeError
        for key, value in data.copy().items():
            # Process 'sku' to create supplier category
            if key == 'sku':
                name = format_text(value) if isinstance(value, str) else value
                category_slug = slugify(name, to_lower=True)
                suppliers_category = SupplierCategory.objects(slug=category_slug, tenant_id=tenant_id).first()
                if not suppliers_category:
                    if supplier_data:  # Ensure supplier_data exists
                        sp = {
                            "name": name,
                            "tenant_id": tenant_id,
                            "tenant_name": supplier_data["tenant_name"],
                            "display_name": generate_display_names(name),
                            "system_key": value,
                            "source_slug": value,
                            "sku": str(uuid.uuid4()),
                            "description": "",
                            "has_manifest": True,
                            "shareable": True,
                            "media": [],
                            "published": True,
                            "vat": 0,
                            "production_days": [
                                {"day": "mon", "active": True, "deliver_before": "12:00"},
                                {"day": "tue", "active": True, "deliver_before": "12:00"},
                                {"day": "wed", "active": True, "deliver_before": "12:00"},
                                {"day": "thu", "active": True, "deliver_before": "12:00"},
                                {"day": "fri", "active": True, "deliver_before": "12:00"},
                                {"day": "sat", "active": False, "deliver_before": "12:00"},
                                {"day": "sun", "active": False, "deliver_before": "12:00"},
                            ]
                        }

                        suppliers_category = SupplierCategory(**sp).save()

                        # run similarity
                        categories = [{"name": name, "sku": ""}]
                        url = 'http://assortments:5000/similarity/categories'
                        obj = {'tenant': tenant_id, 'tenant_name': supplier_data["tenant_name"], 'categories': categories}
                        header = {"Content-type": "application/json"}
                        cat = requests.post(url, json=obj, headers=header)

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

                        suppliers_category.modify(ranges=suppliers_category['ranges'])

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
                            "display_name": generate_display_names(prop.get('title')),
                            "system_key": box_slug,
                            "input_type": '',
                            "incremental": False,
                            "select_limit": 0,
                            "option_limit": 0,
                            "sqm": False,
                            "media": [],
                            "description": "",
                            "shareable": True,
                            "source_slug": prop.get('slug'),
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
                            option_to_store = {
                                "tenant_id": tenant_id,
                                "tenant_name": supplier_data["tenant_name"],
                                "sku": str(uuid.uuid4()),
                                "sort": 0,
                                "media": [],
                                "name": option_name,
                                "system_key": option_slug,
                                "display_name": generate_display_names(option_name),
                                "description": '',
                                "information": '',
                                "additional": {},
                                "configure": [
                                    {
                                        "category_id": str(suppliers_category.id),
                                        "configure": {
                                            "incremental_by": 0,
                                            "dimension": '2d',
                                            "dynamic": prop.get('slug') == 'size' and option.get('slug') == 'custom',
                                            "dynamic_keys": [],
                                            "start_on": 0,
                                            "end_on": 0,
                                            "generate": False,
                                            "dynamic_type": 'format' if prop.get('slug') == 'size' and option.get(
                                                'slug') == 'custom' else '',  # ✅ Fixed syntax
                                            "unit": option.get('sizeUnit', 'mm'),
                                            "start_cost": 0,
                                            "calculation_method": 'qty',
                                            "width": int(option.get('width') or 0),
                                            "maximum_width": option.get('maxWidth', 0),
                                            "minimum_width": option.get('minWidth', 0),
                                            "height": int(option.get('height') or 0),
                                            "maximum_height": option.get('maxHeight', 0),
                                            "minimum_height": option.get('minHeight', 0),
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
                                "dynamic_type": '',
                                "end_on": 0,
                                "generate": False,
                                "start_on": 0,
                                "source_slug": [{f"{category_slug}{box_slug}": option.get('slug')}]
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
                            source_slug = {f"{category_slug}{box_slug}": option.get('slug')}

                            configure = {
                                "category_id": str(suppliers_category.id),
                                "configure": {
                                    "incremental_by": 0,
                                    "dimension": '2d',
                                    "dynamic": prop.get('slug') == 'size' and option.get('slug') == 'custom',
                                    "dynamic_keys": [],
                                    "start_on": 0,
                                    "end_on": 0,
                                    "generate": False,
                                    "dynamic_type": 'format' if prop.get('slug') == 'size' and option.get(
                                        'slug') == 'custom' else '',  # ✅ Fixed syntax
                                    "unit": option.get('sizeUnit', 'mm'),
                                    "start_cost": 0,
                                    "calculation_method": 'qty',
                                    "width": int(option.get('width') or 0),
                                    "maximum_width": option.get('maxWidth', 0),
                                    "minimum_width": option.get('minWidth', 0),
                                    "height": int(option.get('height') or 0),
                                    "maximum_height": option.get('maxHeight', 0),
                                    "minimum_height": option.get('minHeight', 0),
                                    "length": 0,
                                    "maximum_length": 0,
                                    "minimum_length": 0,
                                }
                            }
                            if "configure" not in supplier_option:
                                supplier_option['configure'] = []

                            # Append configure to the option
                            supplier_option['configure'].append(configure)
                            supplier_option['source_slug'].append(source_slug)

                            supplier_option.modify(configure=supplier_option['configure'], source_slug=supplier_option['source_slug'])

                        if supplier_option:
                            option['option_id'] = str(supplier_option.id)  # ✅ Store option_id inside each option
        # Step 1: Build mapping of slugs to option IDs
        slug_to_id_map = build_slug_to_id_map(data["properties"])

        # Step 2: Replace slugs in excludes arrays with option IDs
        data["properties"] = replace_exclusions_with_ids(data["properties"], slug_to_id_map)

        # build the manifest and replace the ides
        for key, value in data.items():
            if key == 'sku':
                suppliers_category = SupplierCategory.objects(slug=value, tenant_id=tenant_id).first()
                boop_manifest = {
                    "tenant_id": suppliers_category.tenant_id,
                    "ref_id": "",
                    "ref_boops_name": "",
                    "tenant_name": suppliers_category.tenant_name,
                    "supplier_category": suppliers_category.id,
                    "linked": suppliers_category.linked,
                    "display_name": suppliers_category.display_name,
                    "system_key": suppliers_category.system_key,
                    "shareable": True,
                    "published": True,
                    "generated": True,
                    "divided": False,
                    "name": suppliers_category.name,
                    "slug": suppliers_category.slug,
                    "boops": []
                }

        # ✅ Fix: Directly iterate over `properties`
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
                    "source_slug": supplier_option.source_slug,
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
                    "linked": supplier_option.linked,
                    "excludes": option.get('excludes'),
                    "dynamic_keys": supplier_option.dynamic_keys,
                    "start_on": supplier_option.start_on,
                    "end_on": supplier_option.end_on,
                    "dynamic_type": supplier_option.dynamic_type,
                    "generate": supplier_option.generate,
                    "dynamic_object": None
                })

            # ✅ Fix: Use `boop_manifest['boops'].append(...)`
            boop_manifest['boops'].append({
                "id": supplier_box.id,
                "name": supplier_box.name,
                "display_name": supplier_box.display_name,
                "system_key": supplier_box.system_key,
                "slug": supplier_box.slug,
                "source_slug": supplier_box.source_slug,
                "description": supplier_box.description,
                "ref_box": '',
                "sqm": supplier_box.sqm,
                "appendage": supplier_box.appendage,
                "calculation_type": supplier_box.calculation_type,
                "media": supplier_box.media,
                "input_type": supplier_box.input_type,
                "linked": supplier_box.linked,
                "published": supplier_box.published,
                "ops": ops
            })

        SupplierBoops(**boop_manifest).save()
        # print(json.dumps(boop_manifest, indent=4))
        return json.dumps(data, indent=4)
