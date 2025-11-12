const CategoryRepository = require('../repositories/CategoryRepository');
const { ValidationError, NotFoundError } = require('../errors');

/**
 * CategoryService
 *
 * Business logic for category operations.
 * Validates and processes category data.
 */
class CategoryService {
    constructor(categoryRepository = null) {
        this.repository = categoryRepository || new CategoryRepository();
    }

    /**
     * Get category with validation
     *
     * @param {string} slug - Category slug
     * @param {string} supplierId - Supplier/tenant ID
     * @returns {Promise<Object>} Category object
     * @throws {NotFoundError} If category not found
     * @throws {ValidationError} If category has no machines
     */
    async getCategory(slug, supplierId) {
        if (!slug || !supplierId) {
            throw new ValidationError('Slug and supplier ID are required');
        }

        const category = await this.repository.findBySlugAndSupplier(slug, supplierId);

        if (!category) {
            throw new NotFoundError(`Category '${slug}' not found`);
        }

        // Extract machines from either legacy 'machine' field or new 'additional' array
        let machines = [];

        if (category.machine && Array.isArray(category.machine) && category.machine.length > 0) {
            // Legacy structure: machines directly in 'machine' field
            machines = category.machine;
        } else if (category.additional && Array.isArray(category.additional) && category.additional.length > 0) {
            // New structure: machines in 'additional' array
            machines = category.additional
                .filter(item => item && item.machine)
                .map(item => item.machine);
        }

        // Validate category has machines
        if (machines.length === 0) {
            throw new ValidationError(`Category '${slug}' has no machines configured`);
        }

        // Validate category has boops (product configuration)
        if (!category.boops || category.boops.length === 0) {
            throw new ValidationError(`Category '${slug}' has no product configuration`);
        }

        return {
            category,
            machines: machines,
            boops: category.boops[0] // First boops element
        };
    }

    /**
     * Check if category exists
     *
     * @param {string} slug - Category slug
     * @param {string} supplierId - Supplier/tenant ID
     * @returns {Promise<boolean>} True if category exists
     */
    async exists(slug, supplierId) {
        return await this.repository.exists(slug, supplierId);
    }

    /**
     * Get delivery options from category
     *
     * @param {Object} category - Category object
     * @returns {Array} Delivery options
     */
    getDeliveryOptions(category) {
        if (!category.production_dlv || !Array.isArray(category.production_dlv)) {
            return [];
        }

        return category.production_dlv;
    }

    /**
     * Validate quantity against category constraints
     *
     * @param {Object} category - Category object
     * @param {number} quantity - Requested quantity
     * @returns {boolean} True if quantity is valid
     */
    validateQuantity(category, quantity) {
        if (!quantity || quantity <= 0) {
            return false;
        }

        // Additional quantity validation logic can be added here
        // e.g., min/max quantity constraints from category

        return true;
    }
}

module.exports = CategoryService;
