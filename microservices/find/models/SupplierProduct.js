const mongoose = require("mongoose");
const Schema = mongoose.Schema;


const SupplierProductSchema = Schema({
    iso:{type: String, required: true},
    tenant_name: {type:String, required: true},
    tenant_id: {type:String, required: true, index: true},
    category_name: {type:String, required: true},
    category_display_name: {type:String, required: true},
    category_slug: {type:String, required: true},
    linked: {type:String, required: true, index: true}, // REFER
    supplier_category: {type:String, required: true, index: true}, // REFER
    shareable: {type:Boolean, default: true},
    published: {type:Boolean, default: true},
    object: {type:Object},
    created_at: {type:Date, default: Date.now, index: true},
});


module.exports = SupplierProduct = mongoose.model("supplier_products", SupplierProductSchema);
