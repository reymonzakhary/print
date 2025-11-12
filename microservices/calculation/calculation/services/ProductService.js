const ProductRepository = require('../repositories/ProductRepository');
const { getUniqueIdsFromDirectIds, getDividerByKey } = require('../Helpers/Helper');
const { ValidationError, NotFoundError } = require('../errors');

/**
 * ProductService
 *
 * Business logic for product/box/option operations.
 * Handles product matching and validation.
 */
class ProductService {
    constructor(productRepository = null) {
        this.repository = productRepository || new ProductRepository();
    }

    /**
     * Get and match products (boxes and options) from request items
     *
     * @param {Array} items - Request items with box and option IDs
     * @param {string} supplierId - Supplier/tenant ID
     * @param {Object} boops - Category boops configuration
     * @param {string} categoryId - Category ID for option configuration
     * @returns {Promise<Array>} Matched product objects
     */
    async getMatchedProducts(items, supplierId, boops, categoryId) {
        if (!items || !Array.isArray(items) || items.length === 0) {
            throw new ValidationError('Product items are required');
        }

        // Extract unique box and option IDs from items
        const filtered = getUniqueIdsFromDirectIds(items);
        const boxIds = filtered.boxes;
        const optionIds = filtered.options;

        // Fetch boxes and options from database
        const { boxes, options } = await this.repository.findBoxesAndOptions(
            boxIds,
            optionIds,
            supplierId
        );

        // Process options to apply category-specific configuration
        const processedOptions = this._processOptionsWithCategoryConfig(
            options,
            categoryId
        );

        // Match items with boxes and options
        const matchedProducts = [];

        for (const item of items) {
            const box = boxes.find(b => b._id.toString() === item.key_id);

            if (!box) {
                throw new NotFoundError(
                    `Box with ID '${item.key_id}' not found in system`
                );
            }

            let option = processedOptions.find(o => o._id.toString() === item.value_id);

            if (!option) {
                throw new NotFoundError(
                    `Option with ID '${item.value_id}' is not available`
                );
            }

            // Handle dynamic options (e.g., custom dimensions)
            if (option.dynamic && item._) {
                option = Object.assign({}, option, { _: item._ });
            }

            // Build matched product object
            matchedProducts.push({
                key_link: box.linked,
                divider: item.divider ?? getDividerByKey(boops.boops, item.key),
                appendage: box.appendage,
                dynamic: option.dynamic || false,
                key: item.key,
                value_link: option.linked,
                value: item.value,
                option_id: option._id,
                box_calc_ref: box.calc_ref,
                option_calc_ref: option.additional?.calc_ref || null,
                box: box,
                option: option
            });
        }

        return matchedProducts;
    }

    /**
     * Process options to apply category-specific configuration
     *
     * @param {Array} options - Raw options from database
     * @param {string} categoryId - Category ID
     * @returns {Array} Processed options
     * @private
     */
    _processOptionsWithCategoryConfig(options, categoryId) {
        const processedOptions = [];

        for (const option of options) {
            // If option has category-specific configuration, apply it
            if (option.configure) {
                const categoryConfig = option.configure.find(
                    c => c.category_id.toString() === categoryId
                );

                if (categoryConfig && categoryConfig.configure) {
                    // Merge category-specific config into option
                    Object.assign(option, categoryConfig.configure);
                }

                // Remove the configure array from result
                delete option.configure;
            }

            processedOptions.push(option);
        }

        return processedOptions;
    }

    /**
     * Extract specific product types from matched products
     *
     * @param {Array} products - Matched products
     * @param {string} calcRef - Calculation reference (e.g., 'material', 'format', 'printing_colors')
     * @returns {Array} Filtered products
     */
    filterByCalcRef(products, calcRef) {
        return products.filter(p => p.box_calc_ref === calcRef);
    }

    /**
     * Get format from products
     *
     * @param {Array} products - Matched products
     * @returns {Object|null} Format object
     */
    getFormat(products) {
        const formatProduct = products.find(p => p.box_calc_ref === 'format');
        return formatProduct ? formatProduct.option : null;
    }

    /**
     * Get material from products
     *
     * @param {Array} products - Matched products
     * @returns {Object|null} Material object
     */
    getMaterial(products) {
        const materialProduct = products.find(p => p.box_calc_ref === 'material');
        return materialProduct ? materialProduct.option : null;
    }

    /**
     * Get weight from products
     *
     * @param {Array} products - Matched products
     * @returns {Object|null} Weight object
     */
    getWeight(products) {
        const weightProduct = products.find(p => p.box_calc_ref === 'weight');
        return weightProduct ? weightProduct.option : null;
    }

    /**
     * Get printing colors from products
     *
     * @param {Array} products - Matched products
     * @returns {Object|null} Color object
     */
    getPrintingColors(products) {
        const colorProduct = products.find(
            p => p.box_calc_ref === 'printing_colors' || p.box_calc_ref === 'printing-colors'
        );
        return colorProduct ? colorProduct.option : null;
    }
}

module.exports = ProductService;
