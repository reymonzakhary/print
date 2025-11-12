const { findDiscountSlot } = require('../Helpers/Helper');

/**
 * DiscountService
 *
 * Handles discount calculations and contract pricing.
 */
class DiscountService {
    /**
     * Get applicable discount for quantity and category
     *
     * @param {Object} contract - Contract object with discounts
     * @param {string} categoryId - Category ID
     * @param {number} quantity - Quantity
     * @returns {Object|null} Discount object or null
     */
    getDiscount(contract, categoryId, quantity) {
        if (!contract) {
            return null;
        }

        // Use existing helper function to find discount slot
        return findDiscountSlot(contract, categoryId, quantity);
    }

    /**
     * Apply discount to a price
     *
     * @param {number} price - Original price
     * @param {Object} discount - Discount object
     * @returns {Object} Price with discount applied
     */
    applyDiscount(price, discount) {
        if (!discount || !discount.value) {
            return {
                original_price: price,
                discounted_price: price,
                discount_amount: 0,
                discount: null
            };
        }

        let discountAmount = 0;

        if (discount.type === 'percentage') {
            discountAmount = (price * discount.value) / 100;
        } else if (discount.type === 'fixed') {
            discountAmount = discount.value;
        }

        const discountedPrice = price - discountAmount;

        return {
            original_price: price,
            discounted_price: Math.max(0, discountedPrice), // Ensure non-negative
            discount_amount: discountAmount,
            discount: discount
        };
    }

    /**
     * Check if discount is applicable
     *
     * @param {Object} discount - Discount object
     * @param {number} quantity - Quantity
     * @returns {boolean} True if discount is applicable
     */
    isApplicable(discount, quantity) {
        if (!discount) {
            return false;
        }

        // Check minimum quantity
        if (discount.min_quantity && quantity < discount.min_quantity) {
            return false;
        }

        // Check maximum quantity
        if (discount.max_quantity && quantity > discount.max_quantity) {
            return false;
        }

        // Check if discount is active (if has date constraints)
        if (discount.start_date || discount.end_date) {
            const now = new Date();

            if (discount.start_date && new Date(discount.start_date) > now) {
                return false;
            }

            if (discount.end_date && new Date(discount.end_date) < now) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate total discount across multiple items
     *
     * @param {Array} items - Array of items with prices
     * @param {Object} discount - Discount to apply
     * @returns {number} Total discount amount
     */
    calculateTotalDiscount(items, discount) {
        if (!items || items.length === 0 || !discount) {
            return 0;
        }

        let totalDiscount = 0;

        for (const item of items) {
            const result = this.applyDiscount(item.price, discount);
            totalDiscount += result.discount_amount;
        }

        return totalDiscount;
    }
}

module.exports = DiscountService;
