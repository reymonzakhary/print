const express = require('express');
const router = express.Router();

/**
 * V1 API Routes
 *
 * This is the main routing file for V1 calculation API.
 * All routes maintain backward compatibility with existing implementations.
 *
 * Route Organization:
 * - /calculations - Full calculation endpoints
 * - /semi-calculations - Semi/simplified calculation endpoints
 * - /products - Product data retrieval endpoints
 *
 * BACKWARD COMPATIBILITY GUARANTEE:
 * - All existing request/response formats are maintained
 * - All existing route paths are preserved
 * - No existing fields are removed or renamed
 * - New optional fields may be added in future versions
 */

const calculationRoutes = require('./calculations');
const semiCalculationRoutes = require('./semi-calculations');
const productRoutes = require('./products');

// Mount route modules
router.use('/', calculationRoutes);
router.use('/', semiCalculationRoutes);
router.use('/', productRoutes);

module.exports = router;
