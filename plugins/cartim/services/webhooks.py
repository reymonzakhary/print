from flask import request, jsonify
from flask_restful import Resource


class OrderHook(Resource):
    def post(self):
        payload = request.get_json(force=True)
        return jsonify({
            'data': payload,
            'message': 'Webhook placeholder',
            'status': 501
        }), 501




