const mongoose = require("mongoose");
const slugify = require("slugify");
const Schema = mongoose.Schema;
const ObjectId = Schema.Types.ObjectId

const SupplierBoxSchema = Schema({
    row_id:{type: String, required: false},
    sort:{type: Number, required: false, default: 0},
    tenant_id:{type: String, required: true},
    tenant_name:{type: String, required: false},
    sku:{type: String, required: false, default:''},
    name:{type: String },
    source_slug:{type: String, required: false, default:'' },
    display_name:{type: [], required: true},
    system_key:{type: String, required: false},
    input_type:{type: String, required: false},
    incremental:{type: Boolean, required: true,default: false},
    select_limit:{type: Number, required: false,default: 0},
    option_limit:{type: Number, required: false,default: 0},
    sqm:{type: Boolean, required: true},
    slug:{type: String, required: true},
    appendage:{type: String, required: true},
    calculation_type:{type: String, required: false, default:''},
    linked: { type: ObjectId, required: false, index: true , default: null},
    description:{type: String, required: false},
    media:{type: Array, required: false, default: []},
    shareable:{type: Boolean, default: true},
    published:{type: Boolean, default: true},
    created_at: { type: Date, default: Date.now },
    additional:{type: Array, required: false},
}, {
  versionKey: false // You should be aware of the outcome after set to false
});

SupplierBoxSchema.pre('save', function(next) {
  if (this.name) {
    this.slug = slugify(this.name, {lower: true});
    this.system_key = slugify(this.name, { lower: true });
  }
  next();
})


module.exports = SupplierBox = mongoose.model("supplier_boxes", SupplierBoxSchema);
