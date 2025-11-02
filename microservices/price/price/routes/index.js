const express = require('express');
const router = express.Router();
const CollectionPricesController = require('../controllers/CollectionPrices/CollectionPricesController');

/* get product with calculation for the shop */
router.post('/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/collection', CollectionPricesController.calculate);

module.exports = router;

