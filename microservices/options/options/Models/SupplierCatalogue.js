const mongoose = require("mongoose");
const Schema = mongoose.Schema;
const ObjectId = Schema.Types.ObjectId

const SupplierCatalogueSchema = Schema({
    tenant_id:{type: String, required: true},
    tenant_name:{type: String, required: true},
    supplier:{type: String, required: true},
    art_nr:{type: String, required: true},
    material:{type: String, required: true},
    material_link:{type:  ObjectId, required: false},
    material_id:{type:  ObjectId, required: true},
    grs:{type: Number, required: true},
    grs_link:{type:  ObjectId, required: false},
    grs_id:{type:  ObjectId, required: true},
    price: {type: Number, default: 0},
    ean: {type: String, default: ''},
    density: {type: Number, default: 0},
    height: {type: Number, default: 0},
    length: {type: Number, default: 0},
    sheet: {type: Boolean, default: true},
    width: {type: Number, default: 0},
    calc_type: {type: String, default: 'kg'},
    created_at: {type:Date, default: Date.now, index: true},
    updated_at: {type:Date, default: Date.now, index: true},
},{
    versionKey: false // You should be aware of the outcome after set to false
});


module.exports = SupplierCatalogue = mongoose.model("supplier_catalogues", SupplierCatalogueSchema);
