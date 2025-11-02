var express = require('express');
var router = express.Router();

const BoxCtrl = require("../controllers/box.controller");

router.get('/search', BoxCtrl.search);

module.exports = router;
