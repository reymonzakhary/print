import os
from flask import request, jsonify
from flask_restful import Resource
import requests
from services.sync import get_header


class Orders(Resource):
    """Orders endpoint for de-groot"""
    DEFAULT_BASE_URL = os.getenv('DEGROOT_API_BASE', 'https://api.grootsgedrukt.nl')

    def get(self, uuid: str = None):
        # Resolve tenant context: query param, then JSON body (even for GET), then header
        tenant_id = request.args.get('tenant_id')
        if not tenant_id:
            body = request.get_json(silent=True) or {}
            tenant_id = body.get('tenant_id')
        if not tenant_id:
            tenant_id = request.headers.get('X-Tenant-Id')
        
        if not tenant_id:
            return jsonify({
                'data': [],
                'message': "Missing 'tenant_id' (header X-Tenant-Id or query param)",
                'status': 422
            })

        secrets = get_header(tenant_id)
        base_url = secrets.get('url') or self.DEFAULT_BASE_URL
        headers = secrets.get('header', {})

        try:
            # Forward query params except control fields
            params = dict(request.args)
            params.pop('tenant_id', None)
            
            if uuid:
                # Get specific order
                url = f"{base_url}/orders/{uuid}"
                response = requests.get(url, headers=headers, params=params, timeout=30)
            else:
                # Get all orders
                url = f"{base_url}/orders"
                response = requests.get(url, headers=headers, params=params, timeout=30)
            
            response.raise_for_status()
            data = response.json()
            
            return jsonify({
                'data': data,
                'message': 'Orders retrieved successfully',
                'status': 200
            })
            
        except requests.RequestException as e:
            return jsonify({
                'data': [],
                'message': f'Failed to fetch orders: {str(e)}',
                'status': 400
            })


class ExternalOrder(Resource):
    """External order endpoint for de-groot"""
    
    def post(self):
        try:
            payload = request.get_json()
            tenant_id = payload.get('tenant_id')
            
            if not tenant_id:
                return {
                    "data": [],
                    "message": "Missing tenant_id",
                    "status": 422
                }
            
            secrets = get_header(tenant_id)
            base_url = secrets.get('url')
            headers = secrets.get('header', {})
            
            if not base_url:
                return {
                    "data": [],
                    "message": "Missing API URL configuration",
                    "status": 422
                }
            
            # Forward the order request to de-groot API
            order_url = f"{base_url}/orders"
            response = requests.post(order_url, json=payload, headers=headers, timeout=60)
            
            response.raise_for_status()
            order_data = response.json()
            
            return {
                "data": order_data,
                "message": "Order created successfully",
                "status": 201
            }
            
        except requests.RequestException as e:
            return {
                "data": [],
                "message": f"Failed to create order: {str(e)}",
                "status": 400
            }
        except Exception as e:
            return {
                "data": [],
                "message": f"Unexpected error: {str(e)}",
                "status": 500
            }