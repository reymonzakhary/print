var express = require('express');
var router = express.Router();

const CategoryCtrl = require("../controllers/category.controller");

router.get('/', CategoryCtrl.index);
router.get('/search', CategoryCtrl.search);
router.get('/:slug', CategoryCtrl.categoryBySlug);

module.exports = router;
