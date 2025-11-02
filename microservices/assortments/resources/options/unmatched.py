from flask import Response, jsonify
from flask_restful import Resource
from models.unmatchedOption import UnmatchedOption
from bson import ObjectId


# from models.unmatchedOption import UnmatchedOption
##############################
#   handel index and store  #
#############################
class UnmatchedOptionsApi(Resource):
    def get(self):
        unmatchedOptions = UnmatchedOption.objects.to_json()
        return Response(unmatchedOptions, mimetype="application/json", status=200)


class UnmatchedOptionsDeleteApi(Resource):
    def delete(self, option):
        try:
            if UnmatchedOption.objects(id=ObjectId(option)).delete():
                return jsonify({
                    'message': "Unmatched option has been deleted successfully.",
                    'status': 200
                })

            return jsonify({
                'message': f"Unmatched option with id {option} was not found!",
                'status': 404
            })
        except Exception as e:
            error = str(e)
            return jsonify({
                'message': f"{error}",
                'status': 422
            })
