import os
from flask import request, jsonify
from flask_restful import Resource


class Orders(Resource):
    def get(self, uuid: str = None):
        return jsonify({
            'data': [],
            'message': 'Orders placeholder',
            'status': 501
        }), 501


class ExternalOrder(Resource):
    def post(self):
        data = request.get_json(force=True)
        return jsonify({
            'data': [],
            'message': 'Order submit placeholder',
            'status': 501
        }), 501




