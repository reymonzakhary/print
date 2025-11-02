const mongoose = require("mongoose");
const Schema = mongoose.Schema;


const optionSchema = Schema({
    iso: { type: String, required: true },
    sort: { type: Number, required: false, default: 0 },
    name: { type: String, required: true, unique: true },
    slug: { type: String, required: false, unique: true },
    description: { type: String, required: false, default: '' },
    media: { type: String, default: '' },
    unit: { type: String, required: false, default: '' },
    maximum: { type: Number, default: 0 },
    minimum: { type: Number, default: 0 },
    incremental_by: { type: Number, default: 0 },
    information: { type: String, default: '' },
    published: { type: Boolean, default: true },
    input_type: { type: String, default: '' },
    created_at: { type: Date, required: true },
    // children = db.ListField(db.ReferenceField('self', default=None),  default=None)
});


module.exports = Option = mongoose.model("options", optionSchema);
