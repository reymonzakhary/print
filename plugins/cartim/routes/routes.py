from flask_restful import Resource
from services.sync import Sync, Import, GetPrice, ValidatePair, GetCategories, GetSecrets
from services.orders import Orders, ExternalOrder
from services.webhooks import OrderHook


def initialize_routes(api):
    api.add_resource(Health, "/health")
    api.add_resource(Sync, "/sync")
    api.add_resource(Import, "/import")
    api.add_resource(GetPrice, "/get-price")
    api.add_resource(ValidatePair, "/validate-pair")
    api.add_resource(GetCategories, "/categories")
    api.add_resource(GetSecrets, "/secrets")
    api.add_resource(ExternalOrder, "/order")
    api.add_resource(Orders, "/orders", "/orders/<string:uuid>")
    api.add_resource(OrderHook, "/webhooks")


class Health(Resource):
    def get(self):
        return {"status": "ok"}


