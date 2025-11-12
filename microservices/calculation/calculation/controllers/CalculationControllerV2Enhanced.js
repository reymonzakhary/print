const CalculationServiceV2 = require('../services/CalculationServiceV2');
const { ApplicationError } = require('../errors');

/**
 * CalculationControllerV2Enhanced
 *
 * V2 API controller with enhanced digital printing calculations.
 * Provides accurate, machine-specific calculations with better breakdown.
 *
 * Differences from V1:
 * - Structured input format (not legacy product array)
 * - Dedicated calculators per machine type
 * - Better cost breakdown
 * - Timing estimates
 * - Multiple machine comparison
 */
module.exports = class CalculationControllerV2Enhanced {
    /**
     * Calculate price using V2 enhanced calculation logic
     *
     * POST /v2/calculate
     *
     * Request body:
     * {
     *   "slug": "business-cards",
     *   "supplier_id": "tenant_123",
     *   "quantity": 500,
     *   "format": {
     *     "width": 85,
     *     "height": 55,
     *     "bleed": 3
     *   },
     *   "material": {
     *     "type": "paper_coated",
     *     "gsm": 300,
     *     "price": 4500
     *   },
     *   "colors": {
     *     "front": 4,
     *     "back": 4
     *   },
     *   "finishing": [
     *     {
     *       "type": "lamination",
     *       "lamination_type": "gloss",
     *       "sides": "front"
     *     },
     *     {
     *       "type": "die-cut",
     *       "custom_shape": true
     *     }
     *   ],
     *   "contract": null,
     *   "internal": false
     * }
     *
     * @param {Object} req - Express request
     * @param {Object} res - Express response
     * @returns {Promise<Response>} JSON response
     */
    static async calculate(req, res) {
        console.log(req.body)
        try {

            const {
                slug,
                supplier_id,
                quantity,
                format,
                material,
                colors,
                finishing,
                contract,
                internal
            } = req.body;

            // Validate required fields
            if (!slug || !supplier_id || !quantity || !format) {
                return res.status(422).json({
                    error: {
                        message: 'Missing required fields: slug, supplier_id, quantity, format',
                        code: 'VALIDATION_ERROR',
                        status: 422
                    }
                });
            }

            // Initialize V2 calculation service
            const calculationService = new CalculationServiceV2();

            // Run calculation
            const result = await calculationService.calculate({
                slug,
                supplierId: supplier_id,
                quantity,
                format,
                material,
                colors: colors || { front: 4, back: 0 },
                finishing: finishing || [],
                contract,
                internal: internal || false
            });

            return res.status(200).json(result);

        } catch (error) {
            return CalculationControllerV2Enhanced._handleError(error, res);
        }
    }

    /**
     * Calculate price for internal use (with margins)
     *
     * POST /v2/calculate/internal
     *
     * @param {Object} req - Express request
     * @param {Object} res - Express response
     * @returns {Promise<Response>} JSON response
     */
    static async calculateInternal(req, res) {
        // Force internal flag
        req.body.internal = true;
        return CalculationControllerV2Enhanced.calculate(req, res);
    }

    /**
     * Calculate price for shop (without margins)
     *
     * POST /v2/calculate/shop
     *
     * @param {Object} req - Express request
     * @param {Object} res - Express response
     * @returns {Promise<Response>} JSON response
     */
    static async calculateShop(req, res) {
        // Force internal flag to false
        req.body.internal = false;
        return CalculationControllerV2Enhanced.calculate(req, res);
    }

    /**
     * Calculate prices for multiple quantities (price list)
     *
     * POST /v2/calculate/price-list
     *
     * @param {Object} req - Express request
     * @param {Object} res - Express response
     * @returns {Promise<Response>} JSON response
     */
    static async calculatePriceList(req, res) {
        try {
            const { quantities, ...params } = req.body;

            if (!quantities || !Array.isArray(quantities) || quantities.length === 0) {
                return res.status(400).json({
                    error: {
                        message: 'quantities array is required',
                        code: 'VALIDATION_ERROR',
                        status: 400
                    }
                });
            }

            const calculationService = new CalculationServiceV2();
            const results = [];

            // Calculate for each quantity
            for (const qty of quantities) {
                try {
                    const result = await calculationService.calculate({
                        ...params,
                        supplierId: params.supplier_id,
                        quantity: qty,
                        internal: params.internal || false
                    });
                    results.push(result);
                } catch (error) {
                    console.error(`Failed to calculate for quantity ${qty}:`, error.message);
                }
            }

            if (results.length === 0) {
                return res.status(422).json({
                    error: {
                        message: 'Unable to calculate prices for any quantity',
                        code: 'CALCULATION_ERROR',
                        status: 422
                    }
                });
            }

            // Merge results
            const mergedResult = {
                api_version: 'v2',
                type: 'price_list',
                ...results[0],
                quantities: quantities,
                price_variations: results.map((r, i) => ({
                    quantity: quantities[i],
                    prices: r.prices,
                    machines_calculated: r.machines_calculated
                }))
            };

            return res.status(200).json(mergedResult);

        } catch (error) {
            return CalculationControllerV2Enhanced._handleError(error, res);
        }
    }

    /**
     * Get available machine types and their requirements
     *
     * GET /v2/calculate/machine-types
     *
     * @param {Object} req - Express request
     * @param {Object} res - Express response
     * @returns {Promise<Response>} JSON response
     */
    static async getMachineTypes(req, res) {
        return res.status(200).json({
            api_version: 'v2',
            machine_types: [
                {
                    type: 'digital',
                    name: 'Digital Printing',
                    description: 'High-quality digital printing for short to medium runs',
                    required_config: ['format', 'material', 'colors'],
                    optional_config: ['coverage', 'finishing'],
                    best_for: {
                        min_quantity: 1,
                        max_quantity: 5000,
                        turnaround: 'fast'
                    }
                },
                {
                    type: 'offset',
                    name: 'Offset Printing',
                    description: 'Traditional offset printing for large runs',
                    required_config: ['format', 'material', 'colors', 'plates'],
                    optional_config: ['finishing', 'coating'],
                    best_for: {
                        min_quantity: 500,
                        max_quantity: 100000,
                        turnaround: 'standard'
                    }
                },
                {
                    type: 'lamination',
                    name: 'Lamination',
                    description: 'Protective and aesthetic lamination finishes',
                    required_config: ['format', 'lamination_type'],
                    optional_config: ['sides'],
                    lamination_types: ['gloss', 'matte', 'soft-touch', 'anti-scratch', 'holographic']
                },
                {
                    type: 'finishing',
                    name: 'Finishing Operations',
                    description: 'Die-cutting, folding, perforating, scoring, drilling',
                    required_config: ['finishing_type'],
                    optional_config: ['custom_shape', 'fold_count', 'hole_count'],
                    finishing_types: ['die-cut', 'fold', 'perforate', 'score', 'drill']
                }
            ]
        });
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
        console.error('V2 Calculation error:', error);

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
