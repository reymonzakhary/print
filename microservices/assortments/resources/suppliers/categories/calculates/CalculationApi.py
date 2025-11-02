from flask_restful import Resource, fields, marshal
from flask import Response, request, jsonify

from models.supplierCategory import SupplierCategory
from models.supplierProduct import SupplierProduct
from models.supplierBoops import SupplierBoops
from models.supplierOption import SupplierOption

from resources.suppliers.categories.calculates.collectionCalculationApi import collectionCalculation
from resources.suppliers.categories.calculates.fullCalculationApi import fullCalculation
from resources.suppliers.categories.calculates.semiCalculationApi import semiCalculation
from bson.json_util import dumps
import json


class CalculationApi(Resource):
    # post request

    def post(self, supplier, category):
        data = request.get_json()
        # get category
        #   if category not exist return error
        supplierCategory = SupplierCategory.objects(slug=category, tenant_id=supplier).first()
        # get the supplier id that he had supplier products 
        supplier_id = supplierCategory.ref_id if supplierCategory.ref_id else supplier

        if not supplierCategory:
            return {
                       "message": "Category Not Exist",
                       "status": 404,
                   }, 200
        # check if Category published
        if not supplierCategory.published:
            return {
                       "message": "Category is not  published",
                       "status": 404,
                   }, 200
        # get  Product computation
        p = []
        boxesFillter = self.boxFillter(supplierCategory, data['products'])
        mainBoxes = boxesFillter['mainBoxes']
        addonsBoxes = boxesFillter['addonsBoxes']
        addonsOptions = SupplierOption.objects(slug__in=addonsBoxes.values()).aggregate([
            # { "$unwind": "$options" },
            {
                "$replaceRoot": {
                    "newRoot": {
                        "options": ["$$ROOT"]
                    }
                }
            }
        ])
        # return dumps(addonsOptions)
        match = []

        if len(mainBoxes):
            match.append({
                "$match": {
                    "$and": []
                }
            })

            for att in mainBoxes:
                match[0]['$match']['$and'].append({'object.key': att, 'object.value': mainBoxes[att]})

        match.append({
            "$lookup": {
                "from": "supplier_options",  # Tag collection database name
                "foreignField": "slug",  # Primary key of the Tag collection
                "localField": "object.value",  # Reference field
                "as": "options",
            },
        })
        match.append({
            "$lookup": {
                "from": "supplier_product_prices",  # Tag collection database name
                "foreignField": "supplier_product",  # Primary key of the Tag collection
                "localField": "_id",  # Reference field
                "as": "prices",
            },
        })
        match.append({
            "$project": {
                "options": 1,
                "prices": 1,
                "count": {"$eq": [{"$size": "$object"}, len(data['products'])]},
                "_id": 0
            }
        })
        # return match
        supplierProducts = SupplierProduct.objects(category_slug=category, tenant_id=supplier_id).aggregate(match)
        #   if Product not exist return error
        if not supplierProducts:
            return {
                       "message": "Products is not  Exist",
                       "status": 404,
                   }, 200
        # if category calc method and make calculation
        # check Method type
        #     -- collection | full_calculation | semi_calculation
        price_build_map = ['collection', 'full_calculation', 'semi_calculation']
        for price_build, value in supplierCategory.price_build.items():
            if not price_build in price_build_map:
                return {
                           "message": "Can't handle this method",
                           "status": 404,
                       }, 200
            price = 0
            if value:
                # return json.loads(dumps(supplierProducts))
                price = self.calculationMethod(price_build).netPrice(supplierCategory,
                                                                     json.loads(dumps(supplierProducts)),
                                                                     data['quantity'],
                                                                     addonsOptions)
                break
        return price

    def calculationMethod(self, method):
        # return instance for Calculation method
        if (method == "collection"):
            return collectionCalculation()
        if (method == "full_calculation"):
            return fullCalculation()
        if (method == "semi_calculation"):
            return semiCalculation()

    def boxFillter(self, supplierCategory, boxes):
        filterResult = {
            "mainBoxes": {},
            "addonsBoxes": {}
        }
        supplierBoops = SupplierBoops.objects(supplier_category=supplierCategory.id).first()
        for box in boxes:
            for boops in supplierBoops.boops:
                if boops['slug'] == box:
                    if boops["ref_box"]:
                        filterResult['mainBoxes'][box] = boxes[box]
                    else:
                        filterResult['addonsBoxes'][box] = boxes[box]
        return filterResult
