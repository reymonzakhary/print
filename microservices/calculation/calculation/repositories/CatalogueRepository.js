const SupplierCatalogue = require('../Models/SupplierCatalogue');

/**
 * CatalogueRepository
 *
 * Handles all database operations for supplier catalogues (materials).
 */
class CatalogueRepository {
    /**
     * Find catalogues by material and GSM criteria
     *
     * @param {string} supplierId - Supplier/tenant ID
     * @param {Object} criteria - Search criteria
     * @returns {Promise<Array>} Array of catalogues
     */
    async findByCriteria(supplierId, criteria) {
        try {
            const catalogues = await SupplierCatalogue.find({
                tenant_id: supplierId,
                ...criteria
            }).lean();

            return catalogues;
        } catch (error) {
            throw new Error(`Failed to fetch catalogues: ${error.message}`);
        }
    }

    /**
     * Find catalogue by material link and GSM link
     *
     * @param {string} supplierId - Supplier/tenant ID
     * @param {ObjectId} materialLink - Material link ID
     * @param {ObjectId} grsLink - GSM link ID
     * @returns {Promise<Object|null>} Catalogue object or null
     */
    async findByMaterialAndGsm(supplierId, materialLink, grsLink) {
        try {
            const catalogue = await SupplierCatalogue.findOne({
                tenant_id: supplierId,
                material_link: materialLink,
                grs_link: grsLink
            }).lean();

            return catalogue;
        } catch (error) {
            throw new Error(`Failed to fetch catalogue: ${error.message}`);
        }
    }

    /**
     * Find all catalogues for a supplier
     *
     * @param {string} supplierId - Supplier/tenant ID
     * @returns {Promise<Array>} Array of catalogues
     */
    async findAllBySupplier(supplierId) {
        try {
            const catalogues = await SupplierCatalogue.find({
                tenant_id: supplierId
            }).lean();

            return catalogues;
        } catch (error) {
            throw new Error(`Failed to fetch catalogues: ${error.message}`);
        }
    }
}

module.exports = CatalogueRepository;
