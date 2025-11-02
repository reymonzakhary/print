from flask import Response, request, jsonify
from models.category import Category
from models.supplierCategory import SupplierCategory
from models.supplierBoops import SupplierBoops
from models.supplierBox import SupplierBox
from models.box import Box
from models.option import Option
from models.supplierProduct import SupplierProduct
from models.supplierOption import SupplierOption
from bson.json_util import dumps
from bson import ObjectId
import json
import math
import requests

from flask_restful import Resource

########################################
############ create class ##############
########################################
from models.supplierProductPrice import SupplierProductPrice


class SupplierProductsCountApi(Resource):
    def get(self, supplier, slug):
        SupplierBoops.objects(tenant_id=supplier, slug=slug).update(generated=True)
        return {"data": SupplierProduct.objects(tenant_id=supplier, category_slug=slug).count(), "status": 200}, 200


class SupplierProductsApi(Resource):
    def get(self, supplier, slug):
        page = 1 if request.args.get('page') is None or request.args.get('page') == "" else int(
            request.args.get('page'))
        per_page = 10 if request.args.get('per_page') is None or request.args.get('per_page') == "" or request.args.get(
            'per_page') == "undefined" else int(
            request.args.get('per_page'))
        skip = 0 if page == 1 else (per_page * page) - per_page
        filter = "" if request.args.get('filter') is None or request.args.get('filter') == "" else request.args.get(
            'filter')
        sortBy = "name" if request.args.get('sort_by') is None or request.args.get(
            'sort_by') == "" else request.args.get('sort_by')
        sortDir = "" if request.args.get('sort_dir') is None or request.args.get('sort_dir') == "" or request.args.get(
            'sort_dir') == "asc" else "-"

        # return jsonify(SupplierOption.objects(tenant_id=supplier))
        supplier_category = SupplierCategory.objects(tenant_id=supplier, slug=slug).first()
        # return len(supplier_category.ref_category_id)
        if supplier_category.ref_category_id:
            products = SupplierProduct.objects(tenant_id=supplier_category.ref_id,
                                               supplier_category=supplier_category.ref_category_id).order_by(
                sortDir + sortBy)
        else:
            products = SupplierProduct.objects(tenant_id=supplier, category_slug=slug).order_by(sortDir + sortBy)
        products = products.aggregate(
            [
                {
                    "$facet": {
                        "data": [

                            {"$skip": skip},
                            {"$limit": per_page},

                        ],
                        "count": [{
                            "$count": "count"
                        }]
                    },
                }
            ])
        products = json.loads(dumps(*products))
        items = products['data']
        if len(products['count']) == 0:
            count = 0
        else:
            count = json.loads(dumps(*products['count']))['count']

        last_page = math.ceil(count / per_page)
        next_page = None if last_page <= page else page + 1
        first_page = 1

        return {
                   "pagination": {
                       "total": count,
                       "per_page": per_page,
                       "current_page": page,
                       "last_page": math.ceil(count / per_page),
                       "first_page_url": "/?page=" + str(first_page),
                       "last_page_url": "/?page=" + str(last_page),
                       "next_page_url": "/?page=" + str(next_page) if next_page else None,
                       "prev_page_url": "/?page=" + str(page - 1) if page > 1 else None,
                       "path": '/',
                       "from": skip,
                       "to": skip + per_page,
                   },
                   "data": items,
               }, 200

    def post(self, supplier, slug):
        data = request.form.to_dict(flat=True)
        supplier = request.args.get('supplier')
        dlv = request.args.get('dlv')
        qty = request.args.get('qty')
        page = 1
        if request.args.get('page'):
            page = request.args.get('page')
        sortdir = request.args.get('sortdir')
        sortby = request.args.get('sortby')
        perPage = 10
        if request.args.get('perPage'):
            perPage = request.args.get('perPage')
        newPerPage = int(perPage) * int(page)
        newSkip = int(newPerPage) - int(perPage)
        p = []
        deliveryType = []
        category = SupplierCategory.objects.first_or_404({"slug": slug})
        p.append({"$match": {"category_name": category['name']}})
        if len(data):
            for att in data:
                if (att == "Delivery Type"):
                    deliveryType = data[att]
                else:
                    p.append(
                        {"$match": {'object.display_key': att, 'object.display_value': {'$in': data[att].split(",")}}})
        supplierIds = []
        dlvs = []
        pricesMatches = [{"$eq": ["$supplier_product", "$$id"]}]
        if supplier:
            supplierIds = supplier.split(",")
            pricesMatches.append({"$in": ["$supplier_id", supplierIds]})
        if deliveryType:
            deliveryTypes = deliveryType.split(",")
            pricesMatches.append({"$in": ["$tables.dlv.type", deliveryTypes]})
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
        if deliveryType:
            deliveryType = deliveryType.split(",")
            pricesMatches.append({"$in": ["$tables.dlv.title", deliveryType]})

            p.append({"$match": {"tables.dlv.title": dlv}})
        p.append({
            "$lookup": {
                "from": "supplier_product_prices",  # Tag collection database name
                "let": {"id": "$_id"},
                # "foreignField": "product",  # Primary key of the Tag collection
                # "localField": "_id",  # Reference field
                "pipeline": [
                    {"$match":
                         {"$expr":
                              {"$and": pricesMatches}
                          }
                     }
                ],

                "as": "prices",

            },
        })

        # p.append({"$unwind": "$prices"})
        # return supplier
        # if supplier:
        #     p.append({"$match": {"prices.supplier_id": {"$in" : supplier.split(",")}}})
        # p.append({
        #     "$sort": sort
        # })
        countAgr = p[:-1]
        countAgr.append({
            "$count": "total"
        })
        p.append({
            "$limit": newPerPage
        })
        p.append({
            "$skip": newSkip
        })

        productList = SupplierProduct.objects.aggregate(
            p
        )
        count = SupplierProduct.objects.aggregate(
            countAgr
        )
        totalData = json.loads(dumps(count))
        # return totalData
        total = 0
        if totalData:
            total = totalData[0]['total']
        productList = json.loads(dumps(productList))
        resJson = {
            "data": productList,
            "page": page,
            "per_page": perPage,
            "total": total,
            "lastPage": math.ceil(int(total) / int(perPage)),
            "status": 200,
        }
        return resJson


class GenerateSupplierProductsApi(Resource):
    def post(self, supplier, slug):
        body = request.get_json(force=True)
        products = body['products']
        category = SupplierCategory.objects(tenant_id=supplier, id=ObjectId(products[0]['supplier_category'])).first()
        category.modify(**{
            "has_products": True,
            "has_manifest": True
        })
        for product in products:
            product['host_id'] = body['host_id']
            for p in product:
                product['linked'] = ObjectId(product['linked'])
                for i, o in enumerate(product['object']):
                    product['object'][i]['key_link'] = None if o['key_link'] == '' else ObjectId(o['key_link'])
                    product['object'][i]['value_link'] = None if o['value_link'] == '' else ObjectId(o['value_link'])
                    product['object'][i]['box_id'] = ObjectId(o['box_id'])
                    product['object'][i]['option_id'] = ObjectId(o['option_id'])
            if SupplierProduct.objects(tenant_id=supplier, category_slug=slug, object=product['object']).count() == 0:
                SupplierProduct(**product).save()
        return {"message": "products generated successfully", "status": "201"}, 201


class ReGenerateProductsApi(Resource):
    def post(self, supplier, slug):
        supplier_category = SupplierCategory.objects(tenant_id=supplier, slug=slug).first()
        if supplier_category:
            products = SupplierProduct.objects(category_slug=slug, tenant_id=supplier)
            for product in products:
                SupplierProductPrice.objects(supplier_id=supplier, supplier_product=product.id).delete()
                product.delete()

            category = SupplierCategory.objects(tenant_id=supplier, slug=slug).first()
            modify = {"has_products": False}
            try:
                # Defensive check: ensure category.linked exists and is valid
                if category.linked:
                    linked_id = None
                    if isinstance(category.linked, str):
                        linked_id = ObjectId(category.linked)
                    if isinstance(category.linked, Category):
                        linked_id = ObjectId(category.linked.id)
                    if linked_id and not Category.objects(id=linked_id).first():
                        modify = {"linked": None, "has_products": False}
            except:
                # If category.linked is invalid or not an ObjectId
                modify = {"linked": None, "has_products": False}

            category.modify(**modify)

            return {"message": "Products has been deleted successfully", "status": 200}, 200

        return {"message": "We could\'nt found this category", "status": 404}, 200
