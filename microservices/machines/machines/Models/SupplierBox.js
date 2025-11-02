const mongoose = require("mongoose");
const Schema = mongoose.Schema;
const ObjectId = Schema.Types.ObjectId

const SupplierBoxSchema = Schema({
    iso:{type: String, required: true},
    row_id:{type: String, required: false},
    sort:{type: Number, required: false, default: 0},
    tenant_id:{type: String, required: true},
    tenant_name:{type: String, required: false},
    sku:{type: String, required: false, default:''},
    name:{type: String },
    display_name:{type: [], required: true},
    system_key:{type: String, required: true},
    input_type:{type: String, required: false},
    incremental:{type: Boolean, required: true},
    select_limit:{type: Number, required: true},
    option_limit:{type: Number, required: true},
    sqm:{type: Boolean, required: true},
    slug:{type: String, required: true},
    appendage:{type: String, required: true},
    calculation_type:{type: String, required: true},
    linked:{type: ObjectId, required: false},
    description:{type: String, required: false},
    media:{type: Array, required: false, default: []},
    shareable:{type: Boolean, default: true},
    published:{type: Boolean, default: true},
    created_at:{type: Date, required: true},
    additional:{type: Array, required: false},
});


module.exports = SupplierBox = mongoose.model("supplier_boxes", SupplierBoxSchema);
