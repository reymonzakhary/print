from flask import Response, request, jsonify
from models.category import Category
from models.supplierCategory import SupplierCategory
from models.supplierBoops import SupplierBoops
from models.supplierProduct import SupplierProduct
from models.supplierProductPrice import SupplierProductPrice
from models.categoryBoxOption import CategoryBoxOption
from models.supplierOption import SupplierOption
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource, fields, marshal
from bson.json_util import dumps
import json
import datetime
import math
import requests
from bson import ObjectId


##############################
#   handle index and store  #
#############################
class SupplierCategoriesApi(Resource):
    def get(self, supplier_id):
        page = 1 if request.args.get('page') is None or request.args.get('page') == "" else int(
            request.args.get('page'))
        per_page = 10 if request.args.get('per_page') is None or request.args.get('per_page') == "" else int(
            request.args.get('per_page'))
        skip = 0 if page == 1 else (per_page * page) - per_page
        filter = "" if request.args.get('filter') is None or request.args.get('filter') == "" else request.args.get(
            'filter')
        published = "" if request.args.get('published') is None or request.args.get(
            'published') == "" else request.args.get(
            'published')
        sortBy = "name" if request.args.get('sort_by') is None or request.args.get(
            'sort_by') == "" else request.args.get('sort_by')
        sortDir = "" if request.args.get('sort_dir') is None or request.args.get('sort_dir') == "" or request.args.get(
            'sort_dir') == "asc" else "-"
        match = []
        if published:
            match.append({
                "$match": {
                    "published": True
                }
            })
        if not request.args.get('mycategory'):
            match.append({
                "$match": {
                    "shareable": True
                }
            })

        # if ids:
        #     object_ids = [ObjectId(id) for id in ids]  # Convert to ObjectId
        #     match.append({
        #         "$match": {
        #             "_id": {
        #                 "$in": object_ids
        #             }
        #         }
        #     })

        match.append({
            "$match": {
                "name": {
                    "$regex": filter,
                    "$options": 'i'  # case-insensitive
                }
            }
        })
        match.append({
            "$facet": {
                "data": [
                    {
                        "$lookup": {
                            "from": "supplier_categories",  # Tag collection database name
                            "foreignField": "category",  # Primary key of the Tag collection
                            "localField": "_id",  # Reference field
                            "as": "suppliers",
                        },
                    },
                    {
                        "$lookup": {
                            "from": "supplier_boops",  # Tag collection database name
                            "foreignField": "supplier_category",  # Primary key of the Tag collection
                            "localField": "_id",  # Reference field
                            "as": "boops",
                        },
                    },
                    {
                        "$lookup": {
                            "from": "matched_categories",  # Tag collection database name
                            "foreignField": "category",  # Primary key of the Tag collection
                            "localField": "_id",  # Reference field
                            "as": "matches",
                        }
                    },
                    {"$skip": skip},
                    {"$limit": per_page},
                ],
                "count": [{
                    "$count": "count",
                }]
            },
        })

        # return jsonify(SupplierCategory.objects(tenant_id=supplier_id))
        categories = SupplierCategory.objects(tenant_id=supplier_id).order_by(sortDir + sortBy).aggregate(match)
        categories = json.loads(dumps(*categories))
        items = categories['data']

        for x, cat in enumerate(items):
            # Ensure 'boops' key exists and is not empty
            if 'boops' not in items[x] or not isinstance(items[x]['boops'], list) or len(items[x]['boops']) == 0:
                continue

            # Iterate over nested 'boops'
            for i, item in enumerate(items[x]['boops'][0].get('boops', [])):
                # Ensure 'ops' key exists and is a list
                if 'ops' not in item or not isinstance(item['ops'], list):
                    continue

                for ox, op in enumerate(item['ops']):
                    # Safely query the option
                    option = SupplierOption.objects(tenant_id=supplier_id, id=op['id']['$oid']).first()
                    if not option:
                        continue  # Skip if option is None

                    # Update fields only if 'option' exists
                    # Access attributes directly using getattr
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['incremental_by'] = getattr(option, 'incremental_by', 0)
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['dimension'] = getattr(option, 'dimension', '2d')
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['dynamic'] = getattr(option, 'dynamic', False)
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['dynamic_keys'] = getattr(option, 'dynamic_keys', [])
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['start_on'] = getattr(option, 'start_on', 0)
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['end_on'] = getattr(option, 'end_on', 0)
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['generate'] = getattr(option, 'generate', False)
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['dynamic_type'] = getattr(option, 'dynamic_type', '')
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['unit'] = getattr(option, 'unit', 'mm')
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['width'] = getattr(option, 'width', 0)
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['maximum_width'] = getattr(option, 'maximum_width', 0)
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['minimum_width'] = getattr(option, 'minimum_width', 0)
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['height'] = getattr(option, 'height', 0)
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['maximum_height'] = getattr(option, 'maximum_height', 0)
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['minimum_height'] = getattr(option, 'minimum_height', 0)
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['length'] = getattr(option, 'length', 0)
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['maximum_length'] = getattr(option, 'maximum_length', 0)
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['minimum_length'] = getattr(option, 'minimum_length', 0)
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['start_cost'] = getattr(option, 'start_cost', 0)
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['rpm'] = getattr(option, 'rpm', 0)
                    items[x]['boops'][0]['boops'][i]['ops'][ox]['media'] = getattr(option, 'media', [])

        # Pagination calculations
        count = len(categories['data']) if 'count' in categories and len(categories['count']) > 0 else 0
        last_page = math.ceil(count / per_page)
        next_page = None if last_page <= page else page + 1
        first_page = 1

        return {
            "pagination": {
                "total": count,
                "per_page": per_page,
                "current_page": page,
                "last_page": last_page,
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

    def post(self, supplier_id):
        body = request.get_json()
        # body = request.form.to_dict(flat=True)
        # return body
        if SupplierCategory.objects(tenant_id=supplier_id, slug=slugify(body['system_key'], to_lower=True)).count() > 0:
            return {
                "data": None,
                "message": "Category already exists.",
                "status": 422
            }, 200
        else:

            cat_display_name = []
            for lang in body['lang']:
                cat_display_name.append({
                    'iso': lang,
                    'display_name': body['name'],
                })
            if ("linked" in body) and (body['linked'] is not None):
                category = Category.objects(id=body['linked']).first()
                if category:
                    data_to_store = {
                        "sort": body['sort'] if 'sort' in body else 0,
                        "tenant_id": supplier_id,
                        "tenant_name": body["tenant_name"],
                        "linked": category.id if category else "",
                        "sku": body['sku'] if 'sku' in body else "",
                        "name": category.name,
                        "system_key": body['system_key'],
                        "display_name": cat_display_name,
                        "slug": slugify(body['system_key'], to_lower=True),
                        "source_slug": body['source_slug'] if 'source_slug' in body else None,
                        "description": body['description'] if 'description' in body else "",
                        "shareable": body['shareable'] if 'shareable' in body else False,
                        "published": body['published'] if 'published' in body else False,
                        "media": body['media'] if 'media' in body else [],
                        "price_build": body['price_build'] if 'price_build' in body else {"collection": True,
                                                                                          "semi_calculation": False,
                                                                                          "full_calculation": False,
                                                                                          "external_calculation": False},
                        "countries": body["countries"] if 'countries' in body else [],
                        "has_products": False,
                        "has_manifest": False,
                        "calculation_method": body['calculation_method'] if 'calculation_method' in body else [
                            {
                                "name": 'Fixed price',
                                "slug": 'fixed-price',
                                "active": True
                            },
                            {
                                "name": 'Sliding scale',
                                "slug": 'sliding-scale',
                                "active": False
                            }
                        ],
                        "dlv_days": body['dlv_days'] if 'dlv_days' in body else [],
                        "production_days": body['production_days'] if 'production_days' in body else [
                            {
                                "active": True,
                                "day": "mon",
                                "deliver_before": "12:00"
                            },
                            {
                                "active": True,
                                "day": "tue",
                                "deliver_before": "12:00"
                            },
                            {
                                "active": True,
                                "day": "wed",
                                "deliver_before": "12:00"
                            },
                            {
                                "active": True,
                                "day": "thu",
                                "deliver_before": "12:00"
                            },
                            {
                                "active": True,
                                "day": "fri",
                                "deliver_before": "12:00"
                            },
                            {
                                "active": False,
                                "day": "sat",
                                "deliver_before": "12:00"
                            },
                            {
                                "active": False,
                                "day": "sun",
                                "deliver_before": "12:00"
                            },
                        ],
                        "printing_method": body['printing_method'] if 'printing_method' in body else [],
                        "start_cost": body['start_cost'] if 'start_cost' in body else 0,

                    }
                    SupplierCategory(**data_to_store).save()
                    supplier_category = {
                        "_id": {
                            "$oid": ""
                        },
                        "boops": self.create_boops(category, body),
                        "display_name": cat_display_name,
                        "generated": True,
                        "name": body['name'],
                        "system_key": body['system_key'],
                        "published": True,
                        "ref_boops_name": "",
                        "ref_id": "",
                        "shareable": False,
                        "slug": slugify(body['system_key'], to_lower=True),
                        "tenant_id": supplier_id,
                        "tenant_name": body["tenant_name"]
                    }
                else:
                    return {
                        "data": None,
                        "message": "Category is not exists.",
                        "status": 422
                    }, 422
            else:

                # run similarty
                categories = [{
                    "name": body['name'],
                    "sku": ""}]
                url = 'http://assortments:5000/similarity/categories'
                obj = {'tenant': supplier_id, 'tenant_name': body["tenant_name"], 'categories': categories}
                header = {"Content-type": "application/json"}
                requests.post(url, json=obj, headers=header)
                # store Data
                data_to_store = {
                    "sort": body['sort'] if 'sort' in body else 0,
                    "tenant_id": supplier_id,
                    "tenant_name": body["tenant_name"],
                    "sku": body['sku'] if 'sku' in body else "",
                    "name": body['name'],
                    "system_key": body['system_key'],
                    "display_name": cat_display_name,
                    "slug": slugify(body['system_key'], to_lower=True),
                    "source_slug": body['source_slug'] if 'source_slug' in body else None,
                    "description": body['description'] if 'description' in body else "",
                    "shareable": body['shareable'] if 'shareable' in body else False,
                    "published": body['published'] if 'published' in body else False,
                    "media": body['media'] if 'media' in body else [],

                    "ranges": body['ranges'] if 'ranges' in body else [],
                    "range_list": body['range_list'] if 'range_list' in body else [],
                    "free_entry": body['free_entry'] if 'free_entry' in body else [],
                    "limits": body['limits'] if 'limits' in body else [],
                    "bleed": body['bleed'] if 'bleed' in body else 0,
                    "range_around": body['range_around'] if 'range_around' in body else None,

                    "price_build": body['price_build'] if 'price_build' in body else {"collection": True,
                                                                                      "semi_calculation": False,
                                                                                      "full_calculation": False,
                                                                                      "external_calculation": False},
                    "countries": body["countries"] if 'countries' in body else [],
                    "has_products": False,
                    "has_manifest": False,
                    "production_days": body['production_days'] if 'production_days' in body else [
                        {
                            "active": True,
                            "day": "mon",
                            "deliver_before": "12:00"
                        },
                        {
                            "active": True,
                            "day": "tue",
                            "deliver_before": "12:00"
                        },
                        {
                            "active": True,
                            "day": "wed",
                            "deliver_before": "12:00"
                        },
                        {
                            "active": True,
                            "day": "thu",
                            "deliver_before": "12:00"
                        },
                        {
                            "active": True,
                            "day": "fri",
                            "deliver_before": "12:00"
                        },
                        {
                            "active": False,
                            "day": "sat",
                            "deliver_before": "12:00"
                        },
                        {
                            "active": False,
                            "day": "sun",
                            "deliver_before": "12:00"
                        },
                    ],
                    "calculation_method": body['calculation_method'] if 'calculation_method' in body else [],
                    "dlv_days": body['dlv_days'] if 'dlv_days' in body else [],
                    "printing_method": body['printing_method'] if 'printing_method' in body else [],
                    "start_cost": body['start_cost'] if 'start_cost' in body else 0,
                    "vat": body['vat'] if 'vat' in body else 0.0,
                }
                supplier_category = SupplierCategory.objects(tenant_id=supplier_id,
                                                             slug=slugify(body['system_key'], to_lower=True)).first()
                if supplier_category:
                    supplier_category.modify(**data_to_store)
                    ## also here
                    supplier_category = SupplierBoops.objects(slug=supplier_category.slug).first()
                else:
                    supplier_category = SupplierCategory(**data_to_store).save()
                    supplier_category = SupplierBoops(**{
                        "tenant_id": supplier_id,
                        "tenant_name": body['tenant_name'],
                        "supplier_category": supplier_category.id,
                        "linked": supplier_category.linked,
                        "name": supplier_category.name,
                        "display_name": supplier_category.display_name,
                        "system_key": supplier_category.system_key,
                        "slug": supplier_category.slug,
                        "boops": []
                    }).save()
            return jsonify(supplier_category)

    def create_boops(self, category, body):

        Boops = CategoryBoxOption.objects(category=category).aggregate([
            {
                "$lookup": {
                    "from": "boxes",  # Tag collection database name
                    "as": "box",
                    "foreignField": "_id",  # Primary key of the Tag collection
                    "localField": "box",  # Reference field
                },
            },
            {
                "$lookup": {
                    "from": "options",  # Tag collection database name
                    "foreignField": "_id",  # Primary key of the Tag collection
                    "localField": "option",  # Reference field
                    "as": "option",
                },
            },
            {
                "$project": {

                    "boxes": "$box",
                    "options": "$option",
                },
            }
        ])
        boxObj = {}
        for item in Boops:
            data = json.loads(dumps(item))
            # delivery-type  || printing-process
            if item['boxes'][0]['slug'] == "delivery-type" or item['boxes'][0]['slug'] == "printing-process":
                continue
            key = item['boxes'][0]['name']
            if item['boxes'][0]['slug'] not in boxObj:
                box_display_name = []
                for lang in body['lang']:
                    box_display_name.append({
                        'iso': lang,
                        'display_name': key,
                    })
                boxObj[item['boxes'][0]['slug']] = {
                    "id": {
                        "$oid": ""
                    },
                    "sort": 0,
                    "name": key,
                    "system_key": key,
                    "display_name": box_display_name,
                    "slug": slugify(key, to_lower=True),
                    "media": [],
                    "input_type": "",
                    "published": False,
                    "unit": "",
                    "maximum": "",
                    "minimum": "",
                    "incremental_by": "",
                    "information": "",
                    "ops": []
                }

            opt_display_name = []
            opt_name = item['options'][0]['name']
            for lang in body['lang']:
                opt_display_name.append({
                    'iso': lang,
                    'display_name': opt_name,
                })
            boxObj[item['boxes'][0]['slug']]["ops"].append({
                "id": {
                    "$oid": ""
                },
                "name": item['options'][0]['name'],
                "system_key": item['options'][0]['name'],
                "display_name": opt_display_name,
                "slug": item['options'][0]['slug'],
                "input_type": "",
                "published": False,
                "excludes": []
            })
        result = []
        for box in boxObj:
            boxObj[box]["ops"] = sorted(boxObj[box]["ops"], key=lambda x: x['slug'], reverse=False)
            result.append(boxObj[box])
        return result


########################################
############ update delete class #######
########################################
class SupplierCategoryApi(Resource):
    def get(self, supplier, slug):
        category = SupplierCategory.objects(tenant_id=supplier, slug=slug).first()
#         if not category or category.shareable is False: return []
        boop = SupplierBoops.objects(supplier_category=category).first()
        print(boop, category)
        return {"category": category.to_json() , "boop": boop.to_json() }

    def put(self, supplier, slug):
        # body = request.form.to_dict(flat=True)
        body = request.get_json()
        category = SupplierCategory.objects(tenant_id=supplier, slug=slug).first()
        if not category:
            return {
                "data": None,
                "message": "Category not found.",
                "status": 404
            }, 404

        machines = []

        if 'additional' in body:
            for a in body['additional']:
                machines.append({"machine": ObjectId(a['machine'])} if 'machine' in a else a)

        data_to_store = {
            "sort": body['sort'] if 'sort' in body else category.sort,
            "tenant_id": category.tenant_id,
            "tenant_name": category.tenant_name,
            "sku": body['sku'] if 'sku' in body else category.sku,
            'display_name': body['display_name'] if 'display_name' in body else category.display_name,
            'system_key': body['system_key'] if 'system_key' in body else category.system_key,
            'production_days': body['production_days'] if 'production_days' in body else category.production_days,
            'description': body['description'] if 'description' in body else category.description,
            'shareable': bool(body['shareable']) if 'shareable' in body else category.shareable,
            'published': bool(body['published']) if 'published' in body else category.published,
            "media": body['media'] if 'media' in body else category.media,
            "price_build": body['price_build'] if 'price_build' in body else category.price_build,
            "countries": body["countries"] if 'countries' in body else category.countries,
            "has_products": bool(body['has_products']) if 'has_products' in body else category.has_products,
            "has_manifest": bool(body['has_manifest']) if 'has_manifest' in body else category.has_manifest,
            "calculation_method": body[
                'calculation_method'] if 'calculation_method' in body else category.calculation_method,
            "ranges": body['ranges'] if 'ranges' in body else category.ranges,
            "range_list": body['range_list'] if 'range_list' in body else category.range_list,
            "free_entry": body['free_entry'] if 'free_entry' in body else category.free_entry,
            "limits": body['limits'] if 'limits' in body else category.limits,
            "bleed": body['bleed'] if 'bleed' in body else category.bleed,
            "range_around": body['range_around'] if 'range_around' in body else category.range_around,

            "dlv_days": body['dlv_days'] if 'dlv_days' in body else category.dlv_days,
            "printing_method": body['printing_method'] if 'printing_method' in body else category.printing_method,
            "start_cost": body['start_cost'] if 'start_cost' in body else category.start_cost,
            "additional": machines,
            "vat": body['vat'] if 'vat' in body else 0.0,
        }

        if ("linked" in body) and (body['linked'] != None):
            category_link = Category.objects(id=body['linked']).first()
            if category_link:
                data_to_store['linked'] = category_link

        category.update(**data_to_store)
        category = SupplierCategory.objects(tenant_id=supplier, slug=slug).first()
        return jsonify(category)

    def delete(self, supplier, slug):
        body = request.form.to_dict(flat=True)
        category = SupplierCategory.objects(tenant_id=supplier, slug=slug).first()
        boops = SupplierBoops.objects(tenant_id=supplier, slug=slug).first()
        supplier_products = SupplierProduct.objects(tenant_id=supplier, category_slug=slug)
        product_prices = SupplierProductPrice.objects(supplier_id=supplier).aggregate([
            {
                "$lookup": {
                    "from": "supplier_products",
                    "localField": "supplier_product",
                    "foreignField": "_id",
                    "as": "product"
                }
            },
            {
                "$project": {
                    "_id": 1
                }

            }
        ])

        if category:
            for price in product_prices:
                id = json.loads(dumps(price))
                SupplierProductPrice.objects(id=id['_id']['$oid']).delete()
            for product in supplier_products:
                id = json.loads(dumps(product.id))
                SupplierProduct.objects(id=id['$oid']).delete()

            category.delete()
            if boops:
                boops.delete()
            return {
                "data": None,
                "message": "Category has been deleted successfully.",
                "status": 200
            }, 200
        return {
            "data": None,
            "message": "Category not found.",
            "status": 404
        }, 200


class SupplierPivotCategoryApi(Resource):

    def get(self, supplier, model):
        print(supplier)
        pass

    def post(self, supplier_id):
        # body = request.form.to_dict(flat=True)
        body = request.get_json()
        categories = SupplierCategory.objects(tenant_id=supplier_id, id__in=body).to_json()

        print(categories)
        return {
            "data": categories,
            "status": 200
        }, 200


class SupplierCategoriesCountApi(Resource):
    def get(self, supplier_id):
        categories = SupplierCategory.objects(tenant_id=supplier_id, shareable=True, published=True).count()
        return jsonify(categories)
