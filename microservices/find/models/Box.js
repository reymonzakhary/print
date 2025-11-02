const mongoose = require("mongoose");
const Schema = mongoose.Schema;


const boxSchema = Schema({
    iso:{type: String, required: true},
    sort:{type: Number, required: false, default: 0},
    name:{type: String, required: true, unique: true},
    slug:{type: String, required: false, unique:true},
    description:{type: String, required: false, default: ''},
    media:{type: []},
    sqm:{type: Boolean, required: true, unique: false, default: false},
    published:{type: Boolean, default: true},
    input_type: {type: String, default: ''},
    created_at: {type: Date, required: true},
    // categories = db.ListField(db.ReferenceField(Category))
});


module.exports = Box = mongoose.model("boxes", boxSchema);
