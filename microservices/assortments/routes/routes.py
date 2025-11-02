from resources.options.merge import MergeOptionsApi
from resources.boxes.merge import MergeBoxesApi
from resources.suppliers.categories.calculates.CalculationApi import CalculationApi
from resources.categories.category import CategoriesApi, CategoryApi, AttachSupplierCategoryApi, \
    DetachSupplierCategoryApi, ProductCalculationApi
from resources.categories.unlinked import UnlinkedCategoriesApi

from resources.categories.manifest import CategoryManifestApi, LinkedCategoryManifestApi
from resources.categories.supplierManifest import SupplierManifestApi

from resources.categories.merge import MergeCategoriesApi
from resources.categories.unmatched import UnmatchedCategoriesApi, UnmatchedCategoriesDeleteApi
from resources.categories.matched import MatchedCategoriesApi
from resources.categories.boxes.box import CategoryBoxesApi, CategoryBoxApi, AttachSupplierBoxApi, DetachSupplierBoxApi, \
    AttachBoxToCategoryApi
from resources.categories.boxes.options.option import CategoryBoxOptionsApi, CategoryBoxOptionApi, \
    AttachSupplierOptionApi, DetachSupplierOptionApi, AttachOptionToBoxApi, SupplierCategoryBoxOptionApi, \
    SupplierCategoryBoxOptionsApi
from resources.boxes.box import BoxApi, BoxesApi, Standard, SysDocs,UpdateOptions
from resources.boxes.options.option import BoxOptionsApi, BoxOptionApi
from resources.boxes.unmatched import UnmatchedBoxesApi, UnmatchedBoxesDeleteApi
from resources.boxes.unlinked import UnlinkedBoxesApi
from resources.boxes.relation import BoxRelationsApi
from resources.options.option import OptionApi, OptionsApi
from resources.options.unmatched import UnmatchedOptionsApi, UnmatchedOptionsDeleteApi
from resources.options.matched import MatchedOptionsApi
from resources.options.unlinked import UnlinkedOptionsApi
from resources.options.relation import OptionRelationsApi
from resources.similarities.similarity import SimilarityCategory, SimilarityBox, SimilarityOption, SimilarityUtil
from resources.boxes.matched import MatchedBoxesApi
#suppliers
from resources.suppliers.categories.category import (SupplierCategoriesApi, SupplierCategoryApi, SupplierPivotCategoryApi,
                                                     SupplierCategoriesCountApi)
from resources.suppliers.categories.categoryImportRuns import SupplierCategoyImportRunsApi
from resources.suppliers.categories.categoyExport import SupplierCategoyExportApi
from resources.suppliers.categories.categoyImport import SupplierCategoyImportApi
from resources.suppliers.categories.linkedCategoriesSuppliers import LinkedCategoriesSuppliersApi, LinkedCategoriesSuppliersManifestApi
from resources.suppliers.boxes.box import SupplierBoxesApi, SupplierBoxApi
from resources.suppliers.boxes.options.option import SupplierBoxOptionsApi, SupplierBoxOptionApi
from resources.suppliers.options.option import SupplierOptionsApi, SupplierOptionApi
from resources.suppliers.machines.options import SupplierMachineOptionsApi
from resources.suppliers.supplier import LinkSupplierCategoryToResellerApi
from resources.suppliers.categories.boops import SupplierCategoryBoopsApi, SupplierCategoryObjectApi, \
    OpenProductBoopsApi
from resources.suppliers.products.product import SupplierProductsApi, SupplierProductsCountApi, \
    GenerateSupplierProductsApi, ReGenerateProductsApi
from resources.resellers.reseller import ResellerCategoryApi, ResellerCategoriesApi
from resources.Migrate import MigrateAPI
from resources.catalogues.catalogue import CatalogueApi
from resources.suppliers.external.external_supplier import ExternalSupplier

def initialize_routes(api):
    api.add_resource(ExternalSupplier, "/import/suppliers/<tenant_id>/categories")
    api.add_resource(MigrateAPI, "/migrate/<status>")
    api.add_resource(CategoriesApi, '/categories')
    api.add_resource(CategoryApi, '/categories/<slug>')
    api.add_resource(CategoryManifestApi, '/categories/<category>/manifest')
    api.add_resource(SupplierManifestApi, '/categories/<category>/manifest/<tenant_id>')
    api.add_resource(LinkedCategoryManifestApi, '/categories/<category>/manifest/<supplier_id>/linked')

    api.add_resource(ProductCalculationApi, '/categories/<slug>/calculation')

    # api.add_resource(SupplierCategoriesApi, '/<supplier>/categories')

    api.add_resource(AttachSupplierCategoryApi, '/categories/<slug>/attach')
    api.add_resource(DetachSupplierCategoryApi, '/categories/<slug>/detach')

    api.add_resource(CategoryBoxesApi, '/categories/<cat_slug>/boxes')
    api.add_resource(CategoryBoxApi, '/categories/<cat_slug>/boxes/<box_slug>')

    api.add_resource(AttachBoxToCategoryApi, '/categories/<cat_slug>/boxes/<box_slug>/attach')

    api.add_resource(CategoryBoxOptionsApi, '/categories/<cat_slug>/boxes/<box_slug>/options')
    api.add_resource(CategoryBoxOptionApi, '/categories/<cat_slug>/boxes/<box_slug>/options/<option_slug>')

    api.add_resource(LinkedCategoriesSuppliersApi, '/categories/<linked>/suppliers')
    api.add_resource(LinkedCategoriesSuppliersManifestApi, '/categories/<linked>/suppliers/<supplier_id>/manifest/load')

    api.add_resource(SupplierCategoryBoxOptionApi, '/suppliers/<supplier_id>/categories/<cat_id>/options/<option_id>')
    api.add_resource(SupplierCategoryBoxOptionsApi, '/suppliers/<supplier_id>/categories/<cat_id>/options')

    api.add_resource(AttachOptionToBoxApi, '/categories/<cat_slug>/boxes/<box_slug>/options/<option_slug>/attach')

    api.add_resource(UnmatchedCategoriesApi, '/unmatched/categories')
    api.add_resource(UnmatchedCategoriesDeleteApi, '/unmatched/categories/<category>')
    api.add_resource(MatchedCategoriesApi, '/matched/categories')

    api.add_resource(UnlinkedCategoriesApi, '/unlinked/categories')
    api.add_resource(MergeCategoriesApi, '/merge/categories')

    api.add_resource(BoxesApi, '/boxes')
    api.add_resource(BoxRelationsApi, '/boxes/<slug>/relations')
    api.add_resource(BoxApi, '/boxes/<slug>')
    api.add_resource(BoxOptionsApi, '/boxes/<slug>/options')
    api.add_resource(BoxOptionApi, '/boxes/<box_slug>/options/<option_slug>')

    api.add_resource(AttachSupplierBoxApi, '/boxes/<slug>/attach')
    api.add_resource(DetachSupplierBoxApi, '/boxes/<slug>/detach')

    api.add_resource(MatchedBoxesApi, '/matched/boxes')
    api.add_resource(UnmatchedBoxesApi, '/unmatched/boxes')
    api.add_resource(UnmatchedBoxesDeleteApi, '/unmatched/boxes/<box>')

    api.add_resource(UnlinkedBoxesApi, '/unlinked/boxes')

    api.add_resource(MergeBoxesApi, '/merge/boxes')
    
    api.add_resource(MergeOptionsApi, '/merge/options')

    api.add_resource(OptionsApi, '/options')
    api.add_resource(OptionApi, '/options/<slug>')
    api.add_resource(OptionRelationsApi, '/options/<slug>/relations')

    api.add_resource(AttachSupplierOptionApi, '/options/<slug>/attach')
    api.add_resource(DetachSupplierOptionApi, '/options/<slug>/detach')

    api.add_resource(MatchedOptionsApi, '/matched/options')
    api.add_resource(UnmatchedOptionsApi, '/unmatched/options')
    api.add_resource(UnmatchedOptionsDeleteApi, '/unmatched/options/<option>')

    api.add_resource(UnlinkedOptionsApi, '/unlinked/options')

    api.add_resource(Standard, '/import/boxes')
    api.add_resource(SysDocs, '/import/docs')
    api.add_resource(UpdateOptions, '/update/options')

    api.add_resource(SimilarityUtil, "/similarity/utils/fix-all-linked")

    api.add_resource(SimilarityCategory, "/similarity/categories")
    api.add_resource(SimilarityBox, "/similarity/boxes")
    api.add_resource(SimilarityOption, "/similarity/options")

    # catalogues
    api.add_resource(CatalogueApi, "/catalogues")

    # supplier categories
    api.add_resource(SupplierCategoryApi, '/suppliers/<supplier>/categories/<slug>')
    api.add_resource(SupplierCategoriesApi, "/suppliers/<supplier_id>/categories")
    api.add_resource(SupplierCategoriesCountApi, "/suppliers/<supplier_id>/categories/count")
    api.add_resource(SupplierPivotCategoryApi, "/suppliers/<supplier_id>/categories/attached")

    # export supplier category
    api.add_resource(SupplierCategoyExportApi, "/suppliers/<supplier_id>/categories/<slug>/export")
    # import supplier category
    api.add_resource(SupplierCategoyImportApi, "/suppliers/<supplier_id>/categories/<slug>/import")
    api.add_resource(SupplierCategoyImportRunsApi, "/suppliers/<supplier_id>/categories/<slug>/import/runs")

    # calculates/
    api.add_resource(CalculationApi, '/suppliers/<supplier>/categories/<category>/calculate/prices')

    # supplier Product
    api.add_resource(SupplierProductsApi, '/suppliers/<supplier>/categories/<slug>/products')
    api.add_resource(GenerateSupplierProductsApi, '/suppliers/<supplier>/categories/<slug>/products/generate')
    api.add_resource(ReGenerateProductsApi, '/suppliers/<supplier>/categories/<slug>/products/regenerate')
    api.add_resource(SupplierProductsCountApi, '/suppliers/<supplier>/categories/<slug>/products/count')

    # supplier boxes
    api.add_resource(SupplierBoxApi, '/suppliers/<supplier>/boxes/<slug>')
    api.add_resource(SupplierBoxesApi, "/suppliers/<supplier_id>/boxes")
    api.add_resource(SupplierBoxOptionsApi, '/suppliers/<supplier>/boxes/<box>/options')
    api.add_resource(SupplierBoxOptionApi, '/suppliers/<supplier>/<box>/options/<option>')

    # supplier options
    api.add_resource(SupplierOptionApi, '/suppliers/<supplier>/options/<option_id>')
    api.add_resource(SupplierOptionsApi, "/suppliers/<supplier_id>/options")
    api.add_resource(SupplierMachineOptionsApi, "/suppliers/<supplier>/machines/<machine>/options")

    api.add_resource(SupplierCategoryBoopsApi, "/suppliers/<supplier_id>/categories/<slug>/boops")
    api.add_resource(OpenProductBoopsApi, "/suppliers/<supplier_id>/categories/<slug>/boops/open-product")

    api.add_resource(SupplierCategoryObjectApi, "/suppliers/<supplier_id>/categories/<slug>/object")

    ## resellers
    api.add_resource(LinkSupplierCategoryToResellerApi, "/suppliers/<supplier_id>/categories/<slug>/link")

    api.add_resource(ResellerCategoriesApi, "/resellers/<tenant_id>/categories")
    api.add_resource(ResellerCategoryApi, "/resellers/<tenant_id>/categories/<supplier_category_id>")
