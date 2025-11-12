const express = require('express');
const router = express.Router();

const CalculationController = require('../../controllers/CalculationControllerV2Enhanced');

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
    '/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price',
    CalculationController.calculate
);


module.exports = router;
