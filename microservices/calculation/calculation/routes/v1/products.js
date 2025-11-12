const express = require('express');
const router = express.Router();

const ProductController = require('../../controllers/ProductController');

/**
 * Product Routes
 *
 * These routes handle product-related data retrieval without performing calculations
 */

/**
 * POST /suppliers/:supplier_id/products/items
 *
 * Retrieves product items/configuration for given products
 * Used to fetch available options, materials, and attributes before calculation
 *
 * Request Parameters:
 * - supplier_id: The tenant/supplier identifier
 *
 * Request Body (existing fields - DO NOT REMOVE):
 * - products: Array of product identifiers
 * - calculation_type: Type of calculation ("full_calculation" or "semi_calculation")
 *
 * Response Format (existing structure - DO NOT REMOVE):
 * Returns array of product items with:
 * - Product identifiers
 * - Available options
 * - Material specifications
 * - Format options
 * - Constraints and validations
 *
 * Error Response:
 * - message: Error description
 * - status: 422 (validation error)
 */
router.post(
    '/suppliers/:supplier_id/products/items',
    ProductController.index
);

module.exports = router;
