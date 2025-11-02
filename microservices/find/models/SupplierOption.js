const mongoose = require("mongoose");
const Schema = mongoose.Schema;


const SupplierOptionSchema = Schema({
    iso: { type: String, required: true },
    sort: { type: Number, required: false, default: 0 },
    name: { type: String, required: true, unique_with: 'tenant_id' },
    display_name: { type: String, required: true },
    slug: { type: String, required: false, unique_with: 'tenant_id' },
    tenant_id: { type: String, required: true },
    tenant_name: { type: String, required: false },
    unit: { type: String, required: false },
    maximum: { type: Number, required: false },
    minimum: { type: Number, required: false },
    incremental_by: { type: Number, required: false },
    information: { type: String, required: false },
    input_type: { type: String, required: false },
    // linked = db.ReferenceField(Option)
    linked: {type:String, required: true, index: true}, // REFER
    shareable: { type: Boolean, required: false, default: false },
    published: { type: Boolean, default: true },
    description: { type: String, required: false, default: '' },
    media: { type: String, default: '' },
    sku: { type: String, default: '' },
    created_at: { type: Date, required: true },
});


module.exports = SupplierOption = mongoose.model("supplier_options", SupplierOptionSchema);
