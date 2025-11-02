const mongoose = require('mongoose');
const slugify = require('slugify');

const categorySchema = new mongoose.Schema({
    name: { type: String, required: true },
    sort: { type: Number, required: false, default: 0 },
    countries: { type: [String], required: false, default: [] },
    sku: { type: String, required: false, default: '' },
    system_key: { type: String, required: true, unique: true },
    display_name: { type: [Object], required: true, default: [] },
    slug: { type: String, required: true, unique: true },
    description: { type: String, required: false, default: null },
    shareable: { type: Boolean, required: false, default: false },
    published: { type: Boolean, required: false, default: true },
    media: { type: [Object], required: false, default: [] },
    price_build: { type: Object, required: false, default: {} },
    has_products: { type: Boolean, required: false, default: false },
    has_manifest: { type: Boolean, required: false, default: false },
    calculation_method: { type: [String], required: false, default: [] },
    dlv_days: { type: [String], required: false, default: [] },
    printing_method: { type: [String], required: false, default: [] },
    production_days: { type: [String], required: false, default: [] },
    production_dlv: { type: [String], required: false, default: [] },
    free_entry: { type: [String], required: false, default: [] },
    limits: { type: [Object], required: false, default: [] },
    ranges: { type: [Object], required: false, default: [] },
    range_list: { type: [Object], required: false, default: [] },
    ref_id: { type: String, required: false, default: '' },
    ref_category_name: { type: String, required: false, default: '' },
    start_cost: { type: Number, required: false, default: 0 },
    vat: { type: Number, required: false, default: 0 },
    bleed: { type: Number, required: false, default: 0 },
    range_around: { type: Number, required: false, default: 0 },
    created_at: { type: Date, default: Date.now, required: true },
    checked: { type: Boolean, default: false },
    additional: { type: Object, required: false, default: {} }
}, {
    versionKey: false
});

categorySchema.pre('save', function (next) {
    if (this.name) {
        this.slug = slugify(this.name, { lower: true });
        this.system_key = slugify(this.name, { lower: true });
    }
    next();
});

const Category = mongoose.model('Category', categorySchema);

module.exports = Category;