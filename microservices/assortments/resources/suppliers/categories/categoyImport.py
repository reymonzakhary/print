from flask import Response, request, jsonify
from flask.json import load
from models.supplierProduct import SupplierProduct
from models.supplierProductPrice import SupplierProductPrice
from models.supplierCategory import SupplierCategory
from models.supplierBox import SupplierBox
from models.supplierOption import SupplierOption
from migrations.displayName import display_name
from models.matchedCategory import MatchedCategory
from models.supplierBoops import SupplierBoops
from models.unmatchedCategory import UnmatchedCategory
from models.categoryBoxOption import CategoryBoxOption
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource, fields, marshal
from bson.json_util import dumps
from bson.objectid import ObjectId
from models.box import Box
from models.option import Option
import json
import datetime
import math
import requests


##############################
#   handel index and store  #
#############################
class SupplierCategoyImportApi(Resource):
    _clearList = [
        'category_name',
        'category_display_name',
        'category_slug',
        'Unnamed: 0',
        'printing_method',
        'Delivery Type',
        'dlv_type',
        'dlv_days',
        'qty',
        'price',
        'shareable',
        'published',
        'incremental_by',
        'from',
        'to',
        'iso',
        'Printing Process',
    ]

    def post(self, supplier_id, slug):
        data = request.get_json()
        products = data['data']
        self._products = data['data']

        tenant_name = data['tenant_name']
        self._tenant_name = data['tenant_name']
        self._host_id = data['host_id']

        supplierCategory = SupplierCategory.objects(tenant_id=supplier_id, slug=slug).first()
        if not supplierCategory:
            return {
                       "data": None,
                       "message": "Category is not exists.",
                       "status": 422
                   }, 422
        boops = SupplierBoops.objects(tenant_id=supplier_id, supplier_category=supplierCategory).first()
        # loop on products
        currentBoops = []
        for product in products:
            productExists = self.productFilter(supplier_id, slug, product)
            boopsProduct = product.copy()
            if not boops or not boops['boops']:
                self.generateBoops(currentBoops, boopsProduct)
            if not productExists:
                productExists = self.generateProduct(productExists, tenant_name, supplier_id, supplierCategory,
                                                     boopsProduct)
                product_id = productExists
            else:
                product_id = productExists['_id']["$oid"]
                # create compoination
            if product['qty'] and product['price'] and product['dlv_type'] and product['dlv_days']:
                productPrice = self.format_to_large_integer(product['price'])
                ppp = round(productPrice / product['qty'], 2)
                priceToSave = {
                    "supplier_id": supplier_id,
                    "supplier_name": self._tenant_name,
                    "host_id": self._host_id,
                    "supplier_product": product_id,
                    "tables": {
                        "pm": product['printing_method'],
                        "dlv": {"title": product['dlv_type'], "days": product['dlv_days']},
                        "qty": product['qty'],
                        "p": productPrice,
                        "ppp": ppp,
                    }
                }
                # return priceToSave
                priceExist = SupplierProductPrice.objects(
                    supplier_id=supplier_id,
                    supplier_product=product_id
                ).aggregate({
                    "$match": {
                        "tables.pm": product['printing_method'],
                        "tables.dlv.title": product['dlv_type'],
                        "tables.dlv.days": product['dlv_days'],
                        "tables.qty": product['qty']
                    }
                })

                priceExist = list(priceExist)
                if len(priceExist):
                    priceExist[0].update(**priceToSave)
                else:
                    # store product prices
                    SupplierProductPrice(**priceToSave).save()

        supplierCategory.modify(**{
            "has_products": True,
            "has_manifest": True,
        })
        data = {
            "tenant_name": data['tenant_name'],
            "boops": currentBoops
        }

        if not boops:
            # Create boops

            url = f'http://assortments:5000/suppliers/{supplier_id}/categories/{slug}/boops'
            header = {"Content-type": "application/json"}
            res = requests.post(url, json=data, headers=header)
        elif not boops['boops']:
            # update boops
            url = f'http://assortments:5000/suppliers/{supplier_id}/categories/{slug}/boops'
            header = {"Content-type": "application/json"}
            res = requests.put(url, json=data, headers=header)
        return {"message": "product prices saved with success", "status": 201}, 201

    def format_to_large_integer(self, price_to_format):
        return price_to_format * 100000

    def generateProduct(self, productExists, tenant_name, supplier_id, supplierCategory, product):
        productObj = {
            "tenant_name": tenant_name,
            "tenant_id": supplier_id,
            "host_id": self._host_id,
            "category_name": supplierCategory['name'],
            "category_display_name": supplierCategory['display_name'],
            "category_slug": supplierCategory['slug'],
            "supplier_category": supplierCategory,
            "linked": supplierCategory['linked'],
            "shareable": False,
            "published": False,
            "object": [],
        }
        for item in self._clearList:
            if item in product: del product[item]
        for att in product:
            box = Box.objects(slug=slugify(att, to_lower=True)).first()
            option = Option.objects(slug=slugify(product[att], to_lower=True)).first()

            supplier_box = SupplierBox.objects(slug=slugify(att, to_lower=True)).first()
            supplier_option = SupplierOption.objects(slug=slugify(product[att], to_lower=True)).first()
            obj = {
                "key_link": ObjectId(box.id) if box else "",
                "key": slugify(att, to_lower=True),
                "display_key": att,
                "box_id": ObjectId(supplier_box.id) if supplier_box else "",
                "value_link": ObjectId(option.id) if option else "",
                "value": slugify(product[att], to_lower=True),
                "display_value": product[att],
                "option_id": ObjectId(supplier_option.id) if supplier_option else ""
            }
            productObj["object"].append(obj)

        productExists = SupplierProduct(**productObj).save()
        return productExists

    def generateBoops(self, currentBoops, product):
        # boops empty create all boxes
        if not currentBoops:
            # product fillteration
            for item in self._clearList:
                if item in product: del product[item]

            for att in product:
                display_key = att
                key = att
                boxObj = {
                    "sort": 0,
                    "name": key,
                    "display_name": display_name({'name': display_key}),
                    "system_key": key,
                    "slug": slugify(display_key, to_lower=True),
                    "description": "",
                    "ref_box": "",
                    "appendage": False,
                    "calculation_type": '',
                    "media": [],
                    "input_type": None,
                    "published": False,
                    "unit": "",
                    "maximum": 0,
                    "sqm": "",
                    "minimum": 0,
                    "incremental_by": 0,
                    "information": "",
                    "ops": []
                }
                currentBoops.append(boxObj)

        for boops in currentBoops:
            optionObj = {
                "ref_option": None,
                "name": product[boops['name']],
                "display_name": display_name({"name": product[boops['name']]}),
                "system_key": product[boops['name']],
                "slug": slugify(product[boops['name']], to_lower=True),
                "description": '',
                "media": [],
                "dimension": '',
                "dynamic": False,
                "unit": '',
                "width": 0,
                "maximum_width": 0,
                "minimum_width": 0,
                "height": 0,
                "maximum_height": 0,
                "minimum_height": 0,
                "length": 0,
                "maximum_length": 0,
                "minimum_length": 0,
                "start_cost": 0,
                "rpm": 0,
                "input_type": '',
                "input_type": "",
                "published": False,
                "excludes": []
            }
            if not boops['ops']:
                boops['ops'].append(optionObj)
            else:
                optionExists = True
                for ops in boops['ops']:
                    if ops['display_name'] == optionObj['display_name']:
                        optionExists = False
                if optionExists:
                    boops['ops'].append(optionObj)

    def productFilter(self, supplier, category, product):
        data = product
        p = [
            {
                "$match": {
                    "tenant_id": supplier,
                    "category_slug": category,
                    "$and": []
                }
            },
        ]
        if len(data):
            for att in data:
                if (att not in self._clearList):
                    p[0]['$match']['$and'].append({'object.key': slugify(att, to_lower=True), 'object.value': slugify(data[att], to_lower=True)})
        productList = SupplierProduct.objects.aggregate(p)
        productList = json.loads(dumps(productList))
        if productList:
            return productList[0]
        else:
            return []
        # for product in productList:
        #     return product
        # if productList:
        #     res = productList[0].id
        # else:
        #     res = 0
        # return res
