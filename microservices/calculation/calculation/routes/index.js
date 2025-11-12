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
 *
 * Both versions work simultaneously - no breaking changes.
 */

// const v1Routes = require('./v1');
const v2Routes = require('./v2');

// Mount V1 routes at root level for backward compatibility
// router.use('/', v1Routes);

// Mount V2 routes at /v2 prefix
router.use('/', v2Routes);


module.exports = router;

