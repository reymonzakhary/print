var express = require('express');
var router = express.Router();

// const Category = require("../models/Category");

const ProductCtrl = require("../controllers/product.controller");

/* GET users listing. */
router.post('/:slug', ProductCtrl.productsBySlug);
router.post('/shop/:slug', ProductCtrl.getProducts);

module.exports = router;
