from flask import Response, request, jsonify
from models.box import Box
from models.option import Option
from flask_restful import Resource
import json
import math
from bson.json_util import dumps


##############################
#   handel index and store  #
#############################
class BoxOptionsApi(Resource):
    def get(self, slug):

        page = 1 if request.args.get('page') is None or request.args.get('page') == "" else int(
            request.args.get('page'))
        per_page = 10 if request.args.get('per_page') is None or request.args.get('per_page') == "" else int(
            request.args.get('per_page'))
        skip = 0 if page == 1 else (per_page * page) - per_page

        supplier = request.args.get('supplier')
        tenant = request.args.get('tenant')

        option_relation = Box.objects(slug=slug).aggregate([
            {
                "$lookup": {
                    "from": "category_box_options",
                    "foreignField": "box",  # Primary key of the Tag collection
                    "localField": "_id",  # Reference field
                    "as": "options",
                },
            },
            {"$project": {"categories": 0, "options.category": 0, "options.box": 0}},
            {"$unwind": '$options'},
            {
                "$group": {"_id": "$_id", "options": {"$addToSet": "$options.option"}}
            },
            {
                "$facet": {
                    "data": [
                        {"$skip": skip},
                        {"$limit": per_page}
                    ],
                    "count": [{
                        "$count": "count"
                    }]
                },
            }
        ])

        option_relation = json.loads(dumps(*option_relation))
        items = json.loads(dumps(*option_relation['data']))
        items = json.loads(dumps(items['options']))

        count = 0 if len(option_relation['count']) == 0 else json.loads(dumps(*option_relation['count']))['count']
        last_page = math.ceil(count / per_page)
        next_page = None if last_page <= page else page + 1
        first_page = 1

        options = []
        for optRelation in items:
            if '$oid' in optRelation:
                option = optRelation["$oid"]
                #                 return json.loads(dumps(option))
                option = Option.objects(id=option).aggregate([
                    {
                        "$lookup": {
                            "from": "supplier_options",  # Tag collection database name
                            "foreignField": "linked",  # Primary key of the Tag collection
                            "localField": "_id",  # Reference field
                            "as": "suppliers",
                        },
                    },
                    {
                        "$lookup": {
                            "from": "matched_options",  # Tag collection database name
                            "foreignField": "option",  # Primary key of the Tag collection
                            "localField": "_id",  # Reference field
                            "as": "matches",
                        }
                    },
                    {
                        "$lookup": {
                            "from": "options",  # Tag collection database name
                            "foreignField": "children",  # Primary key of the Tag collection
                            "localField": "_id",  # Reference field
                            "as": "children",
                        }
                    },

                    {
                        "$lookup": {
                            "from": "supplier_options",  # Tag collection database name
                            "let": {"id": "$_id"},
                            "pipeline": [{
                                "$match": {
                                    "$expr":
                                        {
                                            "$and": [
                                                {"$eq": ["$$id", "$linked"]},
                                                {"$eq": ["$tenant_id", tenant]}
                                            ]
                                        }
                                },
                            }],
                            "as": "me",
                        }
                    },
                    {
                        "$project": {"_id": 1, "children.children": 0}
                    },
                ])
                options.append(json.loads(dumps(*option)))
        # options = dict(sorted(options, key=lambda item: item[1]))

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
                   "data": options,
               }, 200


class BoxOptionApi(Resource):
    def get(self, box_slug, option_slug):
        option = Option.objects.get_or_404(slug=option_slug)
        return jsonify({"data": option,
                        "status": 200,
                        "message": ""})
