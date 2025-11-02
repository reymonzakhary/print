import uuid

from flask import Response, request, jsonify
from models.option import Option
from flask_restful import Resource
from bson.json_util import dumps
import json
import math
from models.categoryBoxOption import CategoryBoxOption
from models.supplierOption import SupplierOption
from models.manifest import Manifest
from models.supplierBoops import SupplierBoops
from models.matchedOption import MatchedOption
from slugify import slugify, Slugify, UniqueSlugify

from helper.helper import generate_display_names


##############################
#   handel index and store  #
#############################


class OptionsApi(Resource):
    def get(self):
        page = 1 if request.args.get('page') is None or request.args.get('page') == "" else int(
            request.args.get('page'))
        per_page = 10 if request.args.get('per_page') is None or request.args.get('per_page') == "" else int(
            request.args.get('per_page'))
        skip = 0 if page == 1 else (per_page * page) - per_page
        filter = "" if request.args.get('filter') is None or request.args.get('filter') == "" else request.args.get(
            'filter')

        options = Option.objects.aggregate([
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
                        {
                            "$lookup": {
                                "from": "supplier_options",  # Tag collection database name
                                "foreignField": "linked",  # Primary key of the Tag collection
                                "localField": "_id",  # Reference field
                                "as": "suppliers",
                            },
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
        options = json.loads(dumps(*options))
        items = options['data']
        count = 0 if len(options['count']) == 0 else json.loads(dumps(*options['count']))['count']
        last_page = math.ceil(count / per_page)
        next_page = None if last_page <= page else page + 1
        first_page = 1
        if len(options['count']) == 0:
            count = 0
        else:
            count = json.loads(dumps(*options['count']))['count']
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
        payload = {
            "sort": 1,
            "name": body['name'],
            "display_name": generate_display_names(body['name']),
            "sku": str(uuid.uuid4()),
            "slug": slugify(body['name'], to_lower=True),
            "system_key": slugify(body['name'], to_lower=True),
            "description": "",
            "media": [],
            "tenant_id": "",
            "tenant_name": "",
            "dimension": "2d",
            "dynamic": False,
            "dynamic_keys": [],
            "start_on": 0,
            "end_on": 0,
            "generate": False,
            "unit": "mm",
            "incremental_by": 0,
            "published": True,
            "has_children": False,
            "configure": [],
            "checked": True
        }
        options = Option(**payload).save()
        return jsonify(options)


class OptionApi(Resource):
    def get(self, slug):
        option = Option.objects.aggregate([{
            "$match": {
                "slug": slug
            }
        },
            {
                "$lookup": {
                    "from": "options",  # Tag collection database name
                    "foreignField": "_id",  # Primary key of the Tag collection
                    "localField": "children",  # Reference field
                    "as": "children",
                },
            },
            {
                "$project": {
                    "_id": 0,
                    "children.children": 0,
                    "children._id": 0
                }
            }])
        
        # Convert cursor to list once
        option_data = json.loads(dumps(option))
        
        if not option_data:
            return {
                "message": "The requested URL was not found on the server. If you entered the URL manually please check your spelling and try again.",
                "status": 404,
            }, 404
        else:
            return jsonify({"data": option_data[0],
                            "status": 200,
                            "message": ""})

    def put(self, slug):

        body = request.form.to_dict(flat=True)

        # Convert boolean fields properly
        body["published"] = body.get("published") == "1"
        body["checked"] = body.get("checked") == "1"

        # Rebuild the display_name list
        display_name = []
        index = 0

        while f"display_name[{index}][iso]" in body:
            display_name.append({
                "iso": body[f"display_name[{index}][iso]"],
                "display_name": body[f"display_name[{index}][display_name]"]
            })
            index += 1

        # Normalize keys like "additional[calc_ref]" -> "additional__calc_ref"
        normalized_body = {}
        for k, v in body.items():
            if "[" in k and "]" in k:
                # Turn additional[calc_ref] into additional__calc_ref
                new_key = k.replace("[", "__").replace("]", "")
                normalized_body[new_key] = v
            else:
                normalized_body[k] = v

        # Build the update query (ignore display_name and slug for now)
        update_query = {
            f"set__{k}": v
            for k, v in normalized_body.items()
            if not k.startswith("display_name") and k != "slug"
        }

        # Add display_name update
        update_query["set__display_name"] = display_name

        # Perform the update
        Option.objects(slug=slug).update_one(**update_query)

        return {
            "message": "Option has been updated successfully",
            "status": 200
        }, 200

    def delete(self, slug):

        # check if category has relation
        force = False if request.args.get('force') is None or request.args.get('force') == "0" or request.args.get(
            'force') == "false" else True

        option = Option.objects(slug=slug).first()

        if force:
            linked = {"linked": None}
            SupplierOption.objects(linked=option.id).modify(**linked)
            CategoryBoxOption.objects(option=option.id).delete()
            Manifest._get_collection().update_many(
                {"boops.ops.id": option.id},
                {"$pull": {"boops.$[].ops": {"id": option.id}}}
            )
            SupplierBoops._get_collection().update_many(
                {"boops.ops.linked": option.id},
                {"$set": {"boops.$[].ops.$[op].linked": None}},
                array_filters=[{"op.linked": option.id}]
            )
            MatchedOption.objects(option=option).delete()
            
            option.delete()
            return {
                "data": None,
                "message": "Option has been deleted successfully",
                "status": 200
            }, 200

        if (
            SupplierOption.objects(linked=option.id).count() == 0 and 
            CategoryBoxOption.objects(option=option).count() == 0 and
            Manifest.objects(boops__ops__id=option.id).count() == 0
        ):
            option.delete()
            return {
                "data": None,
                "message": "Option has been deleted successfully",
                "status": 200
            }, 200
        else:
            return {
                "data": None,
                "message": "We can't remove this option because it has a relation with different tables.",
                "status": 422
            }, 200
