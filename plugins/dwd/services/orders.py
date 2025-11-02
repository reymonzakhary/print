from decimal import Context
import os
import time
from time import sleep

from flask import request, jsonify, Response
from flask_restful import Resource
import requests
from services.sync_service import get_header
import json


class Orders(Resource):
    DEFAULT_BASE_URL = os.getenv('DWD_API_BASE', 'https://api.printdeal.com/api')

    def get(self, uuid: str = None):
        tenant_id = request.args.get('tenant_id')
        if not tenant_id:
            body = request.get_json(silent=True) or {}
            tenant_id = body.get('tenant_id')
        if not tenant_id:
            return jsonify({
                'data': [],
                'message': "Missing 'tenant_id' (query param or body)",
                'status': 422
            })

        secrets = get_header(tenant_id)
        base_url = secrets.get('url') or self.DEFAULT_BASE_URL
        headers = secrets.get('header', {})
        orders = []
        try:
            # Forward query params except control fields
            params = {k: v for k, v in request.args.items() if k.lower() not in {'tenant_id', 'uuid'}}

            # Allow uuid from path, query, or body
            if not uuid:
                uuid = request.args.get('uuid') or (body.get('uuid') if 'body' in locals() and isinstance(body, dict) else None)

            if uuid:
                url = f"{base_url.rstrip('/')}/orders/{uuid}"
            else:
                url = f"{base_url.rstrip('/')}/orders/"

            resp = requests.get(url, headers=headers, params=params, timeout=30)
            resp.raise_for_status()
            response_data = resp.json()
            if response_data and resp.status_code >= 200 and resp.status_code <= 299:
                if uuid:
                    projected = self._project_order_fields(resp.json())
                    orders.append(projected)
                else:
                    orders = response_data['orders']
            projected = self._project_order_fields(resp.json())
            return jsonify({
                'data': orders,
                'message': 'Order details' if uuid else 'Orders list',
                'status': 200,
                'errors': []
            })
        except requests.HTTPError as http_err:
            return jsonify({
                'data': getattr(http_err.response, 'json', lambda: {})(),
                'message': 'Upstream error from Drukwerkdeal',
                'status': http_err.response.status_code if http_err.response else 502
            })
        except Exception as exc:
            return jsonify({
                'data': {'error': str(exc)},
                'message': 'Failed to fetch orders',
                'status': 500
            })

    def delete(self, uuid: str = None):
        # Resolve tenant context: query param, then JSON body, then header
        tenant_id = request.args.get('tenant_id')
        if not tenant_id:
            body = request.get_json(silent=True) or {}
            tenant_id = body.get('tenant_id')
        if not tenant_id:
            return jsonify({
                'data': [],
                'message': "Missing 'tenant_id' (query param or body)",
                'status': 422
            })

        # Determine reason
        if 'body' not in locals():
            body = request.get_json(silent=True) or {}
        reason = body.get('reason') or request.args.get('reason')
        if not reason:
            return jsonify({
                'data': [],
                'message': "Missing 'reason' in request body or query",
                'status': 422
            })

        # Determine order UUID
        if not uuid:
            uuid = request.args.get('uuid') or body.get('uuid')
        if not uuid:
            return jsonify({
                'data': [],
                'message': "Missing 'uuid' (path param, query, or body)",
                'status': 422
            })

        secrets = get_header(tenant_id)
        base_url = secrets.get('url') or self.DEFAULT_BASE_URL
        headers = secrets.get('header', {})

        try:
            url = f"{base_url.rstrip('/')}/orders/{uuid}"
            resp = requests.delete(url, headers=headers, json={"reason": reason}, timeout=30)
            resp.raise_for_status()

            return jsonify({
                'data': resp.json() if resp.content else {},
                'message': 'Order cancelled',
                'status': 200,
                'errors': []
            })
        except requests.HTTPError as http_err:
            return jsonify({
                'data': getattr(http_err.response, 'json', lambda: {})(),
                'message': 'Upstream error from Drukwerkdeal',
                'status': http_err.response.status_code if http_err.response else 502
            })
        except Exception as exc:
            return jsonify({
                'data': {'error': str(exc)},
                'message': 'Failed to cancel order',
                'status': 500
            })


    def post(self):
        data = request.get_json()

        order_payload = data.get("order")
        tenant_id = data.get("tenant_id")

        if not all([order_payload, tenant_id]):
            return {
                "data": [],
                "message": "Missing required fields: 'order' or 'tenant_id'",
                "status": 422,
                "errors": ["validation"]
            }, 422

        secrets = get_header(tenant_id)
        base_url = secrets.get("url")
        headers = secrets.get("header", {})
        headers["Content-Type"] = "application/json"
        headers["accept"] = "application/vnd.printdeal-api.v2+json"

        # Validate base_url before constructing the full URL
        if not base_url:
            return {
                "data": [],
                "message": "Missing or invalid base URL in tenant configuration",
                "status": 422,
                "errors": [{"message": "configuration"}]
            }, 200

        # Ensure base_url is properly formatted
        if not base_url.startswith(('http://', 'https://')):
            base_url = f"https://{base_url}"
        
        url = f"{base_url.rstrip('/')}/orders"

        try:
            response = requests.post(url, json=order_payload, headers=headers)

            status_code = response.status_code
            parsed = None
            try:
                parsed = json.loads(response.text)
            except Exception:
                parsed = response.text

            # Simplified behavior: any 2xx => put body in data, else put body in errors
            if 200 <= status_code < 300:
                message = "OK"
                data_out = [self._sanitize_for_json(parsed)] if parsed is not None else []
                errors = []
                uuid = parsed.get('uuid') or None
                if uuid:
                    # Try up to 3 times over ~60 seconds (about 20s between attempts)
                    attempts = 3
                    for i in range(attempts):
                        try:
                            print("sending tenant & uuid =>", tenant_id, uuid, "attempt", i + 1)
                            order_details_response = self._fetch_order(tenant_id=tenant_id, uuid=str(uuid))
                            if order_details_response:
                                projected = self._project_order_fields(order_details_response)
                                return {
                                    "data": [projected],
                                    "message": message,
                                    "status": status_code,
                                    "errors": []
                                }, 200
                        except Exception as e:
                            print(f"Failed to fetch order details (attempt {i+1}/3): {e}")
                            print(f"Error type: {type(e)}")
                            print(f"Error details: {str(e)}")
                        # Sleep between attempts except after the last one
                        if i < attempts - 1:
                            time.sleep(20)

            else:
                message = self._extract_message_from_parsed(parsed)
                data_out = []
                errors = [self._sanitize_for_json(parsed)] if parsed is not None else []

            return {
                "data": data_out,
                "message": message,
                "status": status_code,
                "errors": errors
            }, 200

        except requests.exceptions.HTTPError as http_err:
            resp = getattr(http_err, 'response', None)
            status_code = resp.status_code if resp is not None else 422
            try:
                parsed = json.loads(resp.text) if resp is not None else None
            except Exception:
                parsed = resp.text if resp is not None else None

            # Treat HTTPError as error path
            message = self._extract_message_from_parsed(parsed)
            data_out = []
            errors = [self._sanitize_for_json(parsed)] if parsed is not None else []

            return {
                "data": data_out,
                "message": message,
                "status": status_code,
                "errors": errors
            }, 200
        except Exception as e:
            return {
                "data": [],
                "message": "Something went wrong",
                "status": 422,
                "errors": [str(e)]
            }, 422

    def _sanitize_for_json(self, value):
        if value is None:
            return None
        if isinstance(value, (str, int, float, bool)):
            return value
        if isinstance(value, list):
            return [self._sanitize_for_json(v) for v in value]
        if isinstance(value, dict):
            return {str(k): self._sanitize_for_json(v) for k, v in value.items()}
        # Fallback for non-serializable types (e.g., Response objects)
        try:
            return str(value)
        except Exception:
            return repr(value)

    def _extract_message_from_parsed(self, parsed):
        """Return a concise message string from a parsed response body.
        - If parsed is a dict and contains a list under key "message", join items.
        - If parsed is a dict and contains a string under key "message", return it.
        - If parsed is a string, return it.
        - Otherwise return a generic failure message.
        """
        try:
            if isinstance(parsed, dict) and "message" in parsed:
                message_value = parsed.get("message")
                if isinstance(message_value, list):
                    # Keep only string-like items and join with separator
                    parts = [str(p) for p in message_value if p is not None]
                    return " | ".join(parts) if parts else "Request failed"
                if isinstance(message_value, (str, int, float, bool)):
                    return str(message_value)
            if isinstance(parsed, (str, int, float, bool)):
                return str(parsed)
        except Exception:
            pass
        return "Request failed"

    def _project_order_fields(self, payload):
        """Return only selected fields from fetched order payload.
        Keeps: uuid, number, id, status, date, reference, testOrder, total.
        Accepts shapes: {data: [ {...} ]} or single dict.
        """
        keep_keys = {"uuid", "number", "id", "status", "date", "reference", "testOrder", "total"}
        def project_one(item):
            if not isinstance(item, dict):
                return item
            return {k: item.get(k) for k in keep_keys if k in item}

        try:
            if isinstance(payload, dict) and isinstance(payload.get("data"), list):
                return {"data": [project_one(x) for x in payload.get("data", [])]}
            if isinstance(payload, list):
                return [project_one(x) for x in payload]
            if isinstance(payload, dict):
                return project_one(payload)
        except Exception:
            return payload
        return payload

    def _fetch_order(self, tenant_id: str, uuid: str = None, params: dict = None):
        secrets = get_header(tenant_id)
        base_url = secrets.get('url') or self.DEFAULT_BASE_URL
        headers = secrets.get('header', {})
        headers["accept"] = "application/vnd.printdeal-api.v2+json"

        if uuid:
            url = f"{base_url.rstrip('/')}/orders/{uuid}"
        else:
            url = f"{base_url.rstrip('/')}/orders/"
        
        resp = requests.get(url, headers=headers, params=params or {}, timeout=60)
        
        resp.raise_for_status()
        return resp.json()


