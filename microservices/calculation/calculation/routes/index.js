const express = require('express');
const router = express.Router();

/**
 * Calculation API Main Router
 *
 * This file delegates to versioned route modules to maintain organization
 * while preserving backward compatibility.
 *
 * V1: Legacy calculation API (at root level)
 * V2: Enhanced calculation API with accurate digital printing (at /v2)
 * TEST: Hybrid routes - V1 format with V2 logic (at /test/v2-logic)
 *
 * All versions work simultaneously - no breaking changes.
 */

// const v1Routes = require('./v1');
const v2Routes = require('./v2');
const testV2LogicRoutes = require('./test-v2-logic');

// Mount V1 routes at root level for backward compatibility
// router.use('/', v1Routes);

// Mount V2 routes at /v2 prefix
router.use('/', v2Routes);


// Mount TEST routes at /test/v2-logic prefix
// These accept V1 payload but use V2 calculation logic
router.use('/test/v2-logic', testV2LogicRoutes);

module.exports = router;

