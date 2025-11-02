'use strict'

const mongoose = require("mongoose");
const Schema = mongoose.Schema;

const SupplierMarginSchema = Schema({
    tenant_id: {type: String, required: true},
    margin: {type: Array, required: true},
});

module.exports = mongoose.model("supplier_margins", SupplierMarginSchema);