const mongoose = require("mongoose");
const slugify = require("slugify");
const Schema = mongoose.Schema;
const ObjectId = Schema.Types.ObjectId

const SupplierProductSchema = Schema({
    tenant_name: { type:String, required: true},
    sort: {type: Number, required: false, default: 0},
    host_id: { type:String, required: true},
    tenant_id: {type:String, required: true, index: true},
    category_name: {type:String, required: true},
    category_display_name: { type:Array, required: true},
    category_slug: {type:String, required: true},
    linked: {type:ObjectId, required: false, index: true},
    supplier_category: {type:ObjectId, required: true, index: true},
    shareable: {type:Boolean, default: true},
    published: {type:Boolean, default: true},
    object: {type:Object, required: true},
    runs: {type:Array, required: true, default: []},
    additional: {type: Array, required: false, default: []},
    created_at: {type:Date, default: Date.now, index: true},
}, {
    versionKey: false // You should be aware of the outcome after set to false
});


// Pre-save hook to dynamically set the sort value and slug
SupplierProductSchema.pre('save', async function (next) {
    try {
        // Automatically set the slug if the name field is modified
        if (this.isModified('category_name')) {
            this.category_slug = slugify(this.category_name, { lower: true });
        }

        // Check if the document is new to dynamically set the sort value
        if (this.isNew) {
            const maxSortCategory = await this.constructor.findOne().sort({ sort: -1 });
            this.sort = maxSortCategory ? maxSortCategory.sort + 1 : 0;
        }

        next();
    } catch (err) {
        next(err);
    }
});

module.exports = SupplierProduct = mongoose.model("supplier_products", SupplierProductSchema);
