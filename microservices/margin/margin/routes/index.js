'use strict'

const express = require('express');
const GeneralMarginController = require('../Controller/GeneralMarginController');
const CategoryMarginController = require('../Controller/CategoryMarginController');
const SupplierMarginController = require('../Controller/SupplierMarginController');
const router = express.Router();

/***
 * Tenant
 */
router.delete('/suppliers/:supplier_id', SupplierMarginController.deleteTenantMargins);

/**
 * General
 */
router.get('/margins/suppliers/:supplier_id/general', GeneralMarginController.index);
router.patch('/margins/suppliers/:supplier_id/general', GeneralMarginController.update);
router.put('/margins/suppliers/:supplier_id/general', GeneralMarginController.update);

/**
 * Categories
 */
router.get('/margins/tenants/:supplier_id/categories/:category_slug', CategoryMarginController.index);
router.patch('/margins/tenants/:supplier_id/categories/:category_slug', CategoryMarginController.update);
router.put('/margins/tenants/:supplier_id/categories/:category_slug', CategoryMarginController.update);


module.exports = router;
