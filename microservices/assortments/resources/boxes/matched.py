from flask import Response, request, jsonify
from flask_restful import Resource
from bson.json_util import dumps
from models.matchedBox import MatchedBox
import json


##############################
#   handel index and store  #
#############################
class MatchedBoxesApi(Resource):
    def get(self):
        matched_boxes = MatchedBox.objects.aggregate([
            {
                "$lookup": {
                    "from": "boxes",  # Tag collection database name
                    "foreignField": "_id",  # Primary key of the Tag collection
                    "localField": "box",  # Reference field
                    "as": "box",
                },
            },
        ])

        valid_docs = []
        to_delete_ids = []

        for doc in matched_boxes:
            if not doc.get("box"):
                to_delete_ids.append(doc["_id"])
            else:
                valid_docs.append(doc)

        if to_delete_ids:
            MatchedBox.objects(id__in=to_delete_ids).delete()

        return json.loads(dumps(valid_docs))
