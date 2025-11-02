from flask_restful import Resource
from flask import request, Response, jsonify
import requests
import json
from services.sync_service import get_header


def _sanitize_for_json(value):
    if value is None:
        return None
    if isinstance(value, (str, int, float, bool)):
        return value
    if isinstance(value, list):
        return [_sanitize_for_json(v) for v in value]
    if isinstance(value, dict):
        return {str(k): _sanitize_for_json(v) for k, v in value.items()}
    # Fallback for non-serializable types (e.g., Response objects)
    try:
        return str(value)
    except Exception:
        return repr(value)

def _extract_message_from_parsed(parsed):
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

class ExternalDWDOrder(Resource):
    @staticmethod
    def post():
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
                data_out = [_sanitize_for_json(parsed)] if parsed is not None else []
                errors = []
            else:
                message = _extract_message_from_parsed(parsed)
                data_out = []
                errors = [_sanitize_for_json(parsed)] if parsed is not None else []

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
            message = _extract_message_from_parsed(parsed)
            data_out = []
            errors = [_sanitize_for_json(parsed)] if parsed is not None else []

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
