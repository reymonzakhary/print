const mongoose = require("mongoose");
const slugify = require("slugify");
const Schema = mongoose.Schema;

const OpSchema = new Schema({
    id: { type: Schema.Types.ObjectId, required: true },
    ref_option: { type: String, default: null },
    name: { type: String, required: true },
    display_name: [
        {
            display_name: { type: String, required: true },
            iso: { type: String, required: true }
        }
    ],
    system_key: { type: String, required: true },
    slug: { type: String, required: true },
    source_slug: { type: Array, default: [] },
    description: { type: String, default: '' },
    media: { type: [String], default: [] },
    dimension: { type: Schema.Types.Mixed, default: null },
    dynamic: { type: Boolean, default: false },
    sheet_runs: { type: Schema.Types.Mixed, default: null },
    unit: { type: String, default: 'mm' },
    width: { type: Number, default: 0 },
    maximum_width: { type: Number, default: 0 },
    minimum_width: { type: Number, default: 0 },
    height: { type: Number, default: 0 },
    maximum_height: { type: Number, default: 0 },
    minimum_height: { type: Number, default: 0 },
    length: { type: Number, default: 0 },
    maximum_length: { type: Number, default: 0 },
    minimum_length: { type: Number, default: 0 },
    start_cost: { type: Number, default: 0 },
    rpm: { type: Number, default: 0 },
    information: { type: String, default: '' },
    input_type: { type: String, default: '' },
    linked: { type: String, default: '' },
    excludes: { type: Array, default: [] },
    dynamic_keys: { type: [String], default: [] },
    start_on: { type: Number, default: 0 },
    end_on: { type: Number, default: 0 },
    dynamic_type: { type: String, default: 'integer' },
    generate: { type: Boolean, default: false },
    dynamic_object: { type: Schema.Types.Mixed, default: null }
}, { _id: false }); // Prevent automatic creation of _id for nested schema

const BoopSchema = new Schema({
    id: { type: Schema.Types.ObjectId, required: true },
    iso: { type: String, required: true },
    name: { type: String, required: true },
    display_name: [
        {
            display_name: { type: String, required: true },
            iso: { type: String, required: true }
        }
    ],
    system_key: { type: String, required: true },
    slug: { type: String, required: true },
    source_slug: { type: String, default: null },
    description: { type: String, default: '' },
    ref_box: { type: String, default: '' },
    sqm: { type: Boolean, default: false },
    appendage: { type: Boolean, default: false },
    calculation_type: { type: String, default: '' },
    media: { type: [String], default: [] },
    input_type: { type: String, default: '' },
    linked: { type: String, default: '' },
    published: { type: Boolean, default: true },
    divider: { type: String, default: '' },
    ops: { type: [OpSchema], default: [] } // Array of OpSchema
}, { _id: false }); // Prevent automatic creation of _id for nested schema

const SupplierBoopsSchema = new Schema({
    tenant_id: { type: String, required: true },
    ref_id: { type: String, default: '' },
    ref_boops_name: { type: String, default: '' },
    tenant_name: { type: String, required: true },
    supplier_category: { type: Schema.Types.ObjectId, required: true },
    linked: { type: Schema.Types.ObjectId, required: false },
    display_name: { type: Array, required: true },
    system_key: { type: String, required: true},
    shareable: { type: Boolean, default: false },
    published: { type: Boolean, default: true },
    generated: { type: Boolean, default: true },
    divided: { type: Boolean, required: false, default: false },
    name: { type: String, required: true },
    slug: { type: String, required: true },
    source_slug: { type: String, default: null },
    boops: { type: [BoopSchema], default: [] }, // Array of BoopSchema
    additional: { type: Schema.Types.Mixed, default: {} }
}, { versionKey: false });

// Create a compound unique index on system_key and tenant_id
SupplierBoopsSchema.index({ system_key: 1, tenant_id: 1 }, { unique: true });

// Pre-save hook to generate slug and system_key
SupplierBoopsSchema.pre('save', async function (next) {
    if (this.name) {
        this.slug = slugify(this.name, { lower: true });
        this.system_key = slugify(this.system_key, { lower: true });
    }
    next();
});

module.exports = SupplierBoops = mongoose.model("supplier_boops", SupplierBoopsSchema);
