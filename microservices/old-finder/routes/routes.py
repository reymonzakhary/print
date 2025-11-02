from resources.categories.category import CategoriesApi, CategoriesSearchApi, CategoryApi
from resources.products.product import ProductFilterApi
from resources.boxes.box import BoxesSearchApi
from resources.options.option import OptionsSearchApi


def initialize_routes(api):
    api.add_resource(CategoriesApi, '/categories')
    api.add_resource(CategoryApi, '/categories/<slug>')
    api.add_resource(CategoriesSearchApi, '/categories/search')
    api.add_resource(ProductFilterApi, '/products/<slug>')
    api.add_resource(BoxesSearchApi, '/boxes/search')
    api.add_resource(OptionsSearchApi, '/options/search')