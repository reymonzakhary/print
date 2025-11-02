const mongoose = require("mongoose");
const Schema = mongoose.Schema;

const ObjectId = Schema.Types.ObjectId

const SupplierCategorySchema = Schema({
    sort:{type: Number, required: false, default: 0},
    tenant_id:{type: String, required: true},
    tenant_name:{type: String, required: true},
    countries: {type: Array, required: false, default: []},
    sku: {type: String, required: false, default: ''},
    name: {type: String, required: true},
    system_key: {type: String, required: true},
    display_name: {type: Array, required: true},
    slug:{type: String, required: true},
    description:{type: String, required: false, default: ''},
    shareable:{type: Boolean, required: false, default: false},
    published: {type:Boolean, required: false, default: true},
    media: {type: Array, required: false, default: []},
    price_build: {type: Object, required: false, default: {}},
    has_products: {type:Boolean, required: false, default: false},
    has_manifest: {type:Boolean, required: false, default: false},
    calculation_method: {type: Array, required: false, default: []},
    dlv_days: {type: Array, required: false, default: []},
    printing_method: {type: Array, required: false, default: []},
    production_days: {type: Array, required: false, default: []},
    start_cost: {type: Number, required: false, default: 0},
    linked: {type: ObjectId, required: false},
    ref_id: {type: String, required: false, default: ''},
    ref_category_name: {type: String, required: false, default: ''},
    created_at: {type:Date, default: Date.now, index: true},
    updated_at: {type:Date, default: Date.now, index: true},
    additional: {type: Array, required: false, default: []},
});


module.exports = SupplierCategory = mongoose.model("supplier_categories", SupplierCategorySchema);
