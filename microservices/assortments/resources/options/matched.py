from flask import Response
from flask_restful import Resource
from models.matchedOption import MatchedOption
from bson.json_util import dumps
import json


# from models.unmatchedOption import UnmatchedOption
##############################
#   handel index and store  #
#############################
class MatchedOptionsApi(Resource):
    def get(self):
        matched_options = MatchedOption.objects.aggregate([
            {
                "$lookup": {
                    "from": "options",  # Tag collection database name
                    "foreignField": "_id",  # Primary key of the Tag collection
                    "localField": "option",  # Reference field
                    "as": "option",
                },
            },
        ])

        valid_docs = []
        to_delete_ids = []

        for doc in matched_options:
            if not doc.get("option"):
                to_delete_ids.append(doc["_id"])
            else:
                valid_docs.append(doc)

        if to_delete_ids:
            MatchedOption.objects(id__in=to_delete_ids).delete()

        return json.loads(dumps(valid_docs))