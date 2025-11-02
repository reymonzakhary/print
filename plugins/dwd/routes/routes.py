from external.external_dwd_supplier import ExternalDWDSupplier
from services.sync_service import ValidatePair, GetPrice, GetCategory, GetCategories, GetSecrets
from services.webhooks import OrderHook
from services.orders import Orders

def initialize_routes(api):
    api.add_resource(GetCategories, "/categories")
    api.add_resource(GetSecrets, "/secrets")
    api.add_resource(ExternalDWDSupplier, "/import")
    api.add_resource(ValidatePair, "/validate-pair")
    api.add_resource(GetPrice, "/get-price")
    api.add_resource(GetCategory, "/sync")
    api.add_resource(OrderHook, "/webhooks")
    api.add_resource(Orders, "/orders", "/orders/<string:uuid>")

