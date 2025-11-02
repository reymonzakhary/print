from resources.categories.category import ImportCategories

def initialize_routes(api):
    api.add_resource(ImportCategories, '/<tenant>/categories')
