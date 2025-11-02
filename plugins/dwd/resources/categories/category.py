from flask import Response, request, jsonify
from models.category import Category
from models.supplierCategory import SupplierCategory
from models.matchedCategory import MatchedCategory
from models.unmatchedCategory import UnmatchedCategory
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource, fields, marshal
from bson.json_util import dumps
import requests
import json
import datetime
import math


# ######################################### #
#              DWD class                    #
# ######################################### #

class ImportCategories(Resource):

    def get(self, tenant):
        categories = SupplierCategory.objects(tenant_id=tenant).only(*['name', 'sku']).all()

        return jsonify(categories)

    def post(self, tenant):
        data = request.get_json()
        # 		data = request.form.to_dict(flat=True)
        # create categories to the assortments api

        proxy = requests.get("https://api.printdeal.com/api/products/categories",
                             data={}, headers={
                                 "API-Secret": "Mi4hQt31Pzbab6+aiBJQUFCg7Mo3Tp/AhY4tcohVVIcqcxL7",
                                 "User-ID": "0ac967eb-1726-4f4d-9cab-d7be7183a78d",
                                 "accept": "application/vnd.printdeal-api.v2"
                             })
        tenant_id = tenant
        tenant_name = data['tenant_name']
        categories = []
        ExistCategories = []
        obj = {}

        for cat in proxy.json():
            msg = 'Similarity Done'
            if SupplierCategory.objects(name=cat["name"], tenant_id=tenant_id).count() == 0:
                if MatchedCategory.objects(name=cat["name"], tenant_id=tenant_id).count() == 0:
                    if UnmatchedCategory.objects(name=cat["name"], tenant_id=tenant_id).count() == 0:
                        cat['shareable'] = True
                        cat['price_build'] = {
                            "collection": True,
                            "semi_calculation": False,
                            "full_calculation": False,
                            "external_calculation": False
                        }
                        categories.append(cat)
                    else:
                        msg = 'Alrady Exist In Un-Matched Category'
                else:
                    msg = 'Alrady Exist In Matched Category'
            else:
                msg = 'Alrady Exist In Supplier Category'

            ExistCategories.append({"name": cat["name"], "msg": msg})

        if categories:
            url = 'http://assortments:5000/similarity/categories'
            obj = {'tenant': tenant_id, 'tenant_name': tenant_name, 'categories': categories}

            header = {"Content-type": "application/json"}

            res = requests.post(url, json=obj, headers=header)

            return res.text

        resJson = {
            "status": 200,
            "data": ExistCategories
        }

        return jsonify(resJson)
