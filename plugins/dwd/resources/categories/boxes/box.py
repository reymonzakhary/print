from flask import Response, request, jsonify
from models.category import Category
from models.supplierCategory import SupplierCategory
from models.supplierBox import SupplierBox
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

class ImportCategoryAttributes(Resource):
    def get(self, tenant, slug):
        category = SupplierCategory.objects(tenant_id=tenant, slug=slug).first()

        # create categories to the assortments api
        proxy = requests.get(f"https://api.printdeal.com/api/products/{category.sku}/attributes",
                             data={}, headers={
                "API-Secret": "Mi4hQt31Pzbab6+aiBJQUFCg7Mo3Tp/AhY4tcohVVIcqcxL7",
                "User-ID": "0ac967eb-1726-4f4d-9cab-d7be7183a78d",
                "accept": "application/vnd.printdeal-api.v2"
            })

        boops = []
        boxes = []
        options = []
        res = {}
        for box in proxy.json():
            if SupplierBox.objects(slug=slugify(box, to_lower=True)).first():
                pass
            else:
                attribute = {}
                for box in proxy.json():
                    boxes.append({"name":box})
                    for option in proxy.json()[box]:
                        if isinstance(option, str):
                            options.append({"name":option})
        url = 'http://assortments:5000/similarity/boxes'
        obj = {'tenant': tenant, 'tenant_name': 'dwd', 'boxes': boxes}

        header = {"Content-type": "application/json"}
        res = requests.post(url, json=obj, headers=header)

        optionUrl = 'http://assortments:5000/similarity/options'
        optionObj = {'tenant': tenant, 'tenant_name': 'dwd', 'options': options}

        header = {"Content-type": "application/json"}
        optionRes = requests.post(optionUrl, json=optionObj, headers=header)
        return {'similarityOptionResult':optionRes.text,'similarityBoxResult':res.text}
                # create a box with similarity to about 80 %
                # add the response to the boops

                ## create a tmp boops


                # return boops
                # return 'box not exists'

        # return jsonify({
        #     "data": proxy.json()
        # })
