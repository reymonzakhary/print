from database import get_mongo_connection
from api import APIService
from pipeline.product_service import ProductService
from pipeline.utils import UtilsService
from pipeline.generate_boobs import GenerateBoobsService
from pipeline.option_service import OptionService

class SyncPipelineService:
    def __init__(self, client):
        """Initialize SyncPipelineService with necessary services."""
        self.client = client
        self.utils_service = UtilsService(client)
        self.product_service = ProductService(client)
        self.generate_boobs_service = GenerateBoobsService(client)
        self.option_service = OptionService(client)
        self.api_service = APIService(client)

    def sync_pipeline(self, tenant_name: str, tenant_id: str, sku_list: [], vendor):
        """Handles full synchronization process."""

        # try:
        # Step 1: Delete old data
        self.utils_service.delete_all_data()

        # Step 2: Fetch products from API
        products = self.api_service.fetch_products()
        if products:
            self.product_service.store_products_in_mongo(products, tenant_name, tenant_id, sku_list, vendor)

        # Step 3: Update range sets
        self.utils_service.set_range_set()

        # Step 4: Generate hierarchical data in 'oops' collection
        self.generate_boobs_service.store_all_in_oops()

        self.option_service.generate_combinations_for_multi()
        self.option_service.generate_combinations_for_non_multi()

        # self.option_service.get_option_ids_from_oops()

        # self.option_service.generate_combinations_for_non_multi('postcards')

        # self.option_service.update_excludes_in_ops()

        # Close DB connection
        self.client.close()

        return {"message": "Print.com Sync completed"}
        # except Exception as e:
        #     return {"error": "An Error Happen Please try again"}


    def sync_propo_pipeline(self, tenant_name: str, tenant_id: str, sku_list: [], vendor: str):
        """Handles full synchronization process."""

        self.utils_service.delete_all_data()

        # Step 2: Fetch products from API
        probo_response = self.api_service.fetch_proboprints_products()
        products = probo_response['data']
        meta = probo_response['meta']

        if products:
            data = self.product_service.store_products_in_mongo(products, tenant_name, tenant_id, sku_list, vendor)
            return data

        self.client.close()

        return products

        # try:
        #     # Step 1: Delete old data
        #     self.utils_service.delete_all_data()
        #
        #     # Step 2: Fetch products from API
        #     probo_response = self.api_service.fetch_proboprints_products().get('data')
        #     products = probo_response.get('data')
        #     meta = probo_response.get('meta')
        #
        #     if products:
        #         self.product_service.store_products_in_mongo(products, tenant_name, tenant_id, sku_list, vendor)
        #
        #     return products
        #     # Step 3: Update range sets
        #     self.utils_service.set_range_set()
        #
        #     # Step 4: Generate hierarchical data in 'oops' collection
        #     self.generate_boobs_service.store_all_in_oops()
        #
        #     self.option_service.get_option_ids_from_oops()
        #
        #     # Close DB connection
        #     self.client.close()
        #
        #     return {"message": "Print.com Sync completed"}
        # except Exception as e:
        #     return {"error": "An Error Happen Please try again"}
