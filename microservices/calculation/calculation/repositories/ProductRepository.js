const SupplierBox = require('../Models/SupplierBox');
const SupplierOption = require('../Models/SupplierOption');

/**
 * ProductRepository
 *
 * Handles all database operations for boxes and options.
 * Provides clean interface for fetching product configuration data.
 */
class ProductRepository {
    /**
     * Find boxes by IDs and supplier
     *
     * @param {Array<ObjectId>} boxIds - Array of box IDs
     * @param {string} supplierId - Supplier/tenant ID
     * @returns {Promise<Array>} Array of boxes
     */
    async findBoxesByIds(boxIds, supplierId) {
        try {
            const boxes = await SupplierBox.aggregate([
                {
                    $match: {
                        $and: [
                            { tenant_id: supplierId },
                            { _id: { $in: boxIds } }
                        ]
                    }
                },
                {
                    $project: {
                        _id: 1,
                        name: 1,
                        display_name: 1,
                        slug: 1,
                        system_key: 1,
                        incremental: 1,
                        sqm: 1,
                        linked: 1,
                        start_cost: 1,
                        calc_ref: 1,
                        appendage: 1
                    }
                }
            ]);

            return boxes;
        } catch (error) {
            throw new Error(`Failed to fetch boxes: ${error.message}`);
        }
    }

    /**
     * Find options by IDs and supplier
     *
     * @param {Array<ObjectId>} optionIds - Array of option IDs
     * @param {string} supplierId - Supplier/tenant ID
     * @returns {Promise<Array>} Array of options
     */
    async findOptionsByIds(optionIds, supplierId) {
        try {
            const options = await SupplierOption.aggregate([
                {
                    $match: {
                        $and: [
                            { tenant_id: supplierId },
                            { _id: { $in: optionIds } }
                        ]
                    }
                },
                {
                    $project: {
                        _id: 1,
                        name: 1,
                        display_name: 1,
                        slug: 1,
                        system_key: 1,
                        linked: 1,
                        rpm: 1,
                        sheet_runs: 1,
                        runs: 1,
                        additional: 1,
                        configure: 1,
                        dynamic: 1,
                        calculation_method: 1,
                        start_cost: 1
                    }
                }
            ]);

            return options;
        } catch (error) {
            throw new Error(`Failed to fetch options: ${error.message}`);
        }
    }

    /**
     * Find a single box by ID
     *
     * @param {string|ObjectId} boxId - Box ID
     * @param {string} supplierId - Supplier/tenant ID
     * @returns {Promise<Object|null>} Box object or null
     */
    async findBoxById(boxId, supplierId) {
        try {
            const box = await SupplierBox.findOne({
                _id: boxId,
                tenant_id: supplierId
            }).lean();

            return box;
        } catch (error) {
            throw new Error(`Failed to fetch box: ${error.message}`);
        }
    }

    /**
     * Find a single option by ID
     *
     * @param {string|ObjectId} optionId - Option ID
     * @param {string} supplierId - Supplier/tenant ID
     * @returns {Promise<Object|null>} Option object or null
     */
    async findOptionById(optionId, supplierId) {
        try {
            const option = await SupplierOption.findOne({
                _id: optionId,
                tenant_id: supplierId
            }).lean();

            return option;
        } catch (error) {
            throw new Error(`Failed to fetch option: ${error.message}`);
        }
    }

    /**
     * Find boxes and options in a single query
     *
     * @param {Array<ObjectId>} boxIds - Array of box IDs
     * @param {Array<ObjectId>} optionIds - Array of option IDs
     * @param {string} supplierId - Supplier/tenant ID
     * @returns {Promise<{boxes: Array, options: Array}>} Boxes and options
     */
    async findBoxesAndOptions(boxIds, optionIds, supplierId) {
        try {
            const [boxes, options] = await Promise.all([
                this.findBoxesByIds(boxIds, supplierId),
                this.findOptionsByIds(optionIds, supplierId)
            ]);

            return { boxes, options };
        } catch (error) {
            throw new Error(`Failed to fetch boxes and options: ${error.message}`);
        }
    }
}

module.exports = ProductRepository;
