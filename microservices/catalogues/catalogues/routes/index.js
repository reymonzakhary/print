const express = require('express');
const CatalogueController = require('../controllers/CatalogueController');
const router = express.Router();

/* Machines controller group. */
router.delete('/suppliers/:supplier_id', CatalogueController.deleteTenantCatalogues);
router.get('/suppliers/:supplier_id/catalogues', CatalogueController.index);
router.post('/suppliers/:supplier_id/catalogues', CatalogueController.store);
router.put('/suppliers/:supplier_id/catalogues/:catalogue', CatalogueController.update);
router.delete('/suppliers/:supplier_id/catalogues/:catalogue', CatalogueController.destroy);

module.exports = router;

