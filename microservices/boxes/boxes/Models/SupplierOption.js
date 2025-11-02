const mongoose = require("mongoose");
const slugify = require("slugify");
const Schema = mongoose.Schema;
const ObjectId = Schema.Types.ObjectId


const SupplierOptionSchema = Schema({
  sort: { type: Number, required: false, default: 0 },
  name: { type: String, required: true, unique_with: 'tenant_id' },
  source_slug: { type: Array, required: false, default: [] },
  display_name: { type: [], required: true },
  slug: { type: String, required: false, unique_with: 'tenant_id' },
  tenant_id: { type: String, required: true },
  system_key: { type: String, default: '' },
  has_children: { type: Boolean, default: false },
  extended_fields: { type: Map, of: String, default: {} },
  dimension: { type: String, default: '2d' },
  dynamic: { type: Boolean, default: false },
  width: { type: Number, default: 0 },
  maximum_width: { type: Number, default: 0 },
  minimum_width: { type: Number, default: 0 },
  height: { type: Number, default: 0 },
  maximum_height: { type: Number, default: 0 },
  minimum_height: { type: Number, default: 0 },
  length: { type: Number, default: 0 },
  maximum_length: { type: Number, default: 0 },
  minimum_length: { type: Number, default: 0 },
  parent: { type: Boolean, default: true },
  start_cost: { type: Number, default: 0 },
  boxes: { type: Array, default: '' },
  additional: { type: Object, required: false, default: {} },
  tenant_name: { type: String, required: false },
  unit: { type: String, required: false , default: 'mm'},
  maximum: { type: Number, required: false },
  minimum: { type: Number, required: false },
  incremental_by: { type: Number, required: false },
  information: { type: String, required: false },
  input_type: { type: String, required: false },
  dynamic_object: {type: Schema.Types.Mixed, required: function () {return this.dynamic}, default: {}},

  dynamic_keys: {type: Array, required: function () { return this.dynamic }},
  start_on: {type: Number, required: false},
  end_on: {type: Number, required: false},
  generate: {type: Boolean, required: false},
  dynamic_type: {type: String, required: false, default: ''},

  linked: { type: ObjectId, required: false, index: true , default: null},
  shareable: { type: Boolean, required: false, default: false },
  published: { type: Boolean, default: true },
  description: { type: String, required: false, default: '' },
  media: { type: Array, default: [] },
  sku: { type: String, default: '',unique:false },
  rpm: { type: Number, default: 0 },
  sheet_runs: { type: Array, default: [] },
  runs: { type: Array, default: [] },
  created_at: { type: Date, default: Date.now }
}, {
  versionKey: false // You should be aware of the outcome after set to false
});

SupplierOptionSchema.pre('save', function (next) {
  if (this.name) {
    this.slug = slugify(this.name, { lower: true });
    this.system_key = slugify(this.name, { lower: true });
  }
  next();
})


module.exports = SupplierOption = mongoose.model("supplier_options", SupplierOptionSchema);
