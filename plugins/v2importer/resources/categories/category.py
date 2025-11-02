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
#              v2Importer class             #
# ######################################### #

class ImportCategories(Resource):

    def get(self, tenant):
        categories = SupplierCategory.objects(tenant_id=tenant).only(*['name', 'sku']).all()

        return jsonify(categories)

    def post(self, tenant):
        data = request.get_json()
        # return data
        # requets = request.form.to_dict(flat=True)
        # create categories to the assortments api

        excel = requests.post("http://filemanager:5000/excel/read",
                             data={
                                 "path":data['path']
                             },headers={
                                 "Content-Type": "application/x-www-form-urlencoded",
                             })
        tenant_id = tenant
        tenant_name = data['tenant_name']
        
        fileData = excel.json()
        category = {
            "name": fileData['data'][0]['CategorieNaam'],
            "description": fileData['data'][0]['CategorieMetaOmschrijving'],
            "shareable": False,
            "published": False,
            "iso":"du",
            "tenant_name":data['tenant_name']
        }
        categoryRes = requests.post(f"http://assortments:5000/suppliers/{tenant}/categories",
                             data=category,
                             headers={
                                 "Content-Type": "application/x-www-form-urlencoded",
                                 "accept": "application/json",
                             })
        return categoryRes.json()
    
        # proxy.json() ========= 
        # [
        #     {
        #         "name": "Heavy-duty Boxes",
        #         "sku": "76982bea-e0dc-40f4-b09f-6bd7ee5e9005",
        #         "combinationsModifiedAt": "2021-04-26 10:09:02"
        #     },
        # ]
        for cat in proxy.json():
            msg = 'Similarity Done'
            if SupplierCategory.objects(name=cat["name"], tenant_id=tenant_id).count() == 0:
                if MatchedCategory.objects(name=cat["name"], tenant_id=tenant_id).count() == 0:
                    if UnmatchedCategory.objects(name=cat["name"], tenant_id=tenant_id).count() == 0:
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
            obj = {'tenant': tenant_id, 'tenant_name': tenant_name, 'categories': categories, 'iso': 'EN'}

            header = {"Content-type": "application/json"}
            # return json.dumps(categories)
            res = requests.post(url, json=obj, headers=header)

            return res.text

        resJson = {
            "status": 200,
            "data": ExistCategories
        }

        return jsonify(resJson)
