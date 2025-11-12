const express = require('express');
const router = express.Router();


const calculationRoutes = require('./calculations');


/**
 * V2 API Routes
 *
 * Enhanced calculation API with accurate, machine-specific calculations.
 *
 * Key improvements over V1:
 * - Clean, structured input format
 * - Dedicated calculators for each machine type
 * - Accurate digital printing calculations
 * - Better cost breakdown with timing
 * - Support for multiple finishing operations
 * - Machine comparison in single request
 *
 * V2 does NOT break V1 - both APIs work simultaneously.
 * V1 remains at: /suppliers/:id/categories/:slug/products/calculate/price
 * V2 is at: /v2/calculate
 */

/**
 * POST /v2/calculate
 *
 * Main calculation endpoint (auto-detects internal vs shop based on request)
 *
 * Request Body:
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
 *   "finishing": [],
 *   "contract": null,
 *   "internal": false
 * }
 */
// router.post('/calculate', CalculationController.calculate);

/**
 * POST /v2/calculate/internal
 *
 * Internal calculation (includes margins and profit)
 * Same request format as /calculate but forces internal=true
 */
// router.post('/calculate/internal', CalculationController.calculateInternal);

/**
 * POST /v2/calculate/shop
 *
 * Shop calculation (excludes internal margins and profit)
 * Same request format as /calculate but forces internal=false
 */
// router.post('/calculate/shop', CalculationController.calculateShop);

/**
 * POST /v2/calculate/price-list
 *
 * Calculate prices for multiple quantities
 *
 * Request Body:
 * {
 *   ...standard fields,
 *   "quantities": [100, 250, 500, 1000, 2500]
 * }
 *
 * Returns prices for each quantity in the array
 */
// router.post('/calculate/price-list', CalculationController.calculatePriceList);

/**
 * GET /v2/calculate/machine-types
 *
 * Get available machine types and their configuration requirements
 * Useful for building dynamic forms
 */
// router.get('/calculate/machine-types', CalculationController.getMachineTypes);


// Mount route modules
router.use('/', calculationRoutes);

module.exports = router;
module.exports = router;
