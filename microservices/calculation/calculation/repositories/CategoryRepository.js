const SupplierCategory = require('../Models/SupplierCategory');

/**
 * CategoryRepository
 *
 * Handles all database operations for supplier categories.
 * Isolates data access logic from business logic.
 */
class CategoryRepository {
    /**
     * Find a category by slug and supplier ID
     *
     * @param {string} slug - Category slug
     * @param {string} supplierId - Supplier/tenant ID
     * @returns {Promise<Object|null>} Category with populated machine and boops
     */
    async findBySlugAndSupplier(slug, supplierId) {
        try {
            const category = await SupplierCategory.findOne({
                slug: slug,
                tenant_id: supplierId,
                published: true
            })
            .populate('machine')
            .populate('boops')
            .lean();

            return category;
        } catch (error) {
            throw new Error(`Failed to fetch category: ${error.message}`);
        }
    }

    /**
     * Find category with all related data
     *
     * @param {string} slug - Category slug
     * @param {string} supplierId - Supplier/tenant ID
     * @returns {Promise<Object|null>} Category with all relations
     */
    async findWithRelations(slug, supplierId) {
        try {
            const category = await SupplierCategory.aggregate([
                {
                    $match: {
                        slug: slug,
                        tenant_id: supplierId,
                        published: true
                    }
                },
                {
                    $lookup: {
                        from: 'supplier_machines',
                        localField: 'machine',
                        foreignField: '_id',
                        as: 'machines'
                    }
                },
                {
                    $lookup: {
                        from: 'supplier_products', // Adjust collection name
                        localField: 'boops',
                        foreignField: '_id',
                        as: 'boops'
                    }
                }
            ]);

            return category.length > 0 ? category[0] : null;
        } catch (error) {
            throw new Error(`Failed to fetch category with relations: ${error.message}`);
        }
    }

    /**
     * Check if category exists
     *
     * @param {string} slug - Category slug
     * @param {string} supplierId - Supplier/tenant ID
     * @returns {Promise<boolean>} True if category exists
     */
    async exists(slug, supplierId) {
        try {
            const count = await SupplierCategory.countDocuments({
                slug: slug,
                tenant_id: supplierId,
                published: true
            });

            return count > 0;
        } catch (error) {
            throw new Error(`Failed to check category existence: ${error.message}`);
        }
    }
}

module.exports = CategoryRepository;
