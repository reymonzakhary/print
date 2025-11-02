const mongoose = require("mongoose");
const Schema = mongoose.Schema;


const SupplierMachineSchema = Schema({
    tenant_id:{type: String, required: true},
    tenant_name: {type: String, required: true},
    pg_id: {type: Number, required: true},
    name: {type: String, required: true},
    description: {type: String, required: false},
    type: {type: String, required: true},
    unit: {type: String, required: false, default: 'mm'},
    width: {type: Number, required: true},
    height: {type: Number, required: true},
    spm: {type: Number, required: false},
    price: {type: Number, required: false, default: 0},
    'default': {type: Boolean, required: false, default: false},
    sqcm: {type: Number, required: true},
    ean: {type: Number, required: true},
    pm: {type: String, required: true},
    setup_time: {type: Number, required: false, default: 0},
    cooling_time: {type: Number, required: false, default:0},
    cooling_time_per: {type: Number, required: false, default: 0},
    mpm: {type: Number, required: false, default: 0},
    divide_start_cost: {type: Boolean, required: false, default: false},
    spoilage: {type: Number, required: false, default: 0},
    wf: {type: Number, required: false, default: 0},
    min_gsm: {type: Number, required: false, default: 0},
    max_gsm: {type: Number, required: false, default: 0},
    colors: {type: Array, required: false},
    fed: {type: String, required: true, default:"sheet"},
    attributes: {type: Array, required: false},
    trim_area: {type: Number, required: false, default: 0},
    trim_area_exclude_y: {type: Boolean, required: false, default: false},
    trim_area_exclude_x: {type: Boolean, required: false, default: false},
    margin_right: {type: Number, required: false, default: 0},
    margin_left: {type: Number, required: false, default: 0},
    margin_top: {type: Number, required: false, default: 0},
    margin_bottom: {type: Number, required: false, default: 0},
    created_at: {type:Date, default: Date.now, index: true},
    updated_at: {type:Date, default: Date.now, index: true},
},{
    versionKey: false // You should be aware of the outcome after set to false
});


module.exports = SupplierMachine = mongoose.model("supplier_machines", SupplierMachineSchema);
