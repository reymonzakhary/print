const express = require('express');
const CategoryController = require('../controllers/CategoryController');
const BoopsController = require('../controllers/BoopsController');
const router = express.Router();

/* Machines controller group. */
router.get('/suppliers/:supplier_id/categories', CategoryController.index);
router.get('/suppliers/:supplier_id/categories/shared', CategoryController.countSharable);
router.get('/suppliers/:supplier_id/categories/:category', CategoryController.show);
router.get('/suppliers/:supplier_id/linked/:linked_id/categories', CategoryController.getByLinked);
router.post('/suppliers/:supplier_id/categories/linked/:linked_id', CategoryController.showByLinkedId);
router.post('/suppliers/:supplier_id/categories', CategoryController.store);
router.put('/suppliers/:supplier_id/categories/:category', CategoryController.update);
router.delete('/suppliers/:supplier_id/categories/:category', CategoryController.destroy);

router.delete('/suppliers/:supplier_id/categories/:category/media', CategoryController.removeMedia);


router.put('/suppliers/:supplier_id/categories/:category/boops', BoopsController.update);
router.put('/suppliers/:supplier_id/categories/:category_slug/simple/boops', BoopsController.simpleUpdate);


module.exports = router;


