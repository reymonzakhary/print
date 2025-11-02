from flask import request, jsonify
from flask_restful import Resource
import requests
import os

class OrderHook(Resource):
    """
    Webhook handler to receive events from Drukwerkdeal/Printdeal.
    - Expects POST JSON
    - Supports events: order.created, orderline.status.updated (extend as needed).
    - Forwards normalized payload to internal services, then returns unified response
    """
    ORDER_SERVICE = os.getenv('ORDER_SERVICE', None)
    ORDER_LINE_SERVICE = os.getenv('ORDER_LINE_SERVICE', None)

    def post(self):
        payload = request.get_json(force=True)
        headers_in = dict(request.headers)

        event_type = payload.get('type') or payload.get('event') or payload.get('event_type')
        data = payload.get('data') if isinstance(payload.get('data'), dict) else payload.get('data')

        if not event_type:
            return jsonify({
                'data': payload or [],
                'message': "Unknown event (missing 'type'/'event')",
                'status': 202
            })

        # No token verification: Drukwerkdeal webhook deliveries do not include a token.

        try:
            if event_type == 'order.created':
                return self._handle_order(data=data or payload, headers=headers_in, event_type=event_type)
            if event_type == 'orderline.status.updated':
                return self._handle_orderline_status(data=data or payload, headers=headers_in, event_type=event_type)

            # Unknown but accepted for idempotency
            return jsonify({
                'data': payload or [],
                'message': f"Unhandled event: {event_type}",
                'status': 202
            })
        except Exception as exc:
            return jsonify({
                'data': {'error': str(exc)},
                'message': 'Webhook processing failed',
                'status': 500
            })

    # ————— Handlers ————— #
    def _handle_order(self, data: dict, headers: dict, event_type: str) -> dict:
        """
            Normalize & forward order to gateway service
        """
        order_uuid = data.get('uuid')

        normalized = {
            'event': event_type,
            'raw': data.get('data'),
            'uuid': order_uuid,
        }

        if not self.ORDER_SERVICE:
            raise RuntimeError('ORDER_SERVICE is not configured')

        resp = requests.post(self.ORDER_SERVICE, json=normalized, headers=headers, timeout=15)
        resp.raise_for_status()

        return jsonify({
            'data': resp.json(),
            'message': 'Order received',
            'status': 200,
            'errors': []
        })

    def _handle_orderline_status(self, data: dict, headers: dict, event_type: str) -> dict:
        """
            Normalize & forward orderline status updates
        """
        orderline_uuid = (data or {}).get('uuid') or (data or {}).get('orderline_uuid')
        status = (data or {}).get('status') or (data or {}).get('orderline_status')

        normalized = {
            'event': event_type,
            'uuid': orderline_uuid,
            'status': status,
            'raw': data,
        }

        if not self.ORDER_LINE_SERVICE:
            raise RuntimeError('ORDER_LINE_SERVICE is not configured')

        resp = requests.post(self.ORDER_LINE_SERVICE, json=normalized, headers=headers, timeout=15)
        resp.raise_for_status()

        return jsonify({
            'data': resp.json(),
            'message': 'Orderline status received',
            'status': 200,  
            'errors': []
        })
