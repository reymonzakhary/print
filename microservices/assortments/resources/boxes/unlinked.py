from flask import Response, request, jsonify
from flask_restful import Resource
from bson.json_util import dumps
import json
from models.supplierBox import SupplierBox
import math


##############################
#   handel index and store  #
#############################
class UnlinkedBoxesApi(Resource):

    @staticmethod
    def get():
        # Get pagination and filter parameters
        page = 1 if not request.args.get('page') else int(request.args.get('page'))
        per_page = 10 if not request.args.get('per_page') else int(request.args.get('per_page'))
        skip = (page - 1) * per_page
        filters = "" if not request.args.get('filter') else request.args.get('filter')
        sort_by = "name" if not request.args.get('sort_by') else request.args.get('sort_by')
        sort_dir = "" if not request.args.get('sort_dir') or request.args.get('sort_dir') == "asc" else "-"

        # Aggregation pipeline
        boxes = SupplierBox.objects.order_by(sort_dir + sort_by).aggregate([
            {
                "$match": {
                    "linked": None,  # Filter boxes where linked is None
                    "name": {"$regex": filters, "$options": 'i'}  # Case-insensitive name filter
                }
            },

            {
                "$lookup": {
                    "from": "matchedBoxes",
                    "let": {"tenant_id": "$tenant_id", "slug": "$slug"},
                    "pipeline": [
                        {
                            "$match": {
                                "$expr": {
                                    "$and": [
                                        {"$eq": ["$tenant_id", "$$tenant_id"]},
                                        {"$eq": ["$slug", "$$slug"]}
                                    ]
                                }
                            }
                        }
                    ],
                    "as": "matched"
                }
            },
            {
                "$lookup": {
                    "from": "unmatchedBoxes",
                    "let": {"tenant_id": "$tenant_id", "slug": "$slug"},
                    "pipeline": [
                        {
                            "$match": {
                                "$expr": {
                                    "$and": [
                                        {"$eq": ["$tenant_id", "$$tenant_id"]},
                                        {"$eq": ["$slug", "$$slug"]}
                                    ]
                                }
                            }
                        }
                    ],
                    "as": "unmatched"
                }
            },
            {
                "$match": {
                    "matched": {"$size": 0},
                    "unmatched": {"$size": 0}
                }
            },
            {
                "$sort": {
                    sort_by: 1 if sort_dir == "+" else -1
                }
            },
            {
                "$facet": {
                    "data": [
                        {"$skip": skip},
                        {"$limit": per_page},
                    ],
                    "count": [{"$count": "count"}]
                }
            }
        ])

        # Convert cursor to JSON
        boxes = json.loads(dumps(*boxes))
        items = boxes['data']

        # Get total count
        count = boxes['count'][0]['count'] if boxes['count'] else 0

        # Calculate pagination details
        last_page = math.ceil(count / per_page)
        next_page = page + 1 if page < last_page else None
        first_page = 1

        # Return paginated response
        response = {
            "total": count,
            "per_page": per_page,
            "current_page": page,
            "last_page": last_page,
            "first_page_url": f"/?page={first_page}",
            "last_page_url": f"/?page={last_page}",
            "next_page_url": f"/?page={next_page}" if next_page else None,
            "prev_page_url": f"/?page={page - 1}" if page > 1 else None,
            "path": '/',
            "from": skip + 1,
            "to": skip + len(items),
            "data": items,
        }, 200

        return response
