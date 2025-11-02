from flask import Response, request, jsonify
from models.category import Category
from models.supplierCategory import SupplierCategory
from models.supplierBoops import SupplierBoops
from models.matchedCategory import MatchedCategory
from models.unmatchedCategory import UnmatchedCategory
from models.categoryBoxOption import CategoryBoxOption
from models.box import Box
from models.resellerCategory import ResellerCategory
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource, fields, marshal
from bson.json_util import dumps
import json
import datetime
import math


class ResellerCategoriesApi(Resource):

    def get(self, tenant_id):
        reseller = ResellerCategory.objects(tenant_id=tenant_id).aggregate([
            {
                "$lookup": {
                    "from": "supplier_categories",
                    "localField": "supplier_category",
                    "foreignField": "_id",
                    "as": "category",
                },

            }
        ])
        categories = []

        for category in reseller:
            categories.append({
                "_id": category['category'][0]['_id'],
                "sort": category['sort'],
                "tenant_id": category['tenant_id'],
                "tenant_name": category['tenant_name'],
                "supplier_id": category['supplier_id'],
                "supplier_name": category['category'][0]['tenant_name'],
                "name": category['category'][0]['name'],
                "slug": category['category'][0]['slug'],
                "published": category['published'],
            })
        return json.loads(dumps(categories))


class ResellerCategoryApi(Resource):

    def get(self, tenant_id, supplier_category_id):
        resellerCategory = ResellerCategory.objects(supplier_category=supplier_category_id,tenant_id=tenant_id).first()
        if resellerCategory:
            reseller = SupplierBoops.objects(supplier_category=resellerCategory.supplier_category,tenant_id = resellerCategory.supplier_id).only('boops','category_name','supplier_category','tenant_id','tenant_name').exclude('id').first()
            return jsonify(reseller)
        else:
            return "no reseller category"
