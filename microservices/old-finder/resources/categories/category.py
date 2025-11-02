from flask import request, jsonify
from models.category import Category
from models.categoryBoxOption import CategoryBoxOption
from models.supplierCategory import SupplierCategory
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource
from bson.json_util import dumps
import json
import math


class CategoriesSearchApi(Resource):
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
        supplierCategory = SupplierCategory.objects.aggregate([

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
        for catid in supplierCategory:
            catIds.append(catid['_id'])
        # return json.loads(dumps(catIds))
        # data = request.getArgs
        found = Category.objects.aggregate([
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
                        "from": "supplier_categories",
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


class CategoriesApi(Resource):

    def get(self):

        page = 1 if request.args.get('page') is None or request.args.get('page') == "" else int(
            request.args.get('page'))
        per_page = 50 if request.args.get('per_page') is None or request.args.get('per_page') == "" else int(
            request.args.get('per_page'))
        skip = 0 if page == 1 else (per_page * page) - per_page
        categories = Category.objects.aggregate([
            {
                "$facet": {
                    "data": [
                        {"$unset": "_id"},
                        {"$skip": skip},
                        {"$limit": per_page},
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
        return {
                   "data": items,
                   "page": page,
                   "per_page": per_page,
                   "total": count,
                   "lastPage": last_page,
                   "status": 200,
               }, 200


class CategoryApi(Resource):
    def get(self, slug):
        category = Category.objects(slug=slug).aggregate([
            {
                "$lookup":
                    {
                        "from": "boxes",
                        # "localField": "_id",
                        # "foreignField": "categories",
                        "let": {"category": "$_id"},
                        "pipeline": [
                            {"$match":
                                {"$expr":
                                    {
                                        "$and":
                                            [
                                                {"$in": ["$$category", "$categories"]},
                                            ],
                                    }
                                },

                            },
                            {"$match":
                                {"slug":
                                    {
                                        "$nin":
                                            [
                                                "printing-process",
                                                "delivery-type",
                                                "quantity"
                                            ]
                                    }
                                }
                            },

                            {
                                "$lookup": {
                                    "from": "category_box_options",
                                    "let": {"box": "$_id"},
                                    "pipeline": [
                                        {"$match":
                                            {"$expr":
                                                {
                                                    "$and":
                                                        [
                                                            {"$eq": ["$$category", "$category"]},
                                                            {"$eq": ["$$box", "$box"]},
                                                        ],
                                                }
                                            },
                                        },
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
                                            }},
                                        {"$project": {
                                            "option": {"$arrayElemAt": ["$option", 0]}
                                        }},
                                    ],
                                    "as": "options"
                                }},
                        ],
                        "as": "boxes",

                    },
            },
            {"$project": {"boxes.categories": 0,
                          "boxes.options._id": 0,
                          "boxes.options.category": 0,
                          "boxes.options.box": 0,
                          #   "countryInfo": { "$arrayElemAt": [ "$countryInfo", 0 ] }
                          }},

        ])
        res = json.loads(dumps(*category))
        boxes = []
        for box in res['boxes']:
            options = []
            for option in box['options']:
                options.append(option['option'])
            box["options"] = options
            boxes.append(box)
        return res
