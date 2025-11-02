const SupplierBoops = require("../Models/SupplierBoops");
const mongoose = require("mongoose");
const ObjectId = mongoose.Types.ObjectId;

module.exports = class BoopsUpdater {
    /**
     * Update SupplierBoops with option configuration changes
     */
    static async updateOptionConfiguration(supplier_id, category_id, option_id, configureData) {
        try {
            // First, try to update if the option exists
            const updateFields = {};
            Object.keys(configureData).forEach(key => {
                updateFields[`boops.$[].ops.$[op].${key}`] = configureData[key];
            });

            const updated = await SupplierBoops.findOneAndUpdate(
                {
                    tenant_id: supplier_id,
                    supplier_category: new ObjectId(category_id),
                    'boops.ops.id': new ObjectId(option_id)
                },
                { $set: updateFields },
                {
                    arrayFilters: [{ 'op.id': new ObjectId(option_id) }],
                    new: true
                }
            );

            if (!updated) {
                await SupplierBoops.updateOne(
                    {
                        tenant_id: supplier_id,
                        supplier_category: new ObjectId(category_id)
                    },
                    {
                        $push: {
                            'boops.$[].ops': {
                                id: new ObjectId(option_id),
                                ...configureData
                            }
                        }
                    }
                );
            }
        } catch (error) {
            console.error('Error updating SupplierBoops configuration:', error);
            throw error;
        }
    }

    /**
     * Remove option from SupplierBoops
     */
    static async removeOptionFromBoops(supplier_id, category_id, option_id) {
        try {
            await SupplierBoops.updateMany(
                {
                    tenant_id: supplier_id,
                    supplier_category: new ObjectId(category_id)
                },
                {
                    $pull: {
                        'boops.$[].ops': {
                            id: new ObjectId(option_id)
                        }
                    }
                }
            );
        } catch (error) {
            console.error('Error removing option from SupplierBoops:', error);
            throw error;
        }
    }
}