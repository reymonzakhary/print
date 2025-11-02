var express = require('express');
var router = express.Router();

const OptionController = require("../controllers/option.controller");

router.get('/search', OptionController.search);

module.exports = router;
