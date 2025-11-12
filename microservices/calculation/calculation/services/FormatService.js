const Format = require('../Calculations/Config/Format');
const { filterByCalcRef } = require('../Helpers/Helper');

/**
 * FormatService
 *
 * Wraps the Format calculator in a clean service interface.
 * Handles format calculations for product dimensions.
 */
class FormatService {
    /**
     * Calculate format details for a product
     *
     * @param {Object} category - Category object
     * @param {Array} items - Product items array
     * @param {number} quantity - Quantity
     * @param {number} bleed - Bleed amount
     * @param {Object} request - Request object with range parameters
     * @returns {Object} Format calculation result
     */
    async calculate(category, items, quantity, bleed, request = {}) {
        try {
            // Extract format items
            const formatItems = filterByCalcRef(items, 'format');
            if (!formatItems.length) {
                throw new Error('The format parameter not specified.');
            }

            const formatOption = formatItems[0].option;

            // Extract related calculation refs
            const pages = filterByCalcRef(items, 'pages');
            const cover = filterByCalcRef(items, 'cover');
            const sides = filterByCalcRef(items, 'sides');
            const folding = filterByCalcRef(items, 'folding');
            const bindingMethod = filterByCalcRef(items, 'binding_method');
            const bindingDirection = filterByCalcRef(items, 'binding_direction');
            const endpapers = filterByCalcRef(items, 'endpapers');

            // Get range parameters
            const {
                quantity_range_start = 0,
                quantity_range_end = 0,
                quantity_incremental_by = 0,
                range_override = false
            } = request;

            // Calculate format
            const format = new Format(
                category,
                formatOption,
                quantity,
                bleed,
                quantity_range_start,
                quantity_range_end,
                quantity_incremental_by,
                range_override,
                pages,
                cover,
                bindingMethod,
                bindingDirection,
                folding,
                endpapers,
                sides
            ).calculate();

            // Check for errors
            if (format.status !== 200) {
                throw new Error(format.message);
            }

            return {
                status: 200,
                format: format,
                // Return commonly used properties for convenience
                width: format.width,
                height: format.height,
                bleed: format.bleed,
                quantity: format.quantity,
                size: format.size,
                sheets: format.sheets,
                pages: format.num_pages
            };
        } catch (error) {
            return {
                status: 422,
                message: error.message,
                format: null
            };
        }
    }

    /**
     * Get default format option from items
     *
     * @param {Array} items - Product items
     * @returns {Object|null} Format option or null
     */
    getDefaultFormatOption(items) {
        const formatItems = filterByCalcRef(items, 'format');
        return formatItems.length ? formatItems[0].option : null;
    }
}

module.exports = FormatService;
