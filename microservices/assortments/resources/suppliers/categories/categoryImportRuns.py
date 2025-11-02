from flask import Response, request, jsonify
from flask.json import load
from models.supplierProduct import SupplierProduct
from models.supplierProductPrice import SupplierProductPrice
from models.supplierCategory import SupplierCategory
from models.supplierBox import SupplierBox
from models.supplierOption import SupplierOption
from models.matchedCategory import MatchedCategory
from models.supplierBoops import SupplierBoops
from models.unmatchedCategory import UnmatchedCategory
from models.categoryBoxOption import CategoryBoxOption
from migrations.displayName import display_name
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource, fields, marshal
from bson.json_util import dumps
from bson.objectid import ObjectId
from models.box import Box
from models.option import Option
from time import time
import json
import datetime

import math
import requests


##############################
#   handel index and store  #
#############################
class SupplierCategoyImportRunsApi(Resource):
    _productIds = []
    _time = 0
    _supplier_id = ""
    _calc_method_allowable = ['fixed-price', 'sliding-scale']
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
        self._supplier_id = supplier_id
        supplierCategory = SupplierCategory.objects(tenant_id=supplier_id, slug=slug).first()
        if not supplierCategory:
            return {
                       "data": None,
                       "message": "Category is not exists.",
                       "status": 422
                   }, 422
        if ("calculation_method" not in supplierCategory) or supplierCategory['calculation_method'] == []:
            supplierCategory.modify(**{
                "calculation_method": [
                    {
                        "name": 'Fixed Price',
                        "slug": 'fixed-price',
                        "active": True
                    },
                    {
                        "name": 'Sliding Scale',
                        "slug": 'sliding-scale',
                        "active": False
                    }
                ]
            })

        boops = SupplierBoops.objects(tenant_id=supplier_id, supplier_category=supplierCategory).first()

        if boops:
            boops.delete()
        boops = []
        # loop on products
        self._time = str(time())
        currentBoops = []
        for product in products:

            productExists = self.productFilter(supplier_id, slug, product)
            if 'create_at' in productExists and productExists['create_at'] != self._time:
                self.delete_price(supplier_id, [productExists['_id']['$oid']])
                SupplierProduct.objects(tenant_id=supplier_id, id=productExists['_id']['$oid']).delete()
                productExists = []

            boopsProduct = product.copy()
            if not boops or not boops['boops']:
                self.generateBoops(currentBoops, boopsProduct)
            if not productExists:
                productExists = self.generateProduct(productExists, tenant_name, supplier_id, supplierCategory,
                                                     boopsProduct, product)
                product_id = json.loads(dumps(productExists['id']))["$oid"]
            else:
                product_id = self.generateRuns(productExists['_id']["$oid"], product)
                # create compoination
            if product_id not in self._productIds:
                self._productIds.append(product_id)
        try:
            self.create_price(supplierCategory)
        except Exception as e:
            return {
                       "data": None,
                       "message": str(e),
                       "status": 422
                   }, 200
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

    def generateProduct(self, productExists, tenant_name, supplier_id, supplierCategory, product, data=""):
        productObj = {
            "tenant_name": tenant_name,
            "host_id": self._host_id,
            "tenant_id": supplier_id,
            "category_name": supplierCategory['name'],
            "category_display_name": supplierCategory.display_name,
            "category_slug": supplierCategory['slug'],
            "supplier_category": supplierCategory,
            "linked": supplierCategory['linked'],
            "shareable": False,
            "published": False,
            "create_at": self._time,
            "object": [],
            "runs": [{
                "from": data['from'],
                "to": data['to'],
                "pm": data['printing_method'],
                "incremental_by": data['incremental_by'],
                "delivery_days": data['dlv_days'],
                "price": data['price'],
                "dlv_type": data['dlv_type']
            }]
        }

        for item in self._clearList:
            if item in product: del product[item]
        for att in product:
            box = Box.objects(slug=slugify(att, to_lower=True)).first()
            option = Option.objects(slug=slugify(product[att], to_lower=True)).first()

            supplier_box = SupplierBox.objects(tenant_id=supplier_id, slug=slugify(att, to_lower=True)).first()
            supplier_option = SupplierOption.objects(tenant_id=supplier_id, slug=slugify(product[att], to_lower=True)).first()
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
                    if ops['name'] == optionObj['name']:
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
                    p[0]['$match']['$and'].append(
                        {'object.key': slugify(att, to_lower=True), 'object.value': slugify(data[att], to_lower=True)})

        productList = SupplierProduct.objects.aggregate(p)
        productList = json.loads(dumps(productList))
        if productList:
            return productList[0]
        else:
            return []

    def generateRuns(self, id, data):
        product = SupplierProduct.objects(id=id).first()
        runs = product['runs']
        runs.append({
            "from": data['from'],
            "to": data['to'],
            "pm": data['printing_method'],
            "incremental_by": data['incremental_by'],
            "delivery_days": data['dlv_days'],
            "price": data['price'],
            "dlv_type": data['dlv_type']
        })
        product.modify(**{
            "runs": runs
        })
        return id

    def create_price(self, category):
        for calc_method in category['calculation_method']:
            if calc_method['active'] and calc_method['slug'] in self._calc_method_allowable:
                return getattr(self, calc_method['slug'].replace('-', "_"))()

        raise Exception(f"Sorry, We can't handle this {calc_method['name']} method right now! .")

    def fixed_price(self):
        supplier_id = self._supplier_id
        last_qty = -1
        products = SupplierProduct.objects(tenant_id=supplier_id, id__in=self._productIds)
        self.delete_price(supplier_id, self._productIds)
        for product in products:
            for run in product['runs']:
                runPrice = self.format_to_large_integer(run['price'])
                priceCount = int(run['to'] / run['incremental_by'])
                qty = run['from'] - 1
                for i in range(priceCount + 1):
                    if qty == last_qty:
                        qty = qty + run['incremental_by']
                        continue
                    priceToSave = {
                        "supplier_id": supplier_id,
                        "supplier_name": self._tenant_name,
                        "host_id": self._host_id,
                        "supplier_product": product,
                        "tables": {
                            "pm": run['pm'],
                            "dlv": {"title": run['dlv_type'], "days": run['delivery_days']},
                            "qty": qty if qty != 0 else 1,
                            "p": runPrice * (qty if qty != 0 else 1),
                            "ppp": runPrice,
                        }
                    }
                    SupplierProductPrice(**priceToSave).save()
                    last_qty = qty
                    qty = qty + run['incremental_by']

    def format_to_large_integer(self, price_to_format):
        return price_to_format * 100000

    def sliding_scale(self):
        supplier_id = self._supplier_id
        last_qty = -1
        last_price = 0
        last_run_qty = 0
        products = SupplierProduct.objects(tenant_id=supplier_id, id__in=self._productIds)
        self.delete_price(supplier_id, self._productIds)
        for product in products:
            for run in product['runs']:
                runPrice = self.format_to_large_integer()
                priceCount = int(run['to'] / run['incremental_by'])
                qty = run['from'] - 1
                for i in range(priceCount + 1):
                    if qty == last_qty:
                        qty = qty + run['incremental_by']
                        continue
                    priceToSave = {
                        "supplier_id": supplier_id,
                        "supplier_product": product,
                        "supplier_name": self._tenant_name,
                        "host_id": self._host_id,
                        "tables": {
                            "pm": run['pm'],
                            "dlv": {"title": run['dlv_type'], "days": run['delivery_days']},
                            "qty": qty if qty != 0 else 1,
                            "p": last_price + runPrice * ((qty - last_run_qty) if qty != 0 else 1),
                            "ppp": runPrice,
                        }
                    }
                    SupplierProductPrice(**priceToSave).save()
                    last_qty = qty
                    qty = qty + run['incremental_by']
                last_price = priceToSave['tables']['p']
                last_run_qty = priceToSave['tables']['qty']

    def delete_price(self, supplier_id, ids):
        # Get and Delete Prices
        priceExist = SupplierProductPrice.objects(
            supplier_id=supplier_id,
            supplier_product__in=ids
        )
        for price in priceExist:
            price.delete()
