const mongoose = require("mongoose");
const Schema = mongoose.Schema;


const SupplierMachineSchema = Schema({
    tenant_id: {type: String, required: true},
    tenant_name: {type: String, required: true},
    name: {type: String, required: true},
    description: {type: String, default: ""},
    type: {type: String, required: true},
    unit: {type: String, default: 'mm'},
    width: {type: Number, required: true},
    height: {type: Number, required: true},
    spm: {type: Number, default: 0},
    price: {type: Number, default: 0},
    sqcm: {type: Number, required: true},
    ean: {type: Number, required: true},
    pm: {type: String, required: true},
    setup_time: {type: Number, default: 0},
    cooling_time: {type: Number, default: 0},
    cooling_time_per: {type: Number, default: 0},
    mpm: {type: Number, default: 0},
    divide_start_cost: {type: Boolean, default: false},
    spoilage: {type: Number, default: 0},
    wf: {type: Number, default: 0},
    min_gsm: {type: Number, default: 0},
    max_gsm: {type: Number, default: 0},
    colors: {type: Array, default: []},
    materials: {type: Array, default: []},
    printable_frame_length_min: {type: Number, default: 0},
    printable_frame_length_max: {type: Number, default: 0},
    fed: {type: String, default: "sheet"},
    attributes: {type: Array, default: []},
    trim_area: {type: Number, default: 0},
    trim_area_exclude_y: {type: Boolean, default: false},
    trim_area_exclude_x: {type: Boolean, default: false},
    margin_right: {type: Number, default: 0},
    margin_left: {type: Number, default: 0},
    margin_top: {type: Number, default: 0},
    margin_bottom: {type: Number, default: 0},
    created_at: {type: Date, default: Date.now, index: true},
    updated_at: {type: Date, default: Date.now, index: true},
}, {
    strict: false, // Allow fields not defined in schema (for future additions)
    versionKey: false
});


module.exports = SupplierMachine = mongoose.model("supplier_machines", SupplierMachineSchema);
