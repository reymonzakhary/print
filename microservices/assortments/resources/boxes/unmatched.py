from flask import Response, request, jsonify
from flask_restful import Resource
from models.unmatchedBox import UnmatchedBox
from bson import ObjectId


##############################
#   handel index and store  #
#############################
class UnmatchedBoxesApi(Resource):
    def get(self):
        unmatchedBoxes = UnmatchedBox.objects.to_json()
        return Response(unmatchedBoxes, mimetype="application/json", status=200)


class UnmatchedBoxesDeleteApi(Resource):
    def delete(self, box):
        try:
            if UnmatchedBox.objects(id=ObjectId(box)).delete():
                return jsonify({
                    'message': "Unmatched box has been deleted successfully.",
                    'status': 200
                })

            return jsonify({
                'message': f"Unmatched box with id {box} was not found!",
                'status': 404
            })
        except Exception as e:
            error = str(e)
            return jsonify({
                'message': f"{error}",
                'status': 422
            })
