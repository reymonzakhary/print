const SupplierOption = require("../Models/SupplierOption");
const SupplierBoops = require("../Models/SupplierBoops");
const UpdateOptionRequest = require("../Requests/UpdateOptionRequest");
const UpdateOptionCategoryConfigRequest = require("../Requests/UpdateOptionCategoryConfigRequest");
const BoopsUpdater = require("../Helpers/BoopsUpdater");
const axios = require('axios');
const mongoose = require("mongoose");
const ObjectId = mongoose.Types.ObjectId;


module.exports = class OptionCategoryController {

    /**
     * Retrieves a specific Supplier Option based on the provided supplier_id and option slug.
     *
     * @param {Object} request - The request object containing supplier_id and option.
     * @param {Object} response - The response object to return the result.
     * @returns {Object} - An object containing the retrieved Supplier Option or an error message.
     */
    static async show(request, response) {
        try {
            const { supplier_id, category_id, option_id } = request.params;

            // Find the option
            const option = await SupplierOption.findOne({
                tenant_id: supplier_id,
                _id: new ObjectId(option_id)
            });

            if (!option) {
                return response.json({
                    data: null,
                    message: 'Option not found',
                    status: 404
                });
            }

            // If no category_id provided, return the full option
            if (!category_id) {
                return response.json({
                    data: option,
                    message: 'Option has been retrieved successfully.',
                    status: 200
                });
            }

            // Use UpdateOptionRequest to flatten the option for this category
            const updateRequest = new UpdateOptionRequest();
            const flattenedOption = updateRequest.flattenForCategory(
                option.toObject(),
                category_id
            );

            return response.json({
                data: flattenedOption,
                message: 'Option configuration for this category has been retrieved successfully.',
                status: 200
            });

        } catch (error) {
            console.error('Error in supplier option show:', error);
            return response.json({
                data: null,
                message: error.message,
                status: 422
            });
        }
    }

    /**
     * Helper method to call similarity service
     */
    static async callSimilarityService(supplier_id, body) {
        try {
            const payload = {
                tenant: supplier_id,
                tenant_name: body.tenant_name,
                options: [{
                    name: body.system_key || body.name,
                    sku: ""
                }]
            };

            await axios.post('http://assortments:5000/similarity/options', payload, {
                headers: { 'Content-Type': 'application/json' },
                timeout: 5000
            });
        } catch (error) {
            console.error('Error calling similarity service:', error.message);
        }
    }


    /**
     * Update a specific Supplier Option based on the provided supplier_id , category and option id.
     *
     * @param {Object} request - The request object containing supplier_id and option.
     * @param {Object} response - The response object to return the result.
     * @returns {Object} - An object containing the retrieved Supplier Option or an error message.
     */
    static async update(request, response) {
        try {
            const { supplier_id, category_id, option_id } = request.params;

            // Find the existing option
            const supplierOption = await SupplierOption.findOne({
                tenant_id: supplier_id,
                _id: new ObjectId(option_id)
            });

            if (!supplierOption) {
                return response.json({
                    data: null,
                    message: "Option doesn't exist",
                    status: 404
                });
            }

            const display_name = request.body.display_name || supplierOption.display_name;

            // Prepare update data using UpdateOptionRequest
            const updateRequest = new UpdateOptionRequest();
            const optionUpdateData = updateRequest.prepare(
                supplierOption,
                request.body,
                display_name,
                category_id
            );

            // Update the supplier option (contains ALL categories data)
            let updatedOption = await SupplierOption.findOneAndUpdate(
                {
                    tenant_id: supplier_id,
                    _id: new ObjectId(option_id)
                },
                { $set: optionUpdateData },
                { new: true }
            );

            if (!updatedOption) {
                return response.json({
                    data: null,
                    message: "Failed to update option",
                    status: 422
                });
            }

            // Get configure and runs data for THIS SPECIFIC category
            const configureData = updateRequest.getConfigureForCategory(
                updatedOption.configure,
                category_id
            );

            const runsData = updateRequest.getRunsForCategory(
                updatedOption.runs,
                category_id
            );

            // Clean the additional field from configure
            const cleanedAdditional = updateRequest.cleanAdditional(configureData.additional || {});

            // Ensure it's always an object, never undefined
            const additionalToSet = cleanedAdditional && typeof cleanedAdditional === 'object'
                ? cleanedAdditional
                : {};

            // Prepare linked as ObjectId (convert string to ObjectId if needed)
            const linkedObjectId = updatedOption.linked
                ? (typeof updatedOption.linked === 'string'
                    ? new ObjectId(updatedOption.linked)
                    : updatedOption.linked)
                : null;

            // Build the COMPLETE update object with ALL fields
            const boopsUpdateFields = {
                // Basic fields from option
                'boops.$[boop].ops.$[opt].name': updatedOption.name,
                'boops.$[boop].ops.$[opt].display_name': updatedOption.display_name,
                'boops.$[boop].ops.$[opt].system_key': updatedOption.system_key,
                'boops.$[boop].ops.$[opt].slug': updatedOption.slug,
                'boops.$[boop].ops.$[opt].description': updatedOption.description,
                'boops.$[boop].ops.$[opt].information': updatedOption.information,
                'boops.$[boop].ops.$[opt].media': updatedOption.media,
                'boops.$[boop].ops.$[opt].linked': linkedObjectId,

                // Configure fields (category-specific)
                'boops.$[boop].ops.$[opt].dimension': configureData.dimension || '2d',
                'boops.$[boop].ops.$[opt].dynamic': configureData.dynamic || false,
                'boops.$[boop].ops.$[opt].dynamic_keys': configureData.dynamic_keys || [],
                'boops.$[boop].ops.$[opt].start_on': configureData.start_on || 0,
                'boops.$[boop].ops.$[opt].end_on': configureData.end_on || 0,
                'boops.$[boop].ops.$[opt].generate': configureData.generate || false,
                'boops.$[boop].ops.$[opt].dynamic_type': configureData.dynamic_type || 'integer',
                'boops.$[boop].ops.$[opt].dynamic_object': configureData.dynamic_object || null,
                'boops.$[boop].ops.$[opt].unit': configureData.unit || 'mm',
                'boops.$[boop].ops.$[opt].width': configureData.width || 0,
                'boops.$[boop].ops.$[opt].maximum_width': configureData.maximum_width || 0,
                'boops.$[boop].ops.$[opt].minimum_width': configureData.minimum_width || 0,
                'boops.$[boop].ops.$[opt].height': configureData.height || 0,
                'boops.$[boop].ops.$[opt].maximum_height': configureData.maximum_height || 0,
                'boops.$[boop].ops.$[opt].minimum_height': configureData.minimum_height || 0,
                'boops.$[boop].ops.$[opt].length': configureData.length || 0,
                'boops.$[boop].ops.$[opt].maximum_length': configureData.maximum_length || 0,
                'boops.$[boop].ops.$[opt].minimum_length': configureData.minimum_length || 0,

                // Runs fields (category-specific)
                'boops.$[boop].ops.$[opt].start_cost': runsData.start_cost || 0,
                'boops.$[boop].ops.$[opt].rpm': updatedOption.rpm || 0,

                // ✅ CRITICAL FIELDS - calculation_method and incremental_by
                'boops.$[boop].ops.$[opt].calculation_method': configureData.calculation_method || 'qty',
                'boops.$[boop].ops.$[opt].incremental_by': configureData.incremental_by || 0,

                // ✅ ADDITIONAL FIELD - with calc_ref and calc_ref_type
                'boops.$[boop].ops.$[opt].additional': additionalToSet
            };

            // Update all SupplierBoops that belong to this category and contain this option
            const boopsUpdateResult = await SupplierBoops.updateMany(
                {
                    tenant_id: supplier_id,
                    supplier_category: new ObjectId(category_id),
                    'boops.ops.id': new ObjectId(option_id)
                },
                {
                    $set: boopsUpdateFields
                },
                {
                    arrayFilters: [
                        { 'boop.ops.id': new ObjectId(option_id) },
                        { 'opt.id': new ObjectId(option_id) }
                    ]
                }
            );

            console.log('SupplierBoops update result:', {
                matched: boopsUpdateResult.matchedCount,
                modified: boopsUpdateResult.modifiedCount
            });

            // Flatten the option to show only current category's data in response
            const flattenedOption = updateRequest.flattenForCategory(
                updatedOption.toObject(),
                category_id
            );

            return response.json({
                data: flattenedOption,
                message: "Option has been updated successfully",
                status: 200
            });

        } catch (error) {
            console.error('Error in supplier option update:', error);
            return response.json({
                data: null,
                message: error.message,
                status: 422
            });
        }
    }


    /**
     * remove  a specific category from Supplier Option Configure based on the provided supplier_id , category and option id.
     *
     * @param {Object} request - The request object containing supplier_id and option.
     * @param {Object} response - The response object to return the result.
     * @returns {Object} - An object containing the retrieved Supplier Option or an error message.
     */
    static async destroy(request, response) {
        try {
            const { supplier_id, category_id, option_id } = request.params;
            const option = await SupplierOption.findOne({
                tenant_id: supplier_id,
                _id: new ObjectId(option_id)
            });

            if (!option) {
                return response.json({
                    data: null,
                    message: 'Option not found',
                    status: 404
                });
            }

            // Check if category exists in configure array
            if (!option.configure || !Array.isArray(option.configure)) {
                return response.json({
                    data: null,
                    message: 'No configurations found for this option',
                    status: 404
                });
            }

            const categoryExists = option.configure.some(
                item => item.category_id.toString() === category_id
            );
            if (!categoryExists) {
                return response.json({
                    data: null,
                    message: 'Configuration for this category not found',
                    status: 404
                });
            }
            const updatedOption = await SupplierOption.findOneAndUpdate(
                {
                    tenant_id: supplier_id,
                    _id: new ObjectId(option_id)
                },
                {
                    $pull: {
                        configure: {
                            category_id: new ObjectId(category_id)
                        },
                        runs: {
                            category_id: new ObjectId(category_id)
                        }
                    }
                },
                { new: true }
            );

            if (!updatedOption) {
                return response.json({
                    data: null,
                    message: 'Failed to remove configuration',
                    status: 500
                });
            }

            OptionCategoryController.removeOptionFromBoops(supplier_id, category_id, option_id);
            return response.json({
                data: updatedOption,
                message: 'Configuration has been removed successfully',
                status: 200
            });

        } catch (error) {
            console.error('Error in supplier option destroy:', error);
            return response.json({
                data: null,
                message: error.message,
                status: 500
            });
        }
    }

    /**
     * Helper method to remove option from SupplierBoops (non-blocking)
     */
    static async removeOptionFromBoops(supplier_id, category_id, option_id) {
        try {
            BoopsUpdater.removeOptionFromBoops(
                supplier_id,
                category_id,
                option_id
            );
        } catch (error) {
            console.error('Error removing option from SupplierBoops:', error);
            // Log error but don't fail the main operation
        }
    }

}
