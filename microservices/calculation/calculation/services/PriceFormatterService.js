const crypto = require('crypto');

/**
 * PriceFormatterService
 *
 * Handles price formatting and calculations including VAT, per-piece prices, etc.
 * Extracted from Helper.js formatPriceObject function.
 */
class PriceFormatterService {
    /**
     * Format a complete price object for API response
     *
     * @param {Object} params - Price parameters
     * @returns {Object} Formatted price object
     */
    formatPriceObject(params) {
        const {
            category,
            quantity,
            price,
            dlv,
            machine,
            margins,
            discount,
            requestDlv,
            internal,
            vat,
            vatOverride
        } = params;

        // Generate unique ID for this price variation
        const id = this._generatePriceId(price, dlv, quantity, category.tenant_id);

        // Calculate gross price (before margins)
        const grossPrice = parseFloat(price);
        const grossPricePerPiece = grossPrice / quantity;

        // Apply margins
        const marginResult = this._applyMargins(grossPrice, margins, internal);
        const sellingPrice = marginResult.selling_price;
        const sellingPricePerPiece = sellingPrice / quantity;

        // Calculate VAT
        const vatInfo = this._calculateVAT(sellingPrice, vat, vatOverride, category);
        const sellingPriceInc = sellingPrice + vatInfo.vat_amount;

        return {
            id,
            pm: machine?.pm || 'per_sheet',
            qty: quantity,
            dlv: this._formatDelivery(dlv),
            gross_price: this._roundPrice(grossPrice),
            gross_ppp: this._roundPrice(grossPricePerPiece),
            p: this._roundPrice(sellingPrice),
            ppp: this._roundPrice(sellingPricePerPiece),
            selling_price_ex: this._roundPrice(sellingPrice),
            selling_price_inc: this._roundPrice(sellingPriceInc),
            profit: internal ? this._roundPrice(marginResult.profit) : null,
            discount: discount || [],
            margins: internal ? marginResult.margins_applied : [],
            vat: vatInfo.vat_percentage,
            vat_p: this._roundPrice(vatInfo.vat_amount),
            vat_ppp: this._roundPrice(vatInfo.vat_amount / quantity)
        };
    }

    /**
     * Generate unique ID for price variation
     *
     * @param {number} price - Price amount
     * @param {Object} dlv - Delivery object
     * @param {number} quantity - Quantity
     * @param {string} tenantId - Tenant ID
     * @returns {string} Unique ID hash
     * @private
     */
    _generatePriceId(price, dlv, quantity, tenantId) {
        const str = `${price}_${dlv?.days || 0}_${quantity}_${tenantId}`;
        return crypto.createHash('md5').update(str).digest('hex');
    }

    /**
     * Apply margins to gross price
     *
     * @param {number} grossPrice - Price before margin
     * @param {Array} margins - Margins to apply
     * @param {boolean} internal - Include internal data
     * @returns {Object} Result with selling price and profit
     * @private
     */
    _applyMargins(grossPrice, margins, internal) {
        if (!margins || margins.length === 0) {
            return {
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

                if (internal) {
                    marginsApplied.push({
                        type: 'percentage',
                        value: margin.value,
                        amount: this._roundPrice(marginAmount)
                    });
                }
            } else if (margin.type === 'fixed') {
                sellingPrice += margin.value;
                totalProfit += margin.value;

                if (internal) {
                    marginsApplied.push({
                        type: 'fixed',
                        value: margin.value,
                        amount: margin.value
                    });
                }
            }
        }

        return {
            selling_price: sellingPrice,
            profit: totalProfit,
            margins_applied: marginsApplied
        };
    }

    /**
     * Calculate VAT amount and percentage
     *
     * @param {number} price - Price before VAT
     * @param {number} vat - VAT percentage from request
     * @param {boolean} vatOverride - Override category VAT
     * @param {Object} category - Category object
     * @returns {Object} VAT info
     * @private
     */
    _calculateVAT(price, vat, vatOverride, category) {
        let vatPercentage = 0;

        if (vatOverride && vat) {
            // Use VAT from request
            vatPercentage = parseFloat(vat);
        } else if (category.vat) {
            // Use VAT from category
            vatPercentage = parseFloat(category.vat);
        }

        const vatAmount = (price * vatPercentage) / 100;

        return {
            vat_percentage: vatPercentage,
            vat_amount: vatAmount
        };
    }

    /**
     * Format delivery object
     *
     * @param {Object} dlv - Raw delivery object
     * @returns {Object} Formatted delivery object
     * @private
     */
    _formatDelivery(dlv) {
        if (!dlv) {
            return { days: 0, title: 'Standard' };
        }

        return {
            days: dlv.days || 0,
            title: dlv.title || 'Standard'
        };
    }

    /**
     * Round price to 2 decimal places
     *
     * @param {number} price - Price to round
     * @returns {number} Rounded price
     * @private
     */
    _roundPrice(price) {
        return Math.round(price * 100) / 100;
    }

    /**
     * Format multiple prices
     *
     * @param {Array} prices - Array of price parameters
     * @returns {Array} Array of formatted price objects
     */
    formatPrices(prices) {
        return prices.map(priceParams => this.formatPriceObject(priceParams));
    }

    /**
     * Merge price objects (for price lists)
     *
     * @param {Array} priceObjects - Array of price objects
     * @returns {Object} Merged result
     */
    mergePriceObjects(priceObjects) {
        if (!priceObjects || priceObjects.length === 0) {
            return { prices: [] };
        }

        // Extract all prices into flat array
        const allPrices = priceObjects
            .filter(obj => obj.prices && obj.prices.length > 0)
            .flatMap(obj => obj.prices);

        // Use first object as base
        const baseObject = priceObjects[0];

        return {
            ...baseObject,
            prices: allPrices
        };
    }
}

module.exports = PriceFormatterService;
