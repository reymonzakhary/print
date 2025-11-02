from flask import Response, request, jsonify
from flask_restful import Resource
from models.unmatchedCategory import UnmatchedCategory
from bson import ObjectId


##############################
#   handel index and store  #
#############################
class UnmatchedCategoriesApi(Resource):
    def get(self):
        unmatchedCategories = UnmatchedCategory.objects.to_json()
        return Response(unmatchedCategories, mimetype="application/json", status=200)


class UnmatchedCategoriesDeleteApi(Resource):
    def delete(self, category):
        try:
            if UnmatchedCategory.objects(id=ObjectId(category)).delete():
                return jsonify({
                    'message': "Unmatched category has been deleted successfully.",
                    'status': 200
                })

            return jsonify({
                'message': f"Unmatched category with id {category} was not found!",
                'status': 404
            })
        except Exception as e:
            error = str(e)
            return jsonify({
                'message': f"{error}",
                'status': 422
            })
