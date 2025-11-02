from resources.categories.category import Configure
from resources.attributes.attribute import GetAttributes, CreateBoops


def initialize_routes(api):
    api.add_resource(Configure, '/configure')
    # api.add_resource(ImportAttributes, '/categoories/<slug>/attributes')
    # api.add_resource(GetCombinations, '/categories/<slug>')
    # api.add_resource(GetAttributes, '/attributes/<slug>')
    # api.add_resource(CreateBoops, '/boops/<slug>')
