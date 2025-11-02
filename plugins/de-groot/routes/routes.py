from flask_restful import Resource
from services.sync import Sync, GetPrice, ValidatePair, GetCategories, GetCategory, GetArticleMetadata, GetSegmentMapping, GetSecrets, TestAuth, GetOptionSegments, ValidateArticlecode, GetExcludes, GetBoopsExcludes, ApplyExcludesByBoops, ResetExcludesByBoops
from services.orders import Orders, ExternalOrder
from services.webhooks import OrderHook
from external.external_degroot_supplier import ExternalDeGrootSupplier


def initialize_routes(api):
    api.add_resource(Health, "/health")
    api.add_resource(GetCategories, "/categories")
    api.add_resource(GetCategory, "/category", "/category/<string:articlenumber>")
    api.add_resource(GetArticleMetadata, "/article-metadata", "/article-metadata/<string:articlenumber>")
    api.add_resource(GetSegmentMapping, "/segment-mapping", "/segment-mapping/<string:articlenumber>")
    api.add_resource(GetOptionSegments, "/option-segments")
    api.add_resource(GetExcludes, "/excludes")
    api.add_resource(GetBoopsExcludes, "/excludes-by-boops")
    api.add_resource(ApplyExcludesByBoops, "/apply-excludes-by-boops")
    api.add_resource(ResetExcludesByBoops, "/reset-excludes-by-boops")
    api.add_resource(ValidateArticlecode, "/validate-articlecode")
    api.add_resource(GetSecrets, "/secrets")
    api.add_resource(TestAuth, "/test-auth")
    api.add_resource(ExternalDeGrootSupplier, "/import")
    api.add_resource(ValidatePair, "/validate-pair")
    api.add_resource(GetPrice, "/get-price")
    api.add_resource(Sync, "/sync")
    api.add_resource(ExternalOrder, "/order")
    api.add_resource(Orders, "/orders", "/orders/<string:uuid>")
    api.add_resource(OrderHook, "/webhooks")


class Health(Resource):
    def get(self):
        return {"status": "ok"}


