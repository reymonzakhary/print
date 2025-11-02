from flask import jsonify, session, request, Response
from models.box import Box
from models.category import Category
from models.option import Option
from models.categoryBoxOption import CategoryBoxOption
from models.supplierBox import SupplierBox
from models.supplierBoops import SupplierBoops
from models.manifest import Manifest
from models.matchedBox import MatchedBox
import json
from flask_restful import Resource
import requests
from bson.json_util import dumps
from slugify import slugify, Slugify, UniqueSlugify
import math
import uuid
from bson import ObjectId
from helper.helper import login_to_print_com, generate_display_names, translate_text
from models.supplierOption import SupplierOption


##############################
#   handel index and store  #
#############################


class BoxesApi(Resource):
    def get(self):
        page = 1 if request.args.get('page') is None or request.args.get('page') == "" else int(
            request.args.get('page'))
        per_page = 10 if request.args.get('per_page') is None or request.args.get('per_page') == "" else int(
            request.args.get('per_page'))
        skip = 0 if page == 1 else (per_page * page) - per_page
        filter = "" if request.args.get('filter') is None or request.args.get('filter') == "" else request.args.get(
            'filter')

        boxes = Box.objects.aggregate([
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
                            "$match": {
                                "name": {
                                    "$regex": filter,
                                    "$options": 'i'  # case-insensitive
                                }
                            }
                        },
                        {
                            "$lookup": {
                                "from": "supplier_boxes",  # Tag collection database name
                                "foreignField": "linked",  # Primary key of the Tag collection
                                "localField": "_id",  # Reference field
                                "as": "suppliers",
                            },
                        },
                        {
                            "$project": {
                                "categories": 0
                            }
                        },
                        {"$skip": skip},
                        {"$limit": per_page},
                    ],
                    "count": [{
                        "$count": "count"
                    }]
                },
            }
        ])
        boxes = json.loads(dumps(*boxes))
        items = boxes['data']
        count = 0 if len(boxes['count']) == 0 else json.loads(dumps(*boxes['count']))['count']
        last_page = math.ceil(count / per_page)
        next_page = None if last_page <= page else page + 1
        first_page = 1

        return {
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
            "data": items,
        }, 200

    def post(self):
        body = request.form.to_dict(flat=True)
        dbox = {
            "sort": body['sort'],
            "name": body['name'],
            "display_name": generate_display_names(body['name']),
            "system_key": slugify(body['name'], to_lower=True),
            "slug": slugify(body['name'], to_lower=True),
            "description": body['description'],
            "sqm": body['sqm'],
            "appendage": body['appendage'],
            "published": body['published'],
            "input_type": body['input_type'],
        }
        #         body = request.get_json()
        box = Box(**dbox).save()
        return jsonify(box)


class BoxApi(Resource):
    def get(self, slug):
        req_fields = [
            'sort', 'tenant_id', 'tenant_name', 'sku', 'name', 'display_name', 'system_key', 'slug', 'description',
            'media',
            'sqm', 'appendage', 'published', 'input_type', 'calculation_type', 'incremental', 'select_limit',
            'option_limit', 'shareable', 'start_cost', 'created_at', 'categories', 'additional', 'created_at'
        ]
        box = Box.objects.only(*req_fields).get_or_404(slug=slug)
        return jsonify({"data": box,
                        "status": 200,
                        "message": ""})

    def put(self, slug):

        body = request.form.to_dict(flat=True)

        # Convert boolean fields properly
        body["published"] = body.get("published") == "1"
        body["checked"] = body.get("checked") == "1"
        body["sqm"] = body.get("sqm") == "1"
        body["appendage"] = body.get("appendage") == "1"

        # Rebuild the display_name list
        display_name = []
        index = 0

        while f"display_name[{index}][iso]" in body:
            display_name.append({
                "iso": body[f"display_name[{index}][iso]"],
                "display_name": body[f"display_name[{index}][display_name]"]
            })
            index += 1

        # Remove all keys that start with "display_name[" dynamically
        update_query = {
            f"set__{k}": v
            for k, v in body.items()
            if not k.startswith("display_name[") and k != "slug"
        }

        # Add display_name update separately as a whole list
        update_query["set__display_name"] = display_name

        # Perform the update
        Box.objects(slug=slug).update_one(**update_query)

        return {
            "data": None,
            "message": "Box has been updated successfully",
            "status": 200
        }, 200

    def delete(self, slug):

        # check if category has relation
        force = False if request.args.get('force') is None or request.args.get('force') == "0" or request.args.get('force') == "false" else True

        box = Box.objects(slug=slug).first()

        if force:
            linked = {"linked": None}
            SupplierBox.objects(linked=box.id).modify(**linked)
            CategoryBoxOption.objects(box=box.id).delete()
            Manifest.objects(boops__id=box.id).update(pull__boops__id=box.id)

            SupplierBoops._get_collection().update_many(
                {"boops.linked": box.id},
                {"$set": {"boops.$[boop].linked": None}},
                array_filters=[{"boop.linked": box.id}]
            )
            MatchedBox.objects(box=box).delete()
            box.delete()
            return {
                "data": None,
                "message": "Box has been deleted successfully",
                "status": 200
            }, 200

        if (
            len(box['categories']) == 0 
            and SupplierBox.objects(linked=box['id']).count() == 0 
            and CategoryBoxOption.objects(box=box).count() == 0 
            and Manifest.objects(boops__id=box.id).count() == 0
        ):
            box.delete()
            return {
                "data": None,
                "message": "Box has been deleted successfully",
                "status": 200
            }, 200
        else:
            return {
                "data": None,
                "message": "We can't remove this box because it has a relation with different tables.",
                "status": 422
            }, 200

class BoxOptionsApi(Resource):
    def get(self, slug):
        box = Box.objects.get(slug=slug)
        options_obj = []
        options = CategoryBoxOption.objects(box=box.id).only(*['option'])

        for opt in options:
            options_obj.append(opt['option'])

        return jsonify(options_obj)


class UpdateOptions(Resource):
    def get(self):
        for boop in SupplierBoops.objects():
            category_id = ObjectId(boop.supplier_category.id)
            print(f"boop id {boop.id}")
            boops = boop.boops
            print(f"Updated category {category_id}.")
            if isinstance(boops, list) and boops:  # Ensure boops is a list
                first_bo = boops
                for b in first_bo:
                    for op in b['ops']:
                        supplier_option = SupplierOption.objects(id=ObjectId(op["id"])).first()
                        if not supplier_option:
                            continue  # Skip if no supplier option found

                        # Extract old field values before deleting them
                        extracted_data = {
                            "incremental_by": supplier_option.incremental_by if hasattr(supplier_option,
                                                                                        "incremental_by") else 0,
                            "dimension": supplier_option.dimension if hasattr(supplier_option, "dimension") else "",
                            "dynamic": supplier_option.dynamic if hasattr(supplier_option, "dynamic") else False,
                            "dynamic_keys": supplier_option.dynamic_keys if hasattr(supplier_option,
                                                                                    "dynamic_keys") else [],
                            "start_on": supplier_option.start_on if hasattr(supplier_option, "start_on") else 0,
                            "end_on": supplier_option.end_on if hasattr(supplier_option, "end_on") else 0,
                            "generate": supplier_option.generate if hasattr(supplier_option, "generate") else False,
                            "dynamic_type": supplier_option.dynamic_type if hasattr(supplier_option,
                                                                                    "dynamic_type") else "",
                            "unit": supplier_option.unit if hasattr(supplier_option, "unit") else "cm",
                            "width": supplier_option.width if hasattr(supplier_option, "width") else 0,
                            "maximum_width": supplier_option.maximum_width if hasattr(supplier_option,
                                                                                      "maximum_width") else 0,
                            "minimum_width": supplier_option.minimum_width if hasattr(supplier_option,
                                                                                      "minimum_width") else 0,
                            "height": supplier_option.height if hasattr(supplier_option, "height") else 0,
                            "maximum_height": supplier_option.maximum_height if hasattr(supplier_option,
                                                                                        "maximum_height") else 0,
                            "minimum_height": supplier_option.minimum_height if hasattr(supplier_option,
                                                                                        "minimum_height") else 0,
                            "length": supplier_option.length if hasattr(supplier_option, "length") else 0,
                            "maximum_length": supplier_option.maximum_length if hasattr(supplier_option,
                                                                                        "maximum_length") else 0,
                            "minimum_length": supplier_option.minimum_length if hasattr(supplier_option,
                                                                                        "minimum_length") else 0,
                            "start_cost": supplier_option.start_cost if hasattr(supplier_option, "start_cost") else 0,
                            "calculation_method": supplier_option.calculation_method if hasattr(supplier_option,
                                                                                                "calculation_method") else []
                        }

                        # Update `configure` field
                        configure = supplier_option.configure if hasattr(supplier_option, "configure") else []
                        configure = [c for c in configure if
                                     c.get("category_id") != category_id]  # Remove old category_id if exists
                        configure.append({"category_id": category_id, "configure": extracted_data})

                        supplier_option.update(set__configure=configure)
                        print(f"Updated SupplierOption {supplier_option.id} with new configure format.")


class SysDocs(Resource):
    uri = "https://api.stg.print.com/"
    headers = {
        "Accept": "application/json",
        "Content-Type": "application/json",
    }

    @staticmethod
    def get_auth_header():
        token = session.get('auth_token')
        if not token:
            login_to_print_com()
        return {**SysDocs.headers, "Authorization": f"Bearer {token}"}

    @staticmethod
    def create_category_object(category):
        return {
            "name": category["titleSingle"],
            "slug": category["sku"],
            "sku": str(uuid.uuid4()),
            "display_name": generate_display_names(category["titleSingle"]),
            "system_key": category["sku"],
            "printing_method": [],
            "price_build": {
                "collection": False,
                "semi_calculation": True,
                "full_calculation": False,
                "external_calculation": False
            },
            "production_days": [
                {
                    "day": 'mon',
                    "active": True,
                    "deliver_before": '12:00'
                },
                {
                    "day": 'tue',
                    "active": True,
                    "deliver_before": '12:00'
                },
                {
                    "day": 'wed',
                    "active": True,
                    "deliver_before": '12:00'
                },
                {
                    "day": 'thu',
                    "active": True,
                    "deliver_before": '12:00'
                },
                {
                    "day": 'fri',
                    "active": True,
                    "deliver_before": '12:00'
                },
                {
                    "day": 'sat',
                    "active": False,
                    "deliver_before": '12:00'
                },
                {
                    "day": 'sun',
                    "active": False,
                    "deliver_before": '12:00'
                }
            ],
            "calculation_method": [
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
            "production_dlv": [],
            "ref_id": "",
            "ref_category_name": ""
        }

    @staticmethod
    def post():
        proxy = requests.get(
            SysDocs.uri + "products",
            data={},
            headers=SysDocs.get_auth_header())

        if proxy.status_code != 200:
            login_to_print_com()
            return
        count: int = 0
        for category in proxy.json():
            if category.get('titleSingle'):
                obj = SysDocs.create_category_object(category)
                if Category.objects(slug=obj["slug"]).count() == 0:
                    Category(**obj).save()
                    count += 1
        return {"data": f"There is {count} Categories has been inserted successfully.", "status": 200}, 200

    def get(self):
        categories = Category.objects.all()
        for category in categories:
            proxy = requests.get(
                SysDocs.uri + "products/" + category.slug + "?fields=excludes",
                data={},
                headers=SysDocs.get_auth_header())
            print(proxy.json())
            return proxy.json()


class Standard(Resource):

    @staticmethod
    def create_category_object(category):
        return {
            "name": category["name"],
            "sku": str(uuid.uuid4()),
            "display_name": generate_display_names(category["name"]),
            "system_key": slugify(category["name"], to_lower=True),
            "printing_method": [],
            "price_build": {
                "collection": False,
                "semi_calculation": True,
                "full_calculation": False,
                "external_calculation": False
            },
            "production_days": [
                {
                    "day": 'mon',
                    "active": True,
                    "deliver_before": '12:00'
                },
                {
                    "day": 'tue',
                    "active": True,
                    "deliver_before": '12:00'
                },
                {
                    "day": 'wed',
                    "active": True,
                    "deliver_before": '12:00'
                },
                {
                    "day": 'thu',
                    "active": True,
                    "deliver_before": '12:00'
                },
                {
                    "day": 'fri',
                    "active": True,
                    "deliver_before": '12:00'
                },
                {
                    "day": 'sat',
                    "active": False,
                    "deliver_before": '12:00'
                },
                {
                    "day": 'sun',
                    "active": False,
                    "deliver_before": '12:00'
                }
            ],
            "calculation_method": [
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
            "production_dlv": [],
            "ref_id": "",
            "ref_category_name": ""
        }

    # def post(self):
    #     # create categories to the assortments api
    #     proxy = requests.get(f"https://api.printdeal.com/api/products/categories", data={}, headers={
    #         "API-Secret": "Mi4hQt31Pzbab6+aiBJQUFCg7Mo3Tp/AhY4tcohVVIcqcxL7",
    #         "User-ID": "0ac967eb-1726-4f4d-9cab-d7be7183a78d",
    #         "accept": "application/vnd.printdeal-api.v2"
    #     })
    #
    #     data = request.form.to_dict(flat=True)
    #     for cat in proxy.json():
    #         print(slugify(cat["name"], to_lower=True))
    #         obj = {
    #             "name": cat["name"],
    #             "sku": cat["sku"],
    #             "display_name": [
    #                 {"iso": "en", "display_name": cat["name"]},
    #                 {"iso": "fr", "display_name": cat["name"]},
    #                 {"iso": "nl", "display_name": cat["name"]},
    #                 {"iso": "de", "display_name": cat["name"]},
    #             ],
    #             "system_key": slugify(cat["name"], to_lower=True),
    #             "printing_method": [],
    #             "ref_id": "",
    #             "ref_category_name": ""
    #         }
    #
    #         if Category.objects(name=obj["name"]).count() == 0 and Category.objects(slug=slugify(obj["name"], to_lower=True)).count() == 0:
    #             category = Category(**obj).save()
    #
    #             proxy = requests.get(f"https://api.printdeal.com/api/products/{cat['sku']}/attributes", data={},
    #                                  headers={
    #                                      "API-Secret": "Mi4hQt31Pzbab6+aiBJQUFCg7Mo3Tp/AhY4tcohVVIcqcxL7",
    #                                      "User-ID": "0ac967eb-1726-4f4d-9cab-d7be7183a78d",
    #                                      "accept": "application/vnd.printdeal-api.v2"
    #                                  })
    #
    #             for k in proxy.json():
    #
    #                 if Box.objects(slug=slugify(k, to_lower=True)).count() == 0:
    #                     box = {
    #                         "name": k,
    #                         "display_name": [
    #                             {"iso": "en", "display_name": k},
    #                             {"iso": "fr", "display_name": k},
    #                             {"iso": "nl", "display_name": k},
    #                             {"iso": "de", "display_name": k}
    #                         ],
    #                         "system_key": k,
    #                         "description": "",
    #                         "tenant_id": "",
    #                         "tenant_name": "",
    #                         "sku": ""
    #                     }
    #                     box_info = Box(**box).save()
    #                     box_info.update(add_to_set__categories=category)
    #                 else:
    #                     box_info = Box.objects(
    #                         slug=slugify(k, to_lower=True)).first()
    #                     box_info.update(add_to_set__categories=category)
    #
    #                 for v in proxy.json()[k]:
    #                     if isinstance(v, str):
    #                         if Option.objects(slug=slugify(v, to_lower=True)).count() == 0:
    #                             obj = {
    #                                 "name": v,
    #                                 "display_name": [
    #                                     {"iso": "en", "display_name": v},
    #                                     {"iso": "fr", "display_name": v},
    #                                     {"iso": "nl", "display_name": v},
    #                                     {"iso": "de", "display_name": v}
    #                                 ],
    #                                 "system_key": slugify(v, to_lower=True),
    #                                 "sku": str(uuid.uuid4())
    #                             }
    #                             option = Option(**obj).save()
    #
    #                             ob = {
    #                                 "category": category,
    #                                 "box": box_info,
    #                                 "option": option
    #                             }
    #                             if CategoryBoxOption.objects(**ob).count() == 0:
    #                                 CategoryBoxOption(**ob).save()
    #                         else:
    #                             option = Option.objects(
    #                                 slug=slugify(v, to_lower=True)).first()
    #                             ob = {
    #                                 "category": category,
    #                                 "box": box_info,
    #                                 "option": option
    #                             }
    #                             if CategoryBoxOption.objects(**ob).count() == 0:
    #                                 CategoryBoxOption(**ob).save()
    #
    #                     elif isinstance(v, int):
    #                         pass
    #                     else:
    #                         x = ''
    #                         if 'unitOfMeasure' in v:
    #                             x = v['unitOfMeasure']
    #                         else:
    #                             x = None
    #                         if Option.objects(
    #                                 slug=slugify(str(v['maximum']) + "-" + str(v['minimum']),
    #                                              to_lower=True)).count() == 0:
    #                             obj = {
    #                                 "name": str(v['maximum']) + "-" + str(v['minimum']),
    #                                 "display_name": [
    #                                     {"iso": "en", "display_name": str(v['maximum']) + "-" + str(v['minimum'])},
    #                                     {"iso": "fr", "display_name": str(v['maximum']) + "-" + str(v['minimum'])},
    #                                     {"iso": "nl", "display_name": str(v['maximum']) + "-" + str(v['minimum'])},
    #                                     {"iso": "de", "display_name": str(v['maximum']) + "-" + str(v['minimum'])}
    #                                 ],
    #                                 "unit": x,
    #                                 "maximum_width": v['maximum'],
    #                                 "minimum_width": v['minimum'],
    #                                 "incremental_by": v['increment'],
    #                                 "information": json.dumps(v),
    #                                 "input_type": "number",
    #                                 "sku": str(uuid.uuid4())
    #                             }
    #                             option = Option(**obj).save()
    #
    #                             ob = {
    #                                 "category": category,
    #                                 "box": box_info,
    #                                 "option": option
    #                             }
    #                             if CategoryBoxOption.objects(**ob).count() == 0:
    #                                 CategoryBoxOption(**ob).save()
    #                         else:
    #                             option = Option.objects(slug=slugify(
    #                                 str(v['maximum']) + "-" + str(v['minimum']), to_lower=True)).first()
    #                             ob = {
    #                                 "category": category,
    #                                 "box": box_info,
    #                                 "option": option
    #                             }
    #                             if CategoryBoxOption.objects(**ob).count() == 0:
    #                                 CategoryBoxOption(**ob).save()
    #
    #     resJson = {
    #         "status": 200,
    #         "data": "data has been inserted",
    #     }
    #
    #     return resJson, 200

    def post(self):
        # create categories to the assortments api
        proxy = requests.get(
            f"https://api.printdeal.com/api/products/categories",
            data={},
            headers={
                "API-Secret": "Mi4hQt31Pzbab6+aiBJQUFCg7Mo3Tp/AhY4tcohVVIcqcxL7",
                "User-ID": "0ac967eb-1726-4f4d-9cab-d7be7183a78d",
                "accept": "application/vnd.printdeal-api.v2"
            }
        )
        # data = request.form.to_dict(flat=True)
        for cat in proxy.json():
            obj = Standard.create_category_object(cat)

            if Category.objects(name=obj["name"]).count() == 0:
                category = Category(**obj).save()

                proxy = requests.get(
                    f"https://api.printdeal.com/api/products/{cat['sku']}/attributes",
                    data={},
                    headers={
                        "API-Secret": "Mi4hQt31Pzbab6+aiBJQUFCg7Mo3Tp/AhY4tcohVVIcqcxL7",
                        "User-ID": "0ac967eb-1726-4f4d-9cab-d7be7183a78d",
                        "accept": "application/vnd.printdeal-api.v2"
                    }
                )

                for k in proxy.json():
                    if Box.objects(slug=slugify(k, to_lower=True)).count() == 0:
                        box = {
                            "name": k,
                            "display_name": generate_display_names(k),
                            "system_key": k,
                            "description": "",
                            "tenant_id": "",
                            "tenant_name": "",
                            "sku": ""
                        }
                        box_info = Box(**box).save()
                        box_info.update(add_to_set__categories=category)

                    else:
                        box_info = Box.objects(slug=slugify(k, to_lower=True)).first()
                        box_info.update(add_to_set__categories=category)

                    for v in proxy.json()[k]:
                        if isinstance(v, str):
                            if Option.objects(slug=slugify(v, to_lower=True)).count() == 0:
                                obj = {
                                    "name": v,
                                    "display_name": generate_display_names(v),
                                    "system_key": slugify(v, to_lower=True),
                                    "sku": str(uuid.uuid4())
                                }
                                option = Option(**obj).save()

                                ob = {
                                    "category": category,
                                    "box": box_info,
                                    "option": option
                                }
                                if CategoryBoxOption.objects(**ob).count() == 0:
                                    CategoryBoxOption(**ob).save()

                            else:
                                option = Option.objects(slug=slugify(v, to_lower=True)).first()
                                ob = {
                                    "category": category,
                                    "box": box_info,
                                    "option": option
                                }
                                if CategoryBoxOption.objects(**ob).count() == 0:
                                    CategoryBoxOption(**ob).save()

                        elif isinstance(v, int):
                            pass

                        else:
                            x = v['unitOfMeasure'] if 'unitOfMeasure' in v else None

                            if Option.objects(
                                    slug=slugify(str(v['maximum']) + "-" + str(v['minimum']),
                                                 to_lower=True)).count() == 0:
                                obj = {
                                    "name": str(v['maximum']) + "-" + str(v['minimum']),
                                    "display_name": generate_display_names(str(v['maximum']) + "-" + str(v['minimum'])),
                                    "unit": x,
                                    "maximum_width": v['maximum'],
                                    "minimum_width": v['minimum'],
                                    "incremental_by": v['increment'],
                                    "information": json.dumps(v),
                                    "input_type": "number",
                                    "sku": str(uuid.uuid4())
                                }
                                option = Option(**obj).save()

                                ob = {
                                    "category": category,
                                    "box": box_info,
                                    "option": option
                                }
                                if CategoryBoxOption.objects(**ob).count() == 0:
                                    CategoryBoxOption(**ob).save()

                            else:
                                option = Option.objects(slug=slugify(
                                    str(v['maximum']) + "-" + str(v['minimum']), to_lower=True)).first()
                                ob = {
                                    "category": category,
                                    "box": box_info,
                                    "option": option
                                }
                                if CategoryBoxOption.objects(**ob).count() == 0:
                                    CategoryBoxOption(**ob).save()

            else:
                category = Category.objects(name=obj["name"]).first()
                category.update(**obj)
                proxy = requests.get(
                    f"https://api.printdeal.com/api/products/{cat['sku']}/attributes",
                    data={},
                    headers={
                        "API-Secret": "Mi4hQt31Pzbab6+aiBJQUFCg7Mo3Tp/AhY4tcohVVIcqcxL7",
                        "User-ID": "0ac967eb-1726-4f4d-9cab-d7be7183a78d",
                        "accept": "application/vnd.printdeal-api.v2"
                    }
                )

                for k in proxy.json():
                    box = {
                        "name": k,
                        "display_name": generate_display_names(k),
                        "system_key": k,
                        "description": "",
                        "tenant_id": "",
                        "tenant_name": "",
                        "sku": str(uuid.uuid4())
                    }
                    if Box.objects(slug=slugify(k, to_lower=True)).count() == 0:

                        box_info = Box(**box).save()
                        box_info.update(add_to_set__categories=category)
                        box_info.update(**box)
                    else:
                        box_info = Box.objects(slug=slugify(k, to_lower=True)).first()
                        box_info.update(add_to_set__categories=category)
                        box_info.update(**box)

                    for v in proxy.json()[k]:
                        if isinstance(v, str):
                            obj = {
                                "name": v,
                                "display_name": generate_display_names(v),
                                "system_key": slugify(v, to_lower=True),
                                "sku": str(uuid.uuid4())
                            }
                            if Option.objects(slug=slugify(v, to_lower=True)).count() == 0:

                                option = Option(**obj).save()

                                ob = {
                                    "category": category,
                                    "box": box_info,
                                    "option": option
                                }
                                if CategoryBoxOption.objects(**ob).count() == 0:
                                    CategoryBoxOption(**ob).save()

                            else:
                                option = Option.objects(slug=slugify(v, to_lower=True)).first()
                                ob = {
                                    "category": category,
                                    "box": box_info,
                                    "option": option
                                }
                                option.update(**obj)
                                if CategoryBoxOption.objects(**ob).count() == 0:
                                    CategoryBoxOption(**ob).save()

                        elif isinstance(v, int):
                            pass

                        else:
                            x = v['unitOfMeasure'] if 'unitOfMeasure' in v else None
                            obj = {
                                "name": str(v['maximum']) + "-" + str(v['minimum']),
                                "display_name": generate_display_names(str(v['maximum']) + "-" + str(v['minimum'])),
                                "unit": x,
                                "maximum_width": v['maximum'],
                                "minimum_width": v['minimum'],
                                "incremental_by": v['increment'],
                                "information": json.dumps(v),
                                "input_type": "number",
                                "sku": str(uuid.uuid4())
                            }
                            if Option.objects(
                                    slug=slugify(str(v['maximum']) + "-" + str(v['minimum']),
                                                 to_lower=True)).count() == 0:

                                option = Option(**obj).save()

                                ob = {
                                    "category": category,
                                    "box": box_info,
                                    "option": option
                                }
                                if CategoryBoxOption.objects(**ob).count() == 0:
                                    CategoryBoxOption(**ob).save()

                            else:
                                option = Option.objects(slug=slugify(
                                    str(v['maximum']) + "-" + str(v['minimum']), to_lower=True)).first()
                                ob = {
                                    "category": category,
                                    "box": box_info,
                                    "option": option
                                }
                                option.update(**obj)
                                if CategoryBoxOption.objects(**ob).count() == 0:
                                    CategoryBoxOption(**ob).save()

        return {
            "status": 200,
            "data": "data has been inserted"
        }, 200
