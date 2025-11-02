const mongoose = require("mongoose");
const Schema = mongoose.Schema;


const categorySchema = Schema({
    iso:{type: String, required: true},
    sort:{type: Number, required: false},
    name:{type: String, required: true},
    display_name:{type: [], required: true},
    system_key:{type: String, required: true},
    slug:{type: String, required: true},
    description:{type: String, required: false},
    media:{type: []},
    published:{type: Boolean, required: true},
    checked:{type: Boolean, required: false},
    created_at:{type: Date, required: true},
    additional:{type: String, required: false},

});


module.exports = Category = mongoose.model("categories", categorySchema);
