from services.generate_data_set_service import DataSetService
from services.box_data_set_service import DataSetService as BoxDataSetService
from services.options_data_set_service import DataSetService as OptionDataSetService
from services.boxes_redisearch_service import RedisearchService as BoxRedisearchService
from services.options_redisearch_service import RedisearchService as OptionRedisearchService
from services.redisearch_service import RedisearchService as CategoryRedisearchService


class Helper:
    def __init__(self):
        # Initialize services
        self.dataset_service = DataSetService()
        self.box_data_service = BoxDataSetService()
        self.option_data_service = OptionDataSetService()

        self.category_service = CategoryRedisearchService()
        self.box_service = BoxRedisearchService()
        self.option_service = OptionRedisearchService()

    
    """
    Clear indexing and Data for all services
    """
    def clear_indixing(self):
        try:
            self.dataset_service.clear_data_set()
            self.box_data_service.clear_data_set()
            self.option_data_service.clear_data_set()
        except Exception as e:
            print(f"Error clearing indexing: {e}")

    """
    Create and insert data for all services
    """
    def create_indexing(self):
        try:
            self.category_service.create_index()
            self.box_service.create_index()
            self.option_service.create_index()
        except Exception as e:
            print(f"Error creating indexing: {e}")

    """ Generate / Inserting Data """
    def generate_data_set(self):
        try:
            self.dataset_service.generate_data_set()
            self.box_data_service.generate_data_set()
            self.option_data_service.generate_data_set()
            return {"message": "Data generated", "status": 200, "data": [], "errors": []}
        except Exception as e:
            print(f"Error generating data set: {e}")
            return {"message": f"Error {e}", "status": 422, "data": [], "errors": [{"error": f"{e}"}]}