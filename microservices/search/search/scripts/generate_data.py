# scripts/generate_data.py
from services.generate_data_set_service import DataSetService
from services.box_data_set_service import DataSetService as BoxDataSetService
from services.options_data_set_service import DataSetService as OptionDataSetService


def generate_data():
    category_data_service = DataSetService()
    category_data_service.generate_data_set()

    box_data_service = BoxDataSetService()
    box_data_service.generate_data_set()

    option_data_service = OptionDataSetService()
    option_data_service.generate_data_set()

generate_data()
