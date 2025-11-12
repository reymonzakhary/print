const CalculationEngine = require('../services/CalculationEngine');
const { ApplicationError } = require('../errors');

/**
 * ShopCalculationControllerV2
 *
 * Refactored shop controller using new service-based architecture.
 * Shop version excludes internal profit margins and pricing data.
 */
module.exports = class ShopCalculationControllerV2 {
    /**
     * Calculate product price (shop version without internal margins)
     *
     * @param {Object} req - Express request
     * @param {Object} res - Express response
     * @returns {Promise<Response>} JSON response
     */
    static async calculate(req, res) {
        try {
            const { supplier_id, slug } = req.params;
            const { contract, product } = req.body;

            // Initialize calculation engine
            const engine = new CalculationEngine();

            // Run calculation
            const result = await engine.calculate({
                slug,
                supplierId: supplier_id,
                productItems: product,
                quantity: req.body.quantity,
                contract,
                internal: false, // Exclude margins and profit
                vat: req.body.vat,
                vatOverride: req.body.vat_override,
                requestDlv: req.body.dlv
            });

            return res.status(200).json(result);

        } catch (error) {
            return ShopCalculationControllerV2._handleError(error, res);
        }
    }

    /**
     * Handle errors with proper HTTP status codes
     *
     * @param {Error} error - Error object
     * @param {Object} res - Express response
     * @returns {Response} JSON error response
     * @private
     */
    static _handleError(error, res) {
        console.error('Shop calculation error:', error);

        // Use proper HTTP status codes based on error type
        if (error instanceof ApplicationError) {
            return res.status(error.statusCode).json({
                error: {
                    message: error.message,
                    code: error.name,
                    status: error.statusCode,
                    ...(error.field && { field: error.field })
                }
            });
        }

        // Unknown error - return 500
        return res.status(500).json({
            error: {
                message: 'Internal server error',
                code: 'INTERNAL_ERROR',
                status: 500
            }
        });
    }
};
