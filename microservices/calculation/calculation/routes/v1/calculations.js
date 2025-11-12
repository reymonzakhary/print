const express = require('express');
const router = express.Router();

const CalculationController = require('../../controllers/CalculationController');
const ShopCalculationController = require('../../controllers/ShopCalculationController');
const ShopCalculationPriceListController = require('../../controllers/ShopCalculationPriceListController');

/**
 * Full Calculation Routes
 *
 * These routes perform complete product calculations including:
 * - Material costs
 * - Machine setup and runtime costs
 * - Color/ink costs
 * - Delivery options
 * - Margin calculations
 * - Final pricing with VAT
 *
 * Request Parameters:
 * - supplier_id: The tenant/supplier identifier
 * - slug: Category slug for product grouping
 *
 * Request Body (existing fields - DO NOT REMOVE):
 * - product: Array of product items with options, materials, formats
 * - quantity: Number of units to produce
 * - contract: Optional contract pricing reference
 *
 * Response Format (existing structure - DO NOT REMOVE):
 * - type: "print"
 * - connection: supplier_id
 * - external: External system reference
 * - external_id: External identifier
 * - external_name: Tenant/supplier name
 * - calculation_type: "full_calculation"
 * - items: Array of product items
 * - product: Original product request
 * - category: Category details
 * - margins: Margin calculations
 * - divided: Boolean indicating if calculation was split
 * - quantity: Requested quantity
 * - calculation: Detailed calculation breakdown
 * - prices: Array of price objects with variations
 */

/**
 * POST /suppliers/:supplier_id/categories/:slug/products/calculate/price
 *
 * Internal full calculation endpoint (includes profit margins)
 * Used by internal systems that need complete pricing breakdown
 */
router.post(
    '/suppliers/:supplier_id/categories/:slug/products/calculate/price',
    CalculationController.calculate
);

/**
 * POST /shop/suppliers/:supplier_id/categories/:slug/products/calculate/price
 *
 * Shop full calculation endpoint (excludes internal profit data)
 * Used by customer-facing systems (webshop, API integrations)
 */
router.post(
    '/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price',
    ShopCalculationController.calculate
);

/**
 * POST /shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/list
 *
 * Shop price list calculation endpoint
 * Calculates prices for multiple quantity ranges based on category configuration
 * Returns consolidated price list with all quantity variations
 *
 * Additional behavior:
 * - Automatically generates calculations for category-defined quantity ranges
 * - Consolidates results into single response with prices array
 * - Useful for displaying tiered pricing to customers
 */
router.post(
    '/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/list',
    ShopCalculationPriceListController.calculate
);

module.exports = router;
