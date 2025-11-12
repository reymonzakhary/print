const mongoose = require("mongoose");
const slugify = require("slugify");
const Schema = mongoose.Schema;
const ObjectId = Schema.Types.ObjectId;

const SupplierOptionSchema = Schema({
    sort: { type: Number, required: false, default: 0 },
    tenant_name: { type: String, required: true },
    tenant_id: { type: String, required: true },
    name: { type: String, required: true, unique_with: 'tenant_id' },
    display_name: { type: Array, required: true },
    source_slug: { type: Array, required: false, default: [] },
    slug: { type: String, required: false, unique_with: 'tenant_id' },
    system_key: { type: String, default: '' },
    description: { type: String, required: false, default: '' },
    information: { type: String, required: false, default: ''},
    media: { type: Array, default: [] },
    incremental_by: { type: Number, required: false },

    published: { type: Boolean, default: true },
    has_children: { type: Boolean, default: false },
    input_type: { type: String, required: false, default: 'radio' },
    extended_fields: { type: Array, default: [] },

    linked: { type: ObjectId, ref: "options", required: false, index: true, default: null },
    shareable: { type: Boolean, required: false, default: false },

    sku: { type: String, default: '', unique: false },
    dimension: { type: String, default: '2d' },
    dynamic: { type: Boolean, default: false },
    unit: { type: String, required: false, default: 'mm' },
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
    rpm: { type: Number, default: 0 },

    sheet_runs: [
        {
            _id: false,
            machine: ObjectId,
            dlv_production: { type: Array, default: [] },
            runs: { type: Array, default: [] }
        }
    ],
    runs: [
        {
            _id: false,
            category_id: ObjectId,
            start_cost: { type: Number, default: 0 },
            runs: { type: Array, default: [] }
        }
    ],
    boxes: { type: Array, default: '' },
    additional: {
        _id: false,
        type: {
            calc_ref: { type: String },
            calc_ref_type: { type: String }
        },
        required: false,
        default: {}
    },
    created_at: { type: Date, default: Date.now },
    calculation_method: { type: String, default: "qty" },
    configure: [
        {
            _id: false,
            category_id: ObjectId,
            configure: {
                incremental_by: { type: Number, default: 0 },
                dimension: { type: String, default: "2d" },
                dynamic: { type: Boolean, default: false },
                dynamic_keys: { type: Array, default: [] },
                start_on: { type: Number, default: 0 },
                end_on: { type: Number, default: 0 },
                generate: { type: Boolean, default: false },
                dynamic_type: { type: String, default: "integer" },
                unit: { type: String, default: "mm" },
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
                calculation_method: { type: String, default: "qty" },
                additional: {
                    _id: false,
                    type: {
                        calc_ref: { type: String },
                        calc_ref_type: { type: String }
                    },
                    required: false,
                    default: {}
                }
            }
        }
    ],
    dynamic_keys: { type: Array, required: false, default: [] },
    dynamic_type: { type: String, required: false, default: '' },
    dynamic_object: { type: Array, required: false, default: null },
    end_on: { type: Number, required: false, default: 0 },
    start_on: { type: Number, required: false, default: 0 },
    generate: { type: Boolean, required: false, default: false },
}, {
    versionKey: false
});

SupplierOptionSchema.pre('save', function (next) {
    if (this.name) {
        this.slug = slugify(this.name, { lower: true });
        this.system_key = slugify(this.name, { lower: true });
    }
    next();
});

module.exports = SupplierOption = mongoose.model("supplier_options", SupplierOptionSchema);