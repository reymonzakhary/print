const express = require('express');
const router = express.Router();

/**
 * Calculation API Main Router
 *
 * This file delegates to versioned route modules to maintain organization
 * while preserving backward compatibility.
 *
 * All routes are currently served under V1 structure.
 * Future versions can be added alongside V1 without breaking existing integrations.
 */

const v1Routes = require('./v1');

// Mount V1 routes at root level for backward compatibility
router.use('/', v1Routes);

module.exports = router;

