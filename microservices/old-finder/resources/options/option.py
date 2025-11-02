from flask import request, jsonify
from models.option import Option
from models.supplierOption import SupplierOption
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource
from bson.json_util import dumps
import json
import math


class OptionsSearchApi(Resource):
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
        sortDir = "1" if request.args.get('sort_dir') is None or request.args.get('sort_dir') == "" or request.args.get(
            'sort_dir') == "asc" else "-1"
        supplierOption = SupplierOption.objects.aggregate([

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
        catIds = []
        for catid in supplierOption:
            catIds.append(catid['_id'])
        # return json.loads(dumps(catIds))
        # data = request.getArgs

        found = Option.objects.aggregate([
            {
                "$facet": {
                    "data": [
                        {
                            "$match": {
                                "$or": [
                                    {"_id": {"$in": catIds}},
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
                                    "from": "supplier_options",
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
                        {
                            "$limit": per_page
                        },
                        {
                            "$skip": skip
                        },
                    ],
                    "total": [{"$count": "total"}]
                }
            }
        ])
        data = json.loads(dumps(*found))
        items = data['data']
        count = 0 if len(data['total']) == 0 else json.loads(dumps(*data['total']))['total']
        last_page = math.ceil(count / per_page)
        next_page = None if last_page <= page else page + 1
        first_page = 1
        if len(data['total']) == 0:
            count = 0
        else:
            count = json.loads(dumps(*data['total']))['total']

        return {
            "data": items,
            "pagination": {
                "current_page": page,
                "first_page_url": "/?page=" + str(first_page),
                "last_page_url": "/?page=" + str(last_page),
                "prev_page_url": "/?page=" + str(page - 1) if page > 1 else None,
                "next_page_url": "/?page=" + str(next_page) if next_page else None,
                "from": skip,
                "to": skip + per_page,
                "last_page": last_page,
                "per_page": per_page,
                "total": count
            }

        }
