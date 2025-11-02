const express = require('express');
const CalculationController = require('../controllers/CalculationController');
const ShopCalculationController = require("../controllers/ShopCalculationController");
const ShopCalculationPriceListController = require("../controllers/ShopCalculationPriceListController");
const SemiCalculationController = require('../controllers/SemiCalculationController');
const ShopSemiCalculationController = require("../controllers/ShopSemiCalculationController");
const ProductController = require('../controllers/ProductController');
const ShopSemiCalculationPriceListController = require('../controllers/ShopSemiCalculationPriceListController');
const router = express.Router();

/* GET home page. */
router.post('/suppliers/:supplier_id/categories/:slug/products/calculate/price', CalculationController.calculate);
/* get product with calculation for the shop */
router.post('/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price', ShopCalculationController.calculate);
/* get price list full calculated */
router.post('/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/list', ShopCalculationPriceListController.calculate);

/* get prices with semi-calculation */
router.post('/suppliers/:supplier_id/categories/:slug/products/calculate/price/semi', SemiCalculationController.newSemiCalculate);
/* get prices with semi-calculation for shop */
router.post('/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/semi', ShopSemiCalculationController.calculate);
/* get prices with semi-calculation for shop list*/
router.post('/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/semi/list', ShopSemiCalculationPriceListController.calculate);

/**
 *
 */
router.post('/suppliers/:supplier_id/products/items', ProductController.index);

module.exports = router;

