const mongoose = require("mongoose");
const Schema = mongoose.Schema;


const SupplierCatalogueSchema = Schema({
    tenant_id:{type: String, required: true},
    tenant_name:{type: String, required: true},
    pg_id:{type: String, required: true},
    art_nr:{type: String, required: true},
    material:{type: String, required: true},
    material_link:{type:  mongoose.Schema.Types.ObjectId, required: true},
    grs:{type: String, required: true},
    grs_link:{type:  mongoose.Schema.Types.ObjectId, required: true},
    calc_type: {type: String, default: 'kg'},
    price: {type: Number, default: 0},
});


module.exports = SupplierCatalogue = mongoose.model("supplier_catalogues", SupplierCatalogueSchema);
