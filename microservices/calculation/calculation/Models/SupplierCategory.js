const mongoose = require("mongoose");
const Schema = mongoose.Schema;


const SupplierCategorySchema = Schema({
    tenant_id:{type: String, required: true},
    slug:{type: String, required: true},
    published: {type:Boolean, default: true},
    production_dlv: {type:Array, default: {},required: true},
});


module.exports = SupplierCategory = mongoose.model("supplier_categories", SupplierCategorySchema);
