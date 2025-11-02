from _resources.zip import Extract, Zip
from resources.excel import ExcelRead,ExportExcel
from resources.directory import Rename
from resources.addlayer import AddLayerToPosition

def initialize_routes(api):
    # ZIP
    api.add_resource(Extract, '/zip/extract')
    api.add_resource(Zip, '/zip')
    # Excel
    api.add_resource(ExcelRead, '/excel/read')
    api.add_resource(ExportExcel, '/excel/create')
    # Rename
    api.add_resource(Rename, '/directory/rename')
    # Add Layer
    api.add_resource(AddLayerToPosition, '/pdf/addlayer')