const mongoose = require('mongoose');
const { Schema } = mongoose;

const SupplierProductPriceSchema = new Schema({
    supplier_product: { type: Schema.Types.ObjectId, ref: 'SupplierProduct', required: true },
    supplier_id: { type: String, required: true },
    supplier_name: { type: String, required: true },
    host_id: { type: String, required: true },
    tables: { type: Map, of: Schema.Types.Mixed },
    created_at: { type: Date, default: Date.now, required: true },
    additional: { type: Map, of: Schema.Types.Mixed, default: {} }
}, {
    timestamps: { createdAt: 'created_at' },
    collection: 'supplier_product_prices',
    versionKey: false // You should be aware of the outcome after set to false
});

// Indexes
SupplierProductPriceSchema.index({ created_at: -1 });

// Pre-save middleware for custom logic
SupplierProductPriceSchema.pre('save', function(next) {
    // Add any custom logic here if needed
    next();
});

const SupplierProductPrice = mongoose.model('SupplierProductPrice', SupplierProductPriceSchema);

module.exports = SupplierProductPrice;
