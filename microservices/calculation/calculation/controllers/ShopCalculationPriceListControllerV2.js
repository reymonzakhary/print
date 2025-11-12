const CalculationEngine = require('../services/CalculationEngine');
const PriceFormatterService = require('../services/PriceFormatterService');
const { ApplicationError } = require('../errors');
const { rangeListFromCategory } = require('../Helpers/Helper');
const FetchCategory = require('../Calculations/FetchCategory');

/**
 * ShopCalculationPriceListControllerV2
 *
 * Refactored price list controller using new service-based architecture.
 * Calculates prices across multiple quantity ranges.
 */
module.exports = class ShopCalculationPriceListControllerV2 {
    /**
     * Calculate price list across multiple quantities
     *
     * @param {Object} req - Express request
     * @param {Object} res - Express response
     * @returns {Promise<Response>} JSON response
     */
    static async calculate(req, res) {
        try {
            const { supplier_id, slug } = req.params;
            const { contract, product } = req.body;

            // Fetch category to get quantity ranges
            const { category } = await (new FetchCategory(slug, supplier_id).getCategory());

            if (!category) {
                return res.status(404).json({
                    error: {
                        message: 'Category not found',
                        code: 'NOT_FOUND',
                        status: 404
                    }
                });
            }

            // Get quantity list from category
            const quantityList = rangeListFromCategory(category).reduce((acc, item) => {
                acc.push(...item.range_list);
                return acc;
            }, []);

            if (quantityList.length === 0) {
                return res.status(400).json({
                    error: {
                        message: 'No quantity ranges defined for this category',
                        code: 'VALIDATION_ERROR',
                        status: 400
                    }
                });
            }

            // Initialize calculation engine
            const engine = new CalculationEngine();
            const priceFormatter = new PriceFormatterService();

            // Calculate for each quantity
            const results = [];

            for (const quantity of quantityList) {
                try {
                    const result = await engine.calculate({
                        slug,
                        supplierId: supplier_id,
                        productItems: product,
                        quantity: quantity,
                        contract,
                        internal: false, // Shop calculation
                        vat: req.body.vat,
                        vatOverride: req.body.vat_override,
                        requestDlv: req.body.dlv
                    });

                    results.push(result);
                } catch (error) {
                    // Log but continue with other quantities
                    console.error(`Failed to calculate for quantity ${quantity}:`, error.message);
                }
            }

            // Filter out empty results
            const validResults = results.filter(r => r && r.prices && r.prices.length > 0);

            if (validResults.length === 0) {
                return res.status(422).json({
                    error: {
                        message: 'Unable to calculate prices for any quantity',
                        code: 'CALCULATION_ERROR',
                        status: 422
                    }
                });
            }

            // Merge all prices into single response
            const mergedResult = priceFormatter.mergePriceObjects(validResults);

            return res.status(200).json(mergedResult);

        } catch (error) {
            return ShopCalculationPriceListControllerV2._handleError(error, res);
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
        console.error('Price list calculation error:', error);

        // Use proper HTTP status codes based on error type
        if (error instanceof ApplicationError) {
            return res.status(error.statusCode).json({
                error: {
                    message: error.message,
                    code: error.name,
                    status: error.statusCode
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
