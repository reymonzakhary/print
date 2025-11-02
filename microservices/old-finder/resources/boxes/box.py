from flask import request, jsonify
from models.box import Box
from models.supplierBox import SupplierBox
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource
from bson.json_util import dumps
import json
import math


class BoxesSearchApi(Resource):
    def get(self):
        page = 1 if request.args.get('page') is None or request.args.get('page') == "" else int(
            request.args.get('page'))
        per_page = 10 if request.args.get('per_page') is None or request.args.get('per_page') == "" else int(
            request.args.get('per_page'))
        skip = 0 if page == 1 else (per_page * page) - per_page
        search = "" if request.args.get('search') is None or request.args.get('search') == "" else request.args.get(
            'search')
        sortBy = "name" if request.args.get('sort_by') is None or request.args.get(
            'sort_by') == "" else request.args.get('sort_by')
        sortDir = "" if request.args.get('sort_dir') is None or request.args.get('sort_dir') == "" or request.args.get(
            'sort_dir') == "asc" else "-"
        supplier_box = SupplierBox.objects.aggregate([

            {
                "$match": {
                    "slug": {
                        "$regex": slugify(search, to_lower=True),
                        "$options": 'i'  # case-insensitive
                    },
                    "linked": {
                        "$exists": True
                    }
                },
            },
            {
                "$project": {"linked": 1, "_id": 0}
            },
            {
                "$group": {
                    "_id": "$linked",
                }
            },
        ])
        boxes_id = []
        for box_id in supplier_box:
            boxes_id.append(box_id['_id'])
        # return json.loads(dumps(catIds))
        # data = request.getArgs
        found = Box.objects.aggregate([
            {
                "$match": {
                    "$or": [
                        {"_id": {"$in": boxes_id}},
                        {
                            "slug": {
                                "$regex": slugify(search, to_lower=True),
                                "$options": 'i'  # case-insensitive
                            }
                        }
                    ]
                },

            },
            {
                "$lookup":
                    {
                        "from": "supplier_boxes",
                        "let": {"id": "$_id"},
                        "pipeline": [
                            {"$match":
                                {"$expr":
                                    {
                                        "$and":
                                            [
                                                {"$eq": ["$$id", "$linked"]},
                                            ],
                                    }
                                },
                            },
                        ],
                        "as": "linked",
                    },
            },

        ])
        return json.loads(dumps({"data": found}))
