const express = require('express');
const BoxController = require('../controllers/BoxController');
const router = express.Router();

/* Machines controller group. */
router.get('/suppliers/:supplier_id/boxes', BoxController.index);
router.get('/suppliers/:supplier_id/boxes/:box', BoxController.show);
router.post('/suppliers/:supplier_id/boxes', BoxController.store);
router.put('/suppliers/:supplier_id/boxes/:box', BoxController.update);
//
// router.get('/suppliers/:supplier_id/linked/:linked_id/categories', CategoryController.getByLinked);
// router.post('/suppliers/:supplier_id/categories/linked/:linked_id', CategoryController.showByLinkedId);
// router.put('/suppliers/:supplier_id/categories/:category', CategoryController.update);
// router.delete('/suppliers/:supplier_id/categories/:category', CategoryController.destroy);
//
// router.delete('/suppliers/:supplier_id/categories/:category/media', CategoryController.removeMedia);
//
//
// router.put('/suppliers/:supplier_id/categories/:category/boops', BoopsController.update);
// router.put('/suppliers/:supplier_id/categories/:category_slug/simple/boops', BoopsController.simpleUpdate);

module.exports = router;


