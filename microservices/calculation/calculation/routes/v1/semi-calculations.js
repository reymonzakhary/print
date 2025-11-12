const express = require('express');
const router = express.Router();

const SemiCalculationController = require('../../controllers/SemiCalculationController');
const ShopSemiCalculationController = require('../../controllers/ShopSemiCalculationController');
const ShopSemiCalculationPriceListController = require('../../controllers/ShopSemiCalculationPriceListController');

/**
 * Semi-Calculation Routes
 *
 * Semi-calculations provide simplified/preliminary pricing estimates.
 * These may be used for:
 * - Quick price quotes
 * - Simplified product configurations
 * - Reduced calculation complexity for specific product types
 *
 * Request Parameters:
 * - supplier_id: The tenant/supplier identifier
 * - slug: Category slug for product grouping
 *
 * Request Body (existing fields - DO NOT REMOVE):
 * - product: Product configuration (may be simplified compared to full calculation)
 * - quantity: Number of units
 * - vat: VAT percentage (for semi-calculation formatting)
 * - dlv: Delivery time filter (optional)
 * - vat_override: Whether to override category VAT (optional)
 * - contract: Optional contract pricing reference
 *
 * Response Format (existing structure - DO NOT REMOVE):
 * - type: "print"
 * - connection: supplier_id
 * - external: External system reference
 * - external_id: External identifier
 * - external_name: Tenant/supplier name
 * - calculation_type: "semi_calculation"
 * - items: Array of product items
 * - product: Original product request
 * - category: Category details
 * - margins: Margin calculations
 * - divided: Boolean indicating if calculation was split
 * - quantity: Requested quantity
 * - calculation: Calculation breakdown
 * - prices: Array of formatted price objects
 */

/**
 * POST /suppliers/:supplier_id/categories/:slug/products/calculate/price/semi
 *
 * Internal semi-calculation endpoint (includes profit margins)
 * Uses new calculation method (newSemiCalculate)
 */
router.post(
    '/suppliers/:supplier_id/categories/:slug/products/calculate/price/semi',
    SemiCalculationController.newSemiCalculate
);

/**
 * POST /shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/semi
 *
 * Shop semi-calculation endpoint (excludes internal profit data)
 * Used by customer-facing systems for quick quotes
 */
router.post(
    '/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/semi',
    ShopSemiCalculationController.calculate
);

/**
 * POST /shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/semi/list
 *
 * Shop semi-calculation price list endpoint
 * Generates semi-calculations for multiple quantity ranges
 * Returns consolidated price list
 *
 * Additional behavior:
 * - Uses category-defined quantity ranges
 * - Performs semi-calculation for each quantity
 * - Consolidates results into single response
 */
router.post(
    '/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/semi/list',
    ShopSemiCalculationPriceListController.calculate
);

module.exports = router;
