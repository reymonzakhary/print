const express = require('express');
const router = express.Router();

const HybridCalculationController = require('../controllers/HybridCalculationController');

/**
 * TEST ROUTES - V2 Logic with V1 Payload Format
 *
 * These routes accept the SAME payload as V1 (product array with calc_ref)
 * but use the NEW V2 calculation logic under the hood.
 *
 * Purpose: Test and compare new calculations with old ones before replacing.
 *
 * Usage:
 * 1. Send your existing V1 payload to these test routes
 * 2. Compare results with old calculation
 * 3. Once validated, we can replace old routes with hybrid controller
 *
 * Routes match V1 structure but with /test/v2-logic prefix
 */

/**
 * POST /test/v2-logic/suppliers/:supplier_id/categories/:slug/products/calculate/price
 *
 * Internal calculation (with margins) using V2 logic
 *
 * Request Body: V1 format
 * {
 *   "product": [
 *     { "key": "format", "value": "a4", ... },
 *     { "key": "printing-colors", "value": "44-full-color", ... },
 *     ...
 *   ],
 *   "quantity": 40,
 *   "vat": "21"
 * }
 */
router.post(
    '/suppliers/:supplier_id/categories/:slug/products/calculate/price',
    HybridCalculationController.calculateInternal
);

/**
 * POST /test/v2-logic/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price
 *
 * Shop calculation (without margins) using V2 logic
 *
 * Request Body: Same V1 format as above
 */
router.post(
    '/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price',
    HybridCalculationController.calculateShop
);

/**
 * POST /test/v2-pipeline/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price
 *
 * V2 PIPELINE calculation (NEW organized architecture)
 *
 * Uses the new V2 calculation pipeline with organized services:
 * - FormatService, CatalogueService, MachineCalculationService, etc.
 *
 * This is the FULLY ORGANIZED V2 system for better testing and scaling
 *
 * Request Body: Same V1 format
 */
router.post(
    '/v2-pipeline/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price',
    HybridCalculationController.calculateV2
);

/**
 * POST /test/v2-logic/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/list
 *
 * Price list calculation using V2 logic
 *
 * Request Body: V1 format + optional quantities array
 * {
 *   "product": [...],
 *   "quantity": 100,
 *   "quantities": [100, 250, 500, 1000, 2500]  // Optional
 * }
 */
router.post(
    '/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/list',
    HybridCalculationController.calculatePriceList
);

/**
 * POST /test/v2-logic/debug/transform
 *
 * Debug endpoint: See how V1 payload is transformed to V2 format
 * Does NOT run calculation - just shows transformation
 *
 * Useful for debugging transformation logic
 */
router.post(
    '/debug/transform',
    HybridCalculationController.debugTransform
);

module.exports = router;
