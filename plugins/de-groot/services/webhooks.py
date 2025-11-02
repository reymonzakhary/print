from flask import request, jsonify
from flask_restful import Resource
import requests
import os

class OrderHook(Resource):
    """
    Webhook handler to receive events from de-groot.
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

        # No token verification: de-groot webhook deliveries do not include a token.

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
        Handle order.created webhook from de-groot
        """
        try:
            # Forward to internal order service if configured
            if self.ORDER_SERVICE:
                forward_response = requests.post(
                    self.ORDER_SERVICE,
                    json={
                        'event_type': event_type,
                        'data': data,
                        'headers': headers,
                        'source': 'degroot'
                    },
                    timeout=30
                )
                
                if forward_response.status_code == 200:
                    return jsonify({
                        'data': data,
                        'message': 'Order webhook processed successfully',
                        'status': 200
                    })
                else:
                    return jsonify({
                        'data': data,
                        'message': f'Order service returned {forward_response.status_code}',
                        'status': 206  # Partial success
                    })
            else:
                # No order service configured, just acknowledge
                return jsonify({
                    'data': data,
                    'message': 'Order webhook received (no processing)',
                    'status': 200
                })
                
        except requests.RequestException as e:
            return jsonify({
                'data': data,
                'message': f'Failed to forward to order service: {str(e)}',
                'status': 206  # Partial success
            })

    def _handle_orderline_status(self, data: dict, headers: dict, event_type: str) -> dict:
        """
        Handle orderline.status.updated webhook from de-groot
        """
        try:
            # Forward to internal order line service if configured
            if self.ORDER_LINE_SERVICE:
                forward_response = requests.post(
                    self.ORDER_LINE_SERVICE,
                    json={
                        'event_type': event_type,
                        'data': data,
                        'headers': headers,
                        'source': 'degroot'
                    },
                    timeout=30
                )
                
                if forward_response.status_code == 200:
                    return jsonify({
                        'data': data,
                        'message': 'Order line status webhook processed successfully',
                        'status': 200
                    })
                else:
                    return jsonify({
                        'data': data,
                        'message': f'Order line service returned {forward_response.status_code}',
                        'status': 206  # Partial success
                    })
            else:
                # No order line service configured, just acknowledge
                return jsonify({
                    'data': data,
                    'message': 'Order line status webhook received (no processing)',
                    'status': 200
                })
                
        except requests.RequestException as e:
            return jsonify({
                'data': data,
                'message': f'Failed to forward to order line service: {str(e)}',
                'status': 206  # Partial success
            })