from flask import Response, request, jsonify
from flask_restful import Resource
from models.matchedCategory import MatchedCategory
from bson.json_util import dumps
import json

##############################
#   handel index and store  #
#############################
class MatchedCategoriesApi(Resource):
    def get(self):
        matched_categories = MatchedCategory.objects.aggregate([
            {
                "$lookup": {
                    "from": "categories",  # Tag collection database name
                    "foreignField": "_id",  # Primary key of the Tag collection
                    "localField": "category",  # Reference field
                    "as": "category",
                },
            },
        ])

        valid_docs = []
        to_delete_ids = []

        for doc in matched_categories:
            if not doc.get("category"):
                to_delete_ids.append(doc["_id"])
            else:
                valid_docs.append(doc)

        if to_delete_ids:
            MatchedCategory.objects(id__in=to_delete_ids).delete()

        return json.loads(dumps(valid_docs))
