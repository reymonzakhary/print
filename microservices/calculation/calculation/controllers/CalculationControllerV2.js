const CalculationEngine = require('../services/CalculationEngine');
const { ApplicationError } = require('../errors');

/**
 * CalculationControllerV2
 *
 * Refactored controller using new service-based architecture.
 * Replaces the old CalculationController that used FetchProduct God Object.
 *
 * This controller uses CalculationEngine which orchestrates:
 * - CategoryService
 * - ProductService
 * - MarginService
 * - DiscountService
 * - PriceFormatterService
 */
module.exports = class CalculationControllerV2 {
    /**
     * Calculate product price (internal version with margins)
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
                internal: true, // Include margins and profit
                vat: req.body.vat,
                vatOverride: req.body.vat_override,
                requestDlv: req.body.dlv
            });

            return res.status(200).json(result);

        } catch (error) {
            return CalculationControllerV2._handleError(error, res);
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
        console.error('Calculation error:', error);

        // Use proper HTTP status codes based on error type
        if (error instanceof ApplicationError) {
            return res.status(error.statusCode).json({
                error: {
                    message: error.message,
                    code: error.name,
                    status: error.statusCode,
                    ...(error.field && { field: error.field }),
                    ...(error.details && { details: error.details })
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
