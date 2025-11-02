const mongoose = require("mongoose");
const slugify = require("slugify");
const Schema = mongoose.Schema;

const BoopOperationSchema = new Schema({
    id: { type: Schema.Types.ObjectId, required: true },
    sort: { type: Number, default: 0 },
    name: { type: String, required: true },
    slug: { type: String },
    sku: { type: String },
    unit: { type: String },
    media: { type: [String], default: [] },
    additional: { type: [String], default: [] },
    tenant_id: { type: String },
    tenant_name: { type: String },
    dimension: { type: String, default: '2d' },
    excludes: { type: [String], default: [] },
    start_on: { type: Number, default: 0 },
    end_on: { type: Number, default: 0 },
    generate: { type: Boolean, default: false },
    description: { type: String },
    information: { type: String },
    input_type: { type: String },
    system_key: { type: String, required: true },
    linked: { type: String, default: "" },
    dynamic: { type: Boolean, default: false },
    dynamic_keys: { type: [String], default: [] },
    extended_fields: { type: [String], default: [] },
    rpm: { type: Number, default: 0 },
    incremental_by: { type: Number, default: 0 },
    published: { type: Boolean, default: true },
    shareable: { type: Boolean, default: false },
    parent: { type: Boolean, default: false },
    has_children: { type: Boolean, default: false },
    start_cost: { type: Number, default: 0 },
    calculation_method: { type: Number, default: 0 },
    height: { type: Number, default: 0 },
    minimum_height: { type: Number, default: 0 },
    maximum_height: { type: Number, default: 0 },
    width: { type: Number, default: 0 },
    minimum_width: { type: Number, default: 0 },
    maximum_width: { type: Number, default: 0 },
    length: { type: Number, default: 0 },
    minimum_length: { type: Number, default: 0 },
    maximum_length: { type: Number, default: 0 },
    display_name: { type: [Schema.Types.Mixed], default: [] } // Multi-language support
}, { _id: false });

const BoopSchema = new Schema({
    id: { type: Schema.Types.ObjectId, required: true },
    system_key: { type: String, required: true },
    name: { type: String, required: true },
    calculation_type: { type: String, default: "" },
    tenant_id: { type: String, default: "" },
    tenant_name: { type: String, default: "" },
    description: { type: String, default: "" },
    slug: { type: String },
    option_limit: { type: Number, default: 0 },
    select_limit: { type: Number, default: 0 },
    sort: { type: Number, default: 0 },
    linked: { type: String, default: '' },
    published: { type: Boolean, default: true },
    incremental: { type: Boolean, default: false },
    shareable: { type: Boolean, default: false },
    divider: { type: String },
    sqm: { type: Number, default: 0 },
    sku: { type: String },
    input_type: { type: String },
    appendage: { type: Boolean, default: false },
    ops: { type: [BoopOperationSchema], default: [] },
    display_name: { type: [Schema.Types.Mixed], default: [] }
}, { _id: false });

const SystemManifestSchema = new Schema({
    name: { type: String, default: null },
    sort: { type: Number, default: 0 },
    sku: { type: String },
    description: { type: String, default: "" },
    slug: { type: String, required: true, unique: true },
    tenant_id: { type: String, default: null },
    ref_id: { type: String, default: '' },
    ref_boops_id: { type: String, default: "" },
    ref_boops_name: { type: String, default: '' },
    category: { type: Schema.Types.ObjectId, ref: "Category", required: true },
    tenant_name: { type: String, default: null },
    divided: { type: Boolean, default: false },
    system_key: { type: String, required: true, unique: true },
    shareable: { type: Boolean, default: false },
    published: { type: Boolean, default: true },
    generated: { type: Boolean, default: true },
    has_products: { type: Boolean, default: false },
    has_manifest: { type: Boolean, default: false },
    start_cost: { type: Number, default: 0 },
    vat: { type: Number, default: 0.0 },
    shared: { type: [String], default: [] },
    display_name: { type: [Schema.Types.Mixed], default: [] },
    boops: { type: [BoopSchema], default: [] },
    additional: { type: Schema.Types.Mixed, default: {} }
}, { collection: 'system_manifests' });

// Pre-save hook to generate slug and system_key
SystemManifestSchema.pre('save', function (next) {
    if (this.name) {
        this.slug = slugify(this.name, { lower: true });
    }
    next();
});

module.exports = SystemManifest = mongoose.model("system_manifests", SystemManifestSchema);
