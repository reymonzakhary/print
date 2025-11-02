from flask import Response, request, jsonify
import requests
from models.supplierCategory import SupplierCategory
from models.category import Category
from slugify import slugify, Slugify, UniqueSlugify
from resources.suppliers.categories.category import SupplierCategoriesApi
from flask_restful import Resource, fields, marshal
from models.supplierBoops import SupplierBoops
from models.supplierBox import SupplierBox
from models.supplierOption import SupplierOption
from bson.json_util import dumps
from bson.objectid import ObjectId

import json


class LinkSupplierCategoryToResellerApi(Resource):

    def post(self, supplier_id, slug):
        # data = request.form.to_dict(flat=True)
        data = request.get_json()
        supplier_category = SupplierCategory.objects(slug=slug, tenant_id=supplier_id).first()

        if supplier_category:
            data_to_store = supplier_category.to_mongo()
        else:
            return {
                       "data": None,
                       "message": "this supplier dose not had this category.",
                       "status": 422
                   }, 200
        # return linked
        if SupplierCategory.objects(tenant_id=data['tenant_id'], slug=slug).count():
            return {
                       "data": None,
                       "message": "Category already exists.",
                       "status": 422
                   }, 200
        else:
            # store Data
            cat_display_name = []
            for lang in data['lang']:
                cat_display_name.append({
                    'iso': lang,
                    'display_name': data['name'] if data['name'] else supplier_category.name,
                })
            del data_to_store['_id']
            data_to_store['ref_id'] = supplier_id
            data_to_store['ref_category_id'] = supplier_category.id
            data_to_store['ref_category_name'] = supplier_category.name
            data_to_store['tenant_id'] = data['tenant_id']
            data_to_store['tenant_name'] = data['tenant_name']
            data_to_store['display_name'] = cat_display_name
            data_to_store['system_key'] = supplier_category.name
            data_to_store['has_products'] = True
            data_to_store['has_manifest'] = True
            data_to_store['additional'] = {}
            supplier_category = SupplierCategory(**data_to_store).save()
            boops = SupplierBoops.objects(tenant_id=supplier_id, system_key=slug).first()
            boops['tenant_id'] = data['tenant_id']
            boops['tenant_name'] = data['tenant_name']
            boops['display_name'] = cat_display_name
            boops['system_key'] = supplier_category.name
            boops['ref_id'] = supplier_id
            boops['ref_boops_id'] = boops.id
            del boops.id
            boops['ref_boops_name'] = boops.name
            boops['supplier_category'] = supplier_category.id

            # TODO  must check Option and Boxs
            for box in boops.boops:
                supplier_box = SupplierBox.objects(tenant_id=supplier_id, slug=box['slug']).first()
                box_display_name = []
                for lang in data['lang']:
                    box_display_name.append({
                        'iso': lang,
                        'display_name': box['name'],
                    })
                box['ref_box'] = supplier_box.to_mongo()['_id'] if supplier_box else "BoxNotFound"
                box['display_name'] = box_display_name
                box['system_key'] = box['name']
                for option in box['ops']:
                    supplier_op = SupplierOption.objects(tenant_id=supplier_id, slug=option['slug']).first()
                    opt_display_name = []

                    for lang in data['lang']:
                        opt_display_name.append({
                            'iso': lang,
                            'display_name': option['name'],
                        })
                    option['ref_option'] = supplier_op.to_mongo()['_id'] if supplier_op else "OptionNotFound"
                    option['display_name'] = opt_display_name
                    option['system_key'] = option['name']
                box['ops'] = sorted(box["ops"], key=lambda x: x['slug'], reverse=False)
            supplier_boops = SupplierBoops.objects(tenant_id=data['tenant_id'], slug=slug).first()
            if supplier_boops:
                supplier_boops.modify(**boops)
            else:
                supplier_boops = SupplierBoops(**boops.to_mongo().to_dict()).save()

            sup_bo = SupplierBoops.objects(tenant_id=data['tenant_id'], slug=slug).first()
            print(sup_bo)

            return jsonify(sup_bo)
