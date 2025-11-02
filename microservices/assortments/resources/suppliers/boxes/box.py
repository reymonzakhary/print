from flask import Response, request, jsonify
from flask.json import dump
from models.category import Category
from models.supplierBox import SupplierBox
from models.boop import Boop
from models.supplierBoops import SupplierBoops
from models.matchedCategory import MatchedCategory
from models.unmatchedCategory import UnmatchedCategory
from models.categoryBoxOption import CategoryBoxOption
from models.box import Box
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource, fields, marshal
from bson.json_util import dumps
import json
import datetime
import math
import requests
from bson import ObjectId

##############################
#   handel index and store  #
#############################
class SupplierBoxesApi(Resource):
    def get(self, supplier_id):
        page = 1 if request.args.get('page') is None or request.args.get('page') == "" else int(
            request.args.get('page'))
        per_page = 10 if request.args.get('per_page') is None or request.args.get('per_page') == "" else int(
            request.args.get('per_page'))
        skip = 0 if page == 1 else (per_page * page) - per_page
        filter = "" if request.args.get('filter') is None or request.args.get('filter') == "" else request.args.get(
            'filter')
        sortBy = "name" if request.args.get('sort_by') is None or request.args.get(
            'sort_by') == "" else request.args.get('sort_by')
        sortDir = "" if request.args.get('sort_dir') is None or request.args.get('sort_dir') == "" or request.args.get(
            'sort_dir') == "asc" else "-"

        # return jsonify(SupplierBox.objects(tenant_id=supplier_id))
        categories = SupplierBox.objects(tenant_id=supplier_id).order_by(sortDir + sortBy).aggregate([
            {
                "$match": {
                    "name": {
                        "$regex": filter,
                        "$options": 'i'  # case-insensitive
                    }
                }
            },

            {
                "$facet": {
                    "data": [
                        {
                            "$match": {"name": {
                                "$regex": filter,
                                "$options": 'i'  # case-insensitive
                            }
                            }
                        },
                        {"$skip": skip},
                        {"$limit": per_page},
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
                                "from": "matched_categories",  # Tag collection database name
                                "foreignField": "category",  # Primary key of the Tag collection
                                "localField": "_id",  # Reference field
                                "as": "matches",
                            }
                        },
                    ],
                    "count": [{
                        "$count": "count"
                    }]
                },
            }
        ])
        categories = json.loads(dumps(*categories))
        items = categories['data']
        if len(categories['count']) == 0:
            count = 0
        else:
            count = json.loads(dumps(*categories['count']))['count']

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

    def post(self, supplier_id):
        # body = request.form.to_dict(flat=True)
        body = request.get_json()

        options = []
        # return linked
        supplier_box = SupplierBox.objects(tenant_id=supplier_id, id=ObjectId(body['id'])).first()

        box_display_name = []
        for lang in body['lang']:
            box_display_name.append({
                'iso': lang,
                'display_name': body['name'],
            })
        if supplier_box:
            return jsonify(box=supplier_box)
        else:
            if "linked" in body and body['linked']:
                box = Box.objects(id=body['linked']).first()
                excludes_visible = bool(body['additional']['excludes_visible']) if 'excludes_visible' in body['additional'] else True
                additional = {
                    "excludes_visible": excludes_visible
                }
                if box:
                    data_to_store = {
                        "tenant_id": supplier_id,
                        "tenant_name": body["tenant_name"],
                        "sku": supplier_id,
                        "name": box.name,
                        "display_name": box_display_name,
                        "system_key": body['system_key'],
                        "input_type": body['input_type'],
                        "additional": additional,
                        "incremental": bool(body['incremental']) if 'incremental' in body else False,
                        "select_limit": int(body['select_limit']) if 'select_limit' in body else 0,
                        "option_limit": int(body['option_limit']) if 'option_limit' in body else 0,
                        "sqm": bool(body['sqm']) if 'sqm' in body else False,
                        "media": body['media'] if 'media' in body else [],
                        "description": body['description'] if 'description' in body else "",
                        "shareable": bool(body['shareable']) if 'shareable' in body else False,
                        "slug": slugify(body['name'], to_lower=True),
                        "source_slug": body['source_slug'] if 'source_slug' in body else None,
                        "linked": box,
                        "start_cost": body['start_cost'] if 'start_cost' in body else 0,
                        "published": bool(body['published']) if 'published' in body else False,
                    }
                    supplier_box = SupplierBox(**data_to_store).save()
                    options = CategoryBoxOption.objects(box=body['linked']).aggregate([
                        {
                            "$lookup": {
                                "from": "options",
                                "let": {"option": "$option"},
                                "pipeline": [
                                    {"$match":
                                        {"$expr":
                                            {
                                                "$and":
                                                    [
                                                        {"$eq": ["$$option", "$_id"]},
                                                    ],
                                            }
                                        },
                                    },
                                ],
                                "as": "option"
                            },
                        },
                        {
                            "$project": {
                                "_id": 0,
                                "box": 0,
                                "category": 0,
                            }
                        }
                    ])
                else:
                    # run similarty
                    dataToStore = {
                        "tenant_id": supplier_id,
                        "tenant_name": body["tenant_name"],
                        "sku": supplier_id,
                        "name": body['name'],
                        "display_name": box_display_name,
                        "system_key": body['system_key'],
                        "input_type": body['input_type'] if 'input_type' in body else "",
                        "calc_ref": body['calc_ref'] if 'calc_ref' in body else "",
                        "incremental": bool(body['incremental']) if 'incremental' in body else False,
                        "select_limit": int(body['select_limit']) if 'select_limit' in body else 0,
                        "option_limit": int(body['option_limit']) if 'option_limit' in body else 0,
                        "sqm": bool(body['sqm']) if 'sqm' in body else False,
                        "media": body['media'] if 'media' in body else [],
                        "description": body['description'] if 'description' in body else "",
                        "shareable": bool(body['shareable']) if 'shareable' in body else False,
                        "slug": slugify(body['name'], to_lower=True),
                        "source_slug": body['source_slug'] if 'source_slug' in body else None,
                        "start_cost": body['start_cost'] if 'start_cost' in body else 0,
                        "published": bool(body['published']) if 'published' in body else False,
                    }
                    supplier_box = SupplierBox(**dataToStore).save()
                    boxes = [{
                        "name": body['name'],
                        "sku": ""}]
                    url = 'http://assortments:5000/similarity/boxes'
                    obj = {'tenant': supplier_id, 'tenant_name': body["tenant_name"], 'boxes': boxes}

                    header = {"Content-type": "application/json"}
                    # return json.dumps(boxes)
                    res = requests.post(url, json=obj, headers=header)

                opt = []
                for option in json.loads(dumps(options)):
                    opt.append(option['option'][0])

                return jsonify(box=supplier_box, data=json.loads(dumps(opt)))
            else:
                # run similarty
                dataToStore = {
                    "tenant_id": supplier_id,
                    "tenant_name": body["tenant_name"],
                    "sku": supplier_id,
                    "name": body['name'],
                    "display_name": box_display_name,
                    "system_key": body['system_key'],
                    "input_type": body['input_type'] if 'input_type' in body else "",
                    "calc_ref": body['calc_ref'] if 'calc_ref' in body else "",
                    "incremental": bool(body['incremental']) if 'incremental' in body else False,
                    "select_limit": int(body['select_limit']) if 'select_limit' in body else 0,
                    "option_limit": int(body['option_limit']) if 'option_limit' in body else 0,
                    "sqm": bool(body['sqm']) if 'sqm' in body else False,
                    "media": body['media'] if 'media' in body else [],
                    "description": body['description'] if 'description' in body else "",
                    "shareable": bool(body['shareable']) if 'shareable' in body else False,
                    "slug": slugify(body['name'], to_lower=True),
                    "source_slug": body['source_slug'] if 'source_slug' in body else None,
                    "start_cost": body['start_cost'] if 'start_cost' in body else 0,
                    "published": bool(body['published']) if 'published' in body else False,
                }
                supplier_box = SupplierBox(**dataToStore).save()
                boxes = [{
                    "name": body['name'],
                    "sku": ""}]
                url = 'http://assortments:5000/similarity/boxes'
                obj = {'tenant': supplier_id, 'tenant_name': body["tenant_name"], 'boxes': boxes}

                header = {"Content-type": "application/json"}
                # return json.dumps(boxes)
                res = requests.post(url, json=obj, headers=header)

            opt = []
            for option in json.loads(dumps(options)):
                opt.append(option['option'][0])

            return jsonify(box=supplier_box, data=json.loads(dumps(opt)))


########################################
######### update delete class ##########
########################################
class SupplierBoxApi(Resource):

    def get(self, supplier, slug):

        box = SupplierBox.objects(tenant_id=supplier, slug=slug).to_json()

        if box:
            return {
                "data": json.loads(box),
                "status": 200
            }, 200
        return {
            "message": "Box not found",
            "status": 200
        }, 200

    def put(self, supplier, slug):
        # body = request.form.to_dict(flat=True)
        body = request.get_json()

        category = SupplierBox.objects(tenant_id=supplier, slug=slug).first()
        if not category:
            return {
                "message": "Box not found.",
                "status": 404
            }, 404


        dataToStore = {
            "display_name": body['display_name'] if 'display_name' in body else category.display_name,
            "system_key": body['system_key'] if 'system_key' in body else category.system_key,
            "sort": int(body['sort']) if 'soft' in body else 0,
            "input_type": body['input_type'] if 'input_type' in body else "",
            "calc_ref": body['calc_ref'] if 'calc_ref' in body else "",
            "incremental": bool(body['incremental']) if 'incremental' in body else False,
            "select_limit": int(body['select_limit']) if 'select_limit' in body else 0,
            "option_limit": int(body['option_limit']) if 'option_limit' in body else 0,
            "sqm": bool(body['sqm']) if 'sqm' in body else False,
            "media": body['media'] if 'media' in body else [],
            "description": body['description'] if 'description' in body else "",
            "shareable": bool(body['shareable']) if 'shareable' in body else False,
            "start_cost": body['start_cost'] if 'start_cost' in body else 0,
            "published": bool(body['published']) if 'published' in body else False,
            "appendage": bool(body['appendage']) if 'appendage' in body else False,
            "additional": body['additional'] if 'additional' in body else [],
        }
        self.update_boops(supplier, body, dataToStore)
        category.update(**dataToStore)
        category = SupplierBox.objects(tenant_id=supplier, slug=slug).first()
        return jsonify(category)

    def update_boops(self, tenant, body, dataToStore):
        category_id = body.get("category_id")
        box_id = body.get("id") 

        if not category_id or not box_id:
            return
        
        query = {
            "tenant_id": tenant,
            "boops.id": ObjectId(box_id)
        }

        if category_id:
            try:
                query["supplier_category"] = ObjectId(category_id)
            except Exception:
                pass

        boops = SupplierBoops._get_collection().update_one(
            query,
            {
                "$set": {
                    "boops.$.display_name": dataToStore['display_name'],
                    "boops.$.system_key": dataToStore['system_key'],
                    "boops.$.media": dataToStore['media'],
                    "boops.$.description": dataToStore['description'],
                    "boops.$.published": dataToStore['published'],
                    "boops.$.appendage": dataToStore['appendage'],
                    "boops.$.input_type": dataToStore['input_type'],
                    "boops.$.calc_ref": dataToStore['calc_ref'],
                }
            }
        )