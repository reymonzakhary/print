const mongoose = require('mongoose');
const slugify = require('slugify');
const Schema = mongoose.Schema;

// Define the Option schema
const OptionSchema = new mongoose.Schema({
  sort: { type: Number, default: 0 },
  name: { type: String, required: true, unique: true },
  display_name: { type: [String], default: [] },
  sku: { type: String, unique: false },
  slug: { type: String, unique: true },
  system_key: { type: String, default: '' },
  description: { type: String, default: '' },
  media: { type: String, default: '' },
  tenant_id: { type: String, default: '' },
  tenant_name: { type: String, default: '' },
  dimension: { type: String, default: '2d' },
  dynamic: { type: Boolean, default: false },
  dynamic_keys: { type: Boolean, default: [] },
  start_on : { type: Boolean, default: 0 },
  end_on: { type: Boolean, default: 0 },
  dynamic_type : { type: String, default: '' },
  generate: { type: Boolean, default: false },
  width: { type: Number, default: 0 },
  maximum_width: { type: Number, default: 0 },
  minimum_width: { type: Number, default: 0 },
  height: { type: Number, default: 0 },
  maximum_height: { type: Number, default: 0 },
  minimum_height: { type: Number, default: 0 },
  length: { type: Number, default: 0 },
  maximum_length: { type: Number, default: 0 },
  minimum_length: { type: Number, default: 0 },
  unit: { type: String, default: 'mm' },
  incremental_by: { type: Number, default: 0 },
  published: { type: Boolean, default: true },
  has_children: { type: Boolean, default: false },
  input_type: { type: String, default: '' },
  extended_fields: { type: Map, of: String, default: {} },
  shareable: { type: Boolean, default: false },
  parent: { type: Boolean, default: true },
  start_cost: { type: Number, default: 0 },
  calculation_method: { type: [String], default: [] },
  rpm: { type: Number, default: 0 },
  sheet_runs: { type: [String], default: [] },
  runs: { type: [String], default: [] },
  information: { type: String, default: '' },
  children: [{ type: mongoose.Schema.Types.ObjectId, ref: 'Option', default: null }],
  additional: { type: Map, of: String, default: {} },
  created_at: { type: Date, default: Date.now, required: true }
}, {
  versionKey: false // You should be aware of the outcome after set to false
});

// Add indexes (equivalent to 'meta' in MongoEngine)
OptionSchema.index({ created_at: -1 });
OptionSchema.index({ slug: 1 });
OptionSchema.index({ name: 1 });

// Pre-save hook to auto-generate slug from the name field
OptionSchema.pre('save', function (next) {
  if (this.isModified('name')) {
    this.slug = slugify(this.name, { lower: true });
    this.system_key = slugify(this.name, { lower: true });
  }
  next();
});

// Set the default sorting (equivalent to 'ordering' in MongoEngine)
OptionSchema.set('timestamps', true);

// Create the Option model from the schema
const Option = mongoose.model('Option', OptionSchema);

module.exports = Option;
