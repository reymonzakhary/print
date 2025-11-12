const mongoose = require("mongoose");
const Schema = mongoose.Schema;


const SupplierBoopsSchema = Schema({
    tenant_id: {type: String, required: true},
    supplier_category: {type: Schema.Types.ObjectId, required: true, ref: 'supplier_categories'},
    tenant_name: {type: String, required: true},
    name: {type: String, required: true},
    slug: {type: String, required: true},
    system_key: {type: String, required: true},
    published: {type: Boolean, default: true},
    boops: {type: Array, default: []}, // Product boxes configuration
}, {
    strict: false, // Allow fields not defined in schema
    versionKey: false
});


module.exports = SupplierBoops = mongoose.model("supplier_boops", SupplierBoopsSchema);
