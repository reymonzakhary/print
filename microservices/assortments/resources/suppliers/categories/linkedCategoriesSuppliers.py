from flask import jsonify, request
from flask_restful import Resource
from bson import ObjectId
from datetime import datetime
from models.supplierBoops import SupplierBoops
from models.category import Category
from models.box import Box
from models.option import Option


def is_valid_object_id(val):
    try:
        if not val:
            return False
        if not isinstance(val, ObjectId):
            ObjectId(val)
        return True
    except Exception:
        return False


def make_json_safe(obj):
    if isinstance(obj, list):
        return [make_json_safe(i) for i in obj]
    elif isinstance(obj, dict):
        clean = {}
        for k, v in obj.items():
            if isinstance(v, datetime):
                continue
            clean[k] = make_json_safe(v)
        return clean
    elif isinstance(obj, ObjectId):
        return str(obj)
    return obj


class LinkedCategoriesSuppliersManifestApi(Resource):

    def post(self, linked, supplier_id):
        try:
            try:
                linked_obj = ObjectId(linked)
            except Exception:
                linked_obj = linked

            supplier_boops = SupplierBoops.objects(linked=linked_obj, tenant_id=supplier_id).first()
            if not supplier_boops:
                return jsonify({
                    "message": "No supplier boops found for this linked ID.",
                    "status": 404
                })

            category = supplier_boops.linked
            if not category:
                return jsonify({
                    "message": "System category not found.",
                    "status": 404
                })

            category_dict = category.to_mongo().to_dict()

            payload = LinkedCategoriesSuppliersManifestApi._generate_compact_manifest(category_dict, supplier_boops)

            payload = make_json_safe(payload)

            return jsonify({
                "data": payload,
                "message": "Supplier-linked compact manifest built successfully.",
                "status": 200
            })

        except Exception as e:
            return jsonify({
                "message": f"Error while building supplier manifest: {str(e)}",
                "status": 500
            })


    def _generate_compact_manifest(category_dict, supplier_boops):
        base_fields = [
            "_id", "slug", "ref_id", "name", "system_key", "display_name", 
            "published", "shareable", "has_manifest", "ref_boops_name", "divided",
            "generated", "shared"
        ]

        system_manifest = {k: category_dict.get(k) for k in base_fields if k in category_dict}
        system_manifest["category"] = supplier_boops.supplier_category.id
        system_manifest["boops"] = []
        
        for boop in supplier_boops.boops:
            try:
                linked_box_id = ObjectId(boop.get("linked"))
            except Exception:
                linked_box_id = boop.get("linked")

            box = Box.objects(id=linked_box_id).first()
            if not box:
                continue

            box_dict = box.to_mongo().to_dict()
            box_data = {
                "id": box.id,
                "sort": box_dict.get("sort"),
                "name": box_dict.get("name"),
                "sku": box_dict.get("sku"),
                "system_key": box_dict.get("system_key"),
                "display_name": box_dict.get("display_name"),
                "slug": box_dict.get("slug"),
                "description": box_dict.get("description"),
                "media": box_dict.get("media"),
                "divider": box_dict.get("divider"),
                "sqm": box_dict.get("sqm"),
                "incremental": box_dict.get("incremental"),
                "published": box_dict.get("published"),
                "select_limit": box_dict.get("select_limit"),
                "option_limit": box_dict.get("option_limit"),
                "input_type": box_dict.get("input_type"),
                "additional": box_dict.get("additional"),
                "appendage": box_dict.get("appendage"),
                "shareable": box_dict.get("shareable"),
                "ops": []
            }

            # only include minimal op info
            for op in boop.get("ops", []):
                try:
                    linked_op_id = ObjectId(op.get("linked"))
                except Exception:
                    linked_op_id = op.get("linked")

                option = Option.objects(id=linked_op_id).first()
                if not option:
                    continue

                option_dict = option.to_mongo().to_dict()
                compact_op = {
                    "id": option.id,
                    "sort": option_dict.get("sort"),
                    "name": option_dict.get("name"),
                    "description": option_dict.get("description"),
                    "incremental_by": option_dict.get("incremental_by"),
                    "dimension": option_dict.get("dimension"),
                    "dynamic": option_dict.get("dynamic"),
                    "unit": option_dict.get("unit"),
                    "width": option_dict.get("width"),
                    "maximum_width": option_dict.get("maximum_width"),
                    "minimum_width": option_dict.get("minimum_width"),
                    "height": option_dict.get("height"),
                    "maximum_height": option_dict.get("maximum_height"),
                    "minimum_height": option_dict.get("minimum_height"),
                    "length": option_dict.get("length"),
                    "maximum_length": option_dict.get("maximum_length"),
                    "minimum_length": option_dict.get("minimum_length"),
                    "parent": option_dict.get("parent"),
                    "published": option_dict.get("published"),
                    "has_children": option_dict.get("has_children"),
                    "extended_fields": option_dict.get("extended_fields"),
                    "display_name": option_dict.get("display_name"),
                    "sku": option_dict.get("sku"),
                    "slug": option_dict.get("slug"),
                    "media": option_dict.get("media"),
                    "system_key": option_dict.get("system_key"),
                    "input_type": option_dict.get("input_type"),
                    "published": option_dict.get("published"),
                    "information": option_dict.get("information"),
                }
                box_data["ops"].append(compact_op)

            system_manifest["boops"].append(box_data)

        return system_manifest



class LinkedCategoriesSuppliersApi(Resource):
    def get(self, linked):
        # Validate linked category ID
        if not is_valid_object_id(linked):
            return {
                "data": None,
                "message": "Invalid linked ID format.",
                "status": 400
            }, 200

        suppliers = SupplierBoops.objects(linked=ObjectId(linked))
        valid_suppliers = []

        for s in suppliers:
            all_boops_valid = True

            for boop in getattr(s, "boops", []):
                if not is_valid_object_id(boop.get("linked")):
                    all_boops_valid = False
                    break

                for op in boop.get("ops", []):
                    if not is_valid_object_id(op.get("linked")):
                        all_boops_valid = False
                        break

                if not all_boops_valid:
                    break

            if all_boops_valid:
                valid_suppliers.append({
                    "id": s.tenant_id,
                    "tenant_name": s.tenant_name,
                    "name": s.name,
                })

        if not valid_suppliers:
            return {
                "data": None,
                "message": "No suppliers found with valid boops and ops linked IDs.",
                "status": 404
            }, 200

        return {
            "data": valid_suppliers,
            "linked": linked
        }, 200