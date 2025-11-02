import json

from flask import Response, request, jsonify
from models.category import Category
from models.supplierBoops import SupplierBoops
from models.supplierCategory import SupplierCategory
from models.manifest import Manifest
from flask_restful import Resource, fields, marshal
from helper.helper import generate_display_names, convert_object_id_fields, convert_bracket_notation_to_nested_dict, \
    remove_list_id_from_nested
from bson.json_util import dumps
from bson import ObjectId


class LinkedCategoryManifestApi(Resource):
    def get(self, category, supplier_id):
        category = SupplierCategory.objects(linked=category, tenant_id=supplier_id).first()
        manifest = SupplierBoops.objects(supplier_category=category, tenant_id=supplier_id).first()
        if manifest:
            return jsonify(manifest)
        return jsonify({
            "message": "Manifest not found",
            "status": 422
        })


class CategoryManifestApi(Resource):
    # Fetch the manifest for a given category
    def get(self, category):
        category = Category.objects(id=category).first()
        try:
            manifest = Manifest.objects(category=category.id).first()
            if manifest:
                return jsonify(manifest)
            return jsonify({
                "message": "Manifest not found",
                "status": 422
            })
        except:
            print('error')
    # Create a new manifest for a category
    @staticmethod
    def post(category):
        data = request.form.to_dict(flat=True)
        # gets the system category first
        system_category = Category.objects(id=category).first()
        keys_to_convert = {"id", "category", "linked"}
        manifest = Manifest.objects(category=system_category.id).first()
        if manifest:
            return jsonify({
                "message": "Manifest already exists",
                "status": 422
            })
        keys_with_type = {
            "published": bool,
            "shareable": bool,
            "appendage": bool,
            "parent": bool,
            "has_products": bool,
            "divided": bool,
            "dynamic": bool,
            "has_manifest": bool,
            "has_children": bool,
            "incremental": int,
            "incremental_by": int,
            "start_cost": int,
            "sort": int,
            "vat": float,
            "select_limit": int,
            "option_limit": int,
            "linked": None
        }

        if system_category:
            payload = CategoryManifestApi._generate_system_data(system_category, data, keys_to_convert, keys_with_type)
            try:
                saved = Manifest(**payload).save()
            except Exception as e:
                return CategoryManifestApi._error_response(str(e))

            return jsonify(saved)

        return jsonify({
            "message": f"Category not found, with id {category}",
            "status": 404
        })

    # Update the manifest for a category
    @staticmethod
    def put(category):
        data = request.form.to_dict(flat=True)
        system_category = Category.objects(id=category).first()
        keys_to_convert = {"id", "category", "linked"}
        keys_with_type = {
            "published": bool,
            "shareable": bool,
            "appendage": bool,
            "parent": bool,
            "has_products": bool,
            "divided": bool,
            "dynamic": bool,
            "has_manifest": bool,
            "has_children": bool,
            "incremental": int,
            "incremental_by": int,
            "start_cost": int,
            "sort": int,
            "vat": float,
            "select_limit": int,
            "option_limit": int
        }
        manifest = Manifest.objects(category=system_category.id).first()

        if system_category:
            payload = CategoryManifestApi._generate_system_data(system_category, data, keys_to_convert, keys_with_type)
            payload["divided"] = bool(int(payload["divided"])) if isinstance(payload["divided"], str) and payload[
                "divided"].isdigit() else payload["divided"]
            try:
                manifest.modify(**payload)
            except Exception as e:
                return CategoryManifestApi._error_response(str(e))

            return jsonify({
                "message": "Manifest has been updated successfully!",
                "status": 200
            })

        return jsonify({
            "message": f"Category not found, with id {category}",
            "status": 404
        })

    # Helper method to generate the payload data needed for creating/updating a manifest
    @staticmethod
    def _generate_system_data(system_category, data, keys_to_convert, keys_with_type):
        def ensure_linked_empty(obj):
            """ Recursively ensures all 'linked' fields are set to an empty string """
            if isinstance(obj, dict):
                if "linked" in obj:
                    obj["linked"] = ""  # Set linked to empty string
                for key, value in obj.items():
                    obj[key] = ensure_linked_empty(value)
            elif isinstance(obj, list):
                obj = [ensure_linked_empty(item) for item in obj]
            return obj
        
        def normalize_oids(obj):
            if isinstance(obj, dict):
                if "$oid" in obj and len(obj) == 1:
                    return ObjectId(obj["$oid"])
                return {k: normalize_oids(v) for k, v in obj.items()}
            elif isinstance(obj, list):
                return [normalize_oids(i) for i in obj]
            else:
                return obj


        boop = convert_bracket_notation_to_nested_dict(
            {k: v for k, v in data.items() if k.startswith('boops[')},  # Extract only 'boops' keys
            keys_to_convert,
            keys_with_type
        )
        payload = {
            'category': ObjectId(system_category.id),
            'display_name': system_category['display_name'],
            'system_key': system_category['system_key'],
            'additional': {},
            'slug': system_category['slug'],
            'name': system_category['name'],
            'tenant_id': "",
            'ref_id': "",
            'ref_boops_id': "",
            'ref_boops_name': "",
            'tenant_name': "",
            'shareable': False,
            'divided': data['divided'],
            'published': True,
            'generated': False,
            'has_manifest': True,
            'shared': system_category['shared'] if 'shared' in system_category else [],
            'boops': remove_list_id_from_nested(boop.get('boops', [])),
        }

        has_manifest = {'has_manifest': True}

        system_category.modify(**has_manifest)
        # Apply recursive function to ensure 'linked' is empty string everywhere
        payload = ensure_linked_empty(payload)
        payload = normalize_oids(payload)
        # Remove 'supplier' key if it exists
        payload.pop('supplier', None)
        payload.pop('listId', None)

        return payload

    # Helper method to return formatted error message on manifest update failure
    @staticmethod
    def _error_response(error):
        return jsonify({
            "message": f"An error occurred while updating the manifest. {error}",
            "status": 422
        })
