const express = require('express');
const OptionController = require('../controllers/OptionController');
const OptionCategoryController = require('../controllers/OptionCategoryController');
const router = express.Router();

/* General Options controller group. */
router.get('/suppliers/:supplier_id/options', OptionController.index);
router.post('/suppliers/:supplier_id/options', OptionController.store);
// router.delete('/suppliers/:supplier_id/options/:option_id', OptionController.destroy);


/*  Options by Category controller group */
router.get('/suppliers/:supplier_id/categories/:category_id/options/:option_id', OptionCategoryController.show);
router.put('/suppliers/:supplier_id/categories/:category_id/options/:option_id', OptionCategoryController.update);
router.delete('/suppliers/:supplier_id/categories/:category_id/options/:option_id', OptionCategoryController.destroy);


module.exports = router;


