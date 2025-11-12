const Axios = require('axios');
const { ExternalServiceError } = require('../errors');

/**
 * MarginService
 *
 * Handles margin calculations and fetching from margin microservice.
 */
class MarginService {
    constructor(marginServiceUrl = null) {
        this.serviceUrl = marginServiceUrl || process.env.MARGIN_SERVICE_URL || 'http://margin:3333/';
    }

    /**
     * Fetch margins from margin microservice
     *
     * @param {string} supplierId - Supplier/tenant ID
     * @param {string} categoryId - Category ID
     * @param {number} quantity - Quantity
     * @param {boolean} internal - Is internal calculation
     * @returns {Promise<Array>} Margins array
     */
    async getMargins(supplierId, categoryId, quantity, internal = false) {
        try {
            const response = await Axios.post(
                `${this.serviceUrl}supplier/${supplierId}/category/${categoryId}`,
                { quantity, internal },
                {
                    headers: { 'Content-Type': 'application/json' },
                    timeout: 5000 // 5 second timeout
                }
            );

            if (response.data && response.data.margins) {
                return response.data.margins;
            }

            // If no margins returned, use empty array (no margin applied)
            return [];
        } catch (error) {
            // Log error but don't fail the calculation
            console.error(`Failed to fetch margins: ${error.message}`);

            // For internal calculations, we might want to throw
            if (internal) {
                throw new ExternalServiceError(
                    'Failed to fetch margins from margin service',
                    'margin'
                );
            }

            // For shop calculations, continue without margins
            return [];
        }
    }

    /**
     * Apply margins to a gross price
     *
     * @param {number} grossPrice - Price before margin
     * @param {Array} margins - Array of margin objects
     * @param {boolean} internal - Is internal calculation
     * @returns {Object} Price with margins applied
     */
    applyMargins(grossPrice, margins, internal = false) {
        if (!margins || margins.length === 0) {
            return {
                gross_price: grossPrice,
                selling_price: grossPrice,
                profit: 0,
                margins_applied: []
            };
        }

        let sellingPrice = grossPrice;
        let totalProfit = 0;
        const marginsApplied = [];

        for (const margin of margins) {
            if (margin.type === 'percentage') {
                const marginAmount = (sellingPrice * margin.value) / 100;
                sellingPrice += marginAmount;
                totalProfit += marginAmount;

                marginsApplied.push({
                    type: 'percentage',
                    value: margin.value,
                    amount: marginAmount
                });
            } else if (margin.type === 'fixed') {
                sellingPrice += margin.value;
                totalProfit += margin.value;

                marginsApplied.push({
                    type: 'fixed',
                    value: margin.value,
                    amount: margin.value
                });
            }
        }

        return {
            gross_price: grossPrice,
            selling_price: sellingPrice,
            profit: internal ? totalProfit : null, // Only include profit for internal
            margins_applied: internal ? marginsApplied : [] // Only include margin details for internal
        };
    }

    /**
     * Calculate profit from gross and selling prices
     *
     * @param {number} grossPrice - Price before margin
     * @param {number} sellingPrice - Price after margin
     * @returns {number} Profit amount
     */
    calculateProfit(grossPrice, sellingPrice) {
        return sellingPrice - grossPrice;
    }

    /**
     * Calculate margin percentage
     *
     * @param {number} grossPrice - Price before margin
     * @param {number} sellingPrice - Price after margin
     * @returns {number} Margin percentage
     */
    calculateMarginPercentage(grossPrice, sellingPrice) {
        if (grossPrice === 0) {
            return 0;
        }

        return ((sellingPrice - grossPrice) / grossPrice) * 100;
    }
}

module.exports = MarginService;
