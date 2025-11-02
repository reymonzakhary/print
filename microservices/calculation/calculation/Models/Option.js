const mongoose = require("mongoose");
const Schema = mongoose.Schema;


const OptionSchema = Schema({
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
    dynamic_keys : {type: Array, required: false, default: []},
    start_on : {type: Number, required: false, default: ''},
    end_on : {type: Number, required: false, default: ''},
    generate : {type: Boolean, required: false, default: ''},
    dynamic_type : {type: String, required: false, default: ''},
    // linked = db.ReferenceField(Option)
    linked: {type:String, required: true, index: true}, // REFER
    shareable: { type: Boolean, required: false, default: false },
    published: { type: Boolean, default: true },
    description: { type: String, required: false, default: '' },
    media: { type: String, default: '' },
    sku: { type: String, default: '' },
    rpm: { type: Number, default: '' },
    sheet_runs: { type: Array, default: '' },
    runs :  { type: Array, default: '' },
    created_at: { type: Date, required: true },
});


module.exports = Option = mongoose.model("options", OptionSchema);
