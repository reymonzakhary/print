from resources.replace import Replace
from resources.addLayerOnPosition import AddLayerToPosition
from resources.addLayers import AddLayersOnPdfs
from resources.addDynamicLayers import AddDynamicLayersOnPdfs
from resources.merge import MergeMultipleFiles

def initialize_routes(api):
    # ZIP
    api.add_resource(Replace, '/replace-text')
    # ADD LAYER ON SPECIFIC POSITION
    api.add_resource(AddLayerToPosition, '/add-layer-on-position')
    # MERGE
    api.add_resource(MergeMultipleFiles, '/merge')
    # ADD LAYERS ON PDF FILES
    api.add_resource(AddLayersOnPdfs, '/add-layer')
    api.add_resource(AddDynamicLayersOnPdfs, '/add-dynamic-layer')