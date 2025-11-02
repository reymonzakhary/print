const express = require('express');
const MachineController = require('../controllers/MachineController');
const router = express.Router();

/* Machines controller group. */
router.get('/suppliers/:supplier_id/machines', MachineController.index);
router.post('/suppliers/:supplier_id/machines', MachineController.store);
router.put('/suppliers/:supplier_id/machines/:machine', MachineController.update);
router.delete('/suppliers/:supplier_id/machines/:machine', MachineController.destroy);

module.exports = router;

