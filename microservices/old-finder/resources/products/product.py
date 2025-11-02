from flask import Response, request, jsonify
from models.supplierCategory import SupplierCategory
from models.category import Category
from models.supplierProduct import SupplierProduct
from models.supplierProductPrice import SupplierProductPrice

from flask_restful import Resource, fields, marshal

from bson.json_util import dumps
import json
import math
from bson import ObjectId


##############################
#   handel index and store  #
#############################
class ProductFilterApi(Resource):
    def post(self, slug):
        # Structure line
        ## Check
        # data = request.form.to_dict(flat=True)
        data = request.get_json()
        supplierIds = data['suppliers']
        # return supplierIds
        fillter = data['product'] if "product" in data else []

        dlv = data.get('dlv')
        qty = data.get('qty')
        page = 1
        if request.args.get('page') and request.args.get('page') != 'undefined':
            page = request.args.get('page')
        sortdir = data.get('sortdir')
        sortby = data.get('sortby')
        per_page = 10
        if request.args.get('perPage'):
            per_page = request.args.get('perPage')
        newPerPage = int(per_page) * int(page)
        newSkip = int(newPerPage) - int(per_page)
        product = []
        delivery_type = []
        # category = Category.objects(slug=slug, published=True).first()
        category = Category.objects.aggregate([
            {
                "$match": {
                    "$and": [
                        {"slug": slug},
                        {"published": True}
                    ]
                }
            },
            {
                "$lookup": {
                    # "localField": "_id",
                    # "foreignField": "linked",
                    "from": "supplier_categories",
                    "let": {"id": "$_id"},
                    "pipeline": [
                        {"$match":
                            {"$expr":
                                {"$and":
                                    [
                                        {"$in": ["$tenant_id", supplierIds]},
                                        {"$eq": ["$linked", "$$id"]},
                                        {"$eq": ["$published", True]},
                                        {"$eq": ["$shareable", True]},
                                    ]
                                }
                            }
                        },
                        {
                            "$project": {
                                "_id": 1
                            }
                        }
                    ],
                    "as": "category_ids"
                },
            },
            {
                "$project": {
                    "category_ids._id": 1
                }
            }
        ])

        categories = json.loads(dumps(*category))
        category_ids = []
        for id in categories['category_ids']:
            category_ids.append(ObjectId(id['_id']['$oid']))

        product.append(
            {
                "$match": {
                    "$and": [{"supplier_category": {"$in": category_ids}}]
                }
            }
        )

        if len(fillter):
            for att in fillter:
                if att == "Delivery Type":
                    delivery_type = fillter[att]
                else:
                    product[0]['$match']['$and'].append(
                        {
                            'object.key_link': ObjectId(att),
                            'object.value_link': ObjectId(fillter[att])
                        }
                    )
        dlvs = []
        pricesMatches = []
        pricesMatches = [{"$eq": ["$supplier_product", "$$id"]}]
        if supplierIds:
            pricesMatches.append({"supplier_id": ["$in", supplierIds]})
            # pricesMatches.append({"$in": ["tenant_id", supplierIds]})
            product.append({
                "$match": {
                    "tenant_id": {
                        "$in": supplierIds
                    }
                }
            })

        if dlv:
            dlvs = dlv.split(',')
            dlvl = len(dlvs)
            if dlvl > 1:
                pricesMatches.append(
                    {
                        "$lte": [
                            "$tables.dlv.days", int(dlvs[1])
                        ]
                    }

                )
            pricesMatches.append(
                {
                    "$gte": [
                        "$tables.dlv.days", int(dlvs[0])
                    ],
                }
            )
        if qty:
            qtys = qty.split(',')
            qtyl = len(qtys)
            if qtyl > 1:
                pricesMatches.append(
                    {
                        "$lte": [
                            "$tables.qty", int(qtys[1])
                        ]
                    }

                )
            pricesMatches.append(
                {
                    "$gte": [
                        "$tables.qty", int(qtys[0])
                    ],
                }
            )

            # pricesMatches.append(
            #     {
            #         "tables.qty": {
            #             "$gte": int(qtys[0]),
            #             "$lte": int(qtys[1])
            #         }
            #
            #     }
            # )

        # pricesMatches.append({"$eq": ["$supplier_product", "$$id"]})
        # product.append({"$count": "count"})
        # pricesMatches.append({"$eq": ["$supplier_product", "$$id"]})

        # product.append({"$match":{"tables.dlv.title":dlv}})
        # return  pricesMatches10

        product.append({
            "$lookup": {
                "from": "supplier_product_prices",  # Tag collection database name
                "let": {"id": "$_id"},
                # "foreignField": "supplier_product",  # Primary key of the Tag collection
                # "localField": "_id",  # Reference field
                "pipeline": [
                    {
                        "$match":
                            {
                                "$expr":
                                    {
                                        "$and": pricesMatches
                                    }
                            }
                    }
                ],

                "as": "prices",

            },
        })

        # product.append({
        #     "$match": {
        #         "prices": {"$ne": "Null"}
        #     }
        # })
        # return json.loads(dumps(product))
        # return supplier
        # if supplier:
        #     product.append({"$match": {"prices.supplier_id": {"$in" : supplier.split(",")}}})
        # product.append({
        #     "$sort": sort
        # })

        countAgr = product[:-2]
        # return json.loads(dumps(countAgr))
        # countAgr.append({
        #     "$count": "total"
        # })

        # product.append({
        #     "$unwind":
        #         {
        #             "path": '$prices',
        #             "preserveNullAndEmptyArrays": False,
        #         }
        # })
        # return p
        # product.append({
        #     "$limit": newPerPage
        # })
        # product.append({
        #     "$skip": newSkip
        # })
        # product.append({
        #     "$project": {
        #         "_id": 1
        #     }
        # })
        product.append({
            "$facet": {
                "count": [{"$count": "total"}],
                "data": [{"$skip": newSkip}, {"$limit": newPerPage}]
            }
        })
        # return json.loads(dumps(product))
        # return json.loads(dumps(product))
        productList = SupplierProduct.objects.aggregate(product)
        totalData = json.loads(dumps(*productList))
        # return pricesMatches
        # for prd_id in totalData['data']:
        #     product_id = ObjectId(prd_id['_id']['$oid'])
        #     prd_id['prices'] = SupplierProductPrice.objects.aggregate([
        #         {
        #             "$match": {
        #                 "$and": pricesMatches,
        #
        #             }
        #         },
        #         {
        #             "$match": pricesMatches
        #         },
        #     ])

        # count = su.objects.aggregate(
        #     countAgr
        # )
        # totalData = json.loads(dumps(count))
        # return totalData
        total = 0
        data = []
        if totalData:
            if len(totalData['count']):
                total = totalData['count'][0]['total']
            if len(totalData['data']):
                data = json.loads(dumps(totalData))['data']

        resJson = {
            "data": data,
            "page": page,
            "per_page": per_page,
            "total": total,
            "pagination": {
                "first_page_url": "",
                "last_page_url": "",
                "prev_page_url": "",
                "next_page_url": "",
                "current_page": page,
                "from": newSkip,
                "to": newPerPage,
                "page": page,
                "per_page": per_page,
                "total": total,
                "lastPage": math.ceil(int(total) / int(per_page)),
                "last_page": math.ceil(int(total) / int(per_page)),
            },
            "lastPage": math.ceil(int(total) / int(per_page)),
            "status": 200,
        }
        return resJson
