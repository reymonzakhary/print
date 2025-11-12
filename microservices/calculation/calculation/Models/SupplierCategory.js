const mongoose = require("mongoose");
const Schema = mongoose.Schema;


const SupplierCategorySchema = Schema({
    tenant_id:{type: String, required: true},
    slug:{type: String, required: true},
    published: {type:Boolean, default: true},
    production_dlv: {type:Array, default: {},required: true},
    // Allow additional fields from database that aren't explicitly defined
    additional: [{
        machine: {type: Schema.Types.ObjectId, ref: 'supplier_machines'}
    }],
    machine: [{type: Schema.Types.ObjectId, ref: 'supplier_machines'}], // Legacy field
}, {
    strict: false, // Allow fields not defined in schema
    strictPopulate: false // Allow populating paths not in schema
});


module.exports = SupplierCategory = mongoose.model("supplier_categories", SupplierCategorySchema);
