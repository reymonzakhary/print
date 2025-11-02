const mongoose = require('mongoose');
const slugify = require('slugify');

// Define the Box schema
const BoxSchema = new mongoose.Schema({
  sort: { type: Number, default: 0 },
  tenant_id: { type: String, default: null },
  tenant_name: { type: String, required: false },
  sku: { type: String, default: '' },
  name: { type: String, required: true, unique: true },
  display_name: { type: [String], default: [] },
  system_key: { type: String, default: '' },
  slug: { type: String, unique: true },
  description: { type: String, default: '' },
  media: { type: [String], default: [] },
  sqm: { type: Boolean, required: true, default: false },
  appendage: { type: Boolean, required: true, default: false },
  calculation_type: { type: String, default: '' },
  published: { type: Boolean, default: true },
  input_type: { type: String, default: '' },
  incremental: { type: Boolean, required: true, default: false },
  select_limit: { type: Number, default: 0 },
  option_limit: { type: Number, default: 0 },
  shareable: { type: Boolean, default: true },
  start_cost: { type: Number, default: 0 },
  created_at: { type: Date, default: Date.now, required: true },
  categories: [{ type: mongoose.Schema.Types.ObjectId, ref: 'Category' }],
  additional: { type: Map, of: String, default: {} },
}, {
  versionKey: false // You should be aware of the outcome after set to false
});

// Add an index to the schema (equivalent to 'meta' in MongoEngine)
BoxSchema.index({ created_at: -1 });
BoxSchema.index({ slug: 1 });
BoxSchema.index({ name: 1 });
BoxSchema.index({ categories: 1 });

// Pre-save hook to auto-generate slug from the name field
BoxSchema.pre('save', function (next) {
  if (this.isModified('name')) {
    this.slug = slugify(this.name, { lower: true });
    this.system_key = slugify(this.name, { lower: true });
  }
  next();
});

// Set the default sorting (equivalent to 'ordering' in MongoEngine)
BoxSchema.set('timestamps', true);

// Create the Box model from the schema
const Box = mongoose.model('Box', BoxSchema);

module.exports = Box;
