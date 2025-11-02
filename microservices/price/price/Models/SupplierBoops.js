const mongoose = require("mongoose");
const Schema = mongoose.Schema;

const SupplierBoopsSchema = Schema({
    tenant_id: { type: String, required: true },
    ref_id: { type: String, default: '' },
    ref_boops_name: { type: String, default: '' },
    tenant_name: { type: String, required: true },
    supplier_category: { type: Schema.Types.ObjectId, required: true },
    linked: { type: Schema.Types.ObjectId, required: true },
    display_name: { type: Array, required: true },
    system_key: { type: String, required: true },
    shareable: { type: Boolean, default: false },
    published: { type: Boolean, default: true },
    generated: { type: Boolean, default: true },
    name: { type: String, required: true },
    slug: { type: String, required: true },
    boops: { type: Array, default: [] },
    additional: { type: Schema.Types.Mixed, default: {} }
});

module.exports = SupplierBoops = mongoose.model("supplier_boops", SupplierBoopsSchema);
