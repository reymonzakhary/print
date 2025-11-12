const SupplierOption = require("../Models/SupplierOption");
const Option = require("../Models/Option");
const StoreOptionRequest = require("../Requests/StoreOptionRequest");
const ValidationHelper = require("../Helpers/ValidationHelper");
const slugify = require('slugify');
const axios = require('axios');
const mongoose = require("mongoose");
const ObjectId = mongoose.Types.ObjectId;
const {
    generate_display_name, update_display_name, mergeDisplayNames
} = require('../Helpers/Helper');
const SupplierBoops = require("../Models/SupplierBoops");

module.exports = class OptionController {

    /**
     * Retrieves the Boxes and returns them as a JSON response.
     *
     * @param {Object} request - The request object.
     * @param {Object} response - The response object.
     * @returns {Promise<Object>} - A promise that resolves to the JSON response.
     */
    static async index(request, response) {
        try {
            const supplierId = request.params.supplier_id;

            // Validate and sanitize pagination
            const { page, limit, skip } = ValidationHelper.validatePagination(
                request.query.page, 
                request.query.per_page
            );

            const filter = { tenant_id: supplierId };
            if (request.query.ref) {
                filter['additional.calc_ref'] = request.query.ref;
            }

            if (request.query.filter ?? false) {
                filter.$or = [
                    { name: { $regex: request.query.filter, $options: 'i' } },
                    { 'display_name.display_name': { $regex: request.query.filter, $options: 'i' } }
                ];
            }

            // Use Promise.all for parallel execution
            const [totalCount, options] = await Promise.all([
                SupplierOption.countDocuments(filter),
                SupplierOption.aggregate([
                    { $match: filter },
                    { $skip: skip },
                    { $limit: limit },
                ]).exec()
            ]);

            // Calculate pagination metadata
            const totalPages = Math.ceil(totalCount / limit);
            const from = totalCount > 0 ? skip + 1 : 0;
            const to = Math.min(skip + options.length, totalCount);

            // Build pagination URLs
            const host = request.query.host || request.get('host');
            const basePath = `${host}/api/v1/mgr/supplier/${supplierId}/options`;
            
            const buildPageUrl = (pageNum) => {
                if (!pageNum) return null;
                let url = `${basePath}?page=${pageNum}&per_page=${limit}`;
                
                // Include filter in pagination URLs if present
                if (request.query.filter) {
                    url += `&filter=${encodeURIComponent(request.query.filter)}`;
                }
                
                // Include ref in pagination URLs if present
                if (request.query.ref) {
                    url += `&ref=${encodeURIComponent(request.query.ref)}`;
                }
                
                return url;
            };

            const pagination = {
                total: totalCount,
                per_page: limit,
                current_page: page,
                last_page: totalPages,
                from,
                to,
                first_page_url: buildPageUrl(1),
                last_page_url: buildPageUrl(totalPages),
                next_page_url: page < totalPages ? buildPageUrl(page + 1) : null,
                prev_page_url: page > 1 ? buildPageUrl(page - 1) : null,
                path: basePath
            };

            return response.status(200).json({
                data: options,
                message: 'Supplier Options have been retrieved successfully.',
                status: 200,
                pagination
            });

        } catch (error) {
            console.error('Error fetching supplier options:', error);
            return response.status(500).json({
                data: null,
                message: error.message,
                status: 422
            });
        }
    }

    /**
     * Store a specific Supplier Option based on the provided supplier_id and option slug.
     *
     * @param {Object} request - The request object containing supplier_id and option.
     * @param {Object} response - The response object to return the result.
     * @returns {Object} - An object containing the retrieved Supplier Option or an error message.
     */
    static async store(request, response) {
        try {
            const { supplier_id } = request.params;
            let option = await SupplierOption.findOne({
                tenant_id: supplier_id,
                slug: slugify(request.body.name, {lower: true}),
            });

            if (option) {
                return response.json({
                    data: option,
                    message: 'Option has been retrieved successfully',
                    status: 201
                });
            }

            const system_option = await Option.findById(request.body.linked);

            if(system_option) {
                option = await SupplierOption.findOne({
                    tenant_id: supplier_id,
                    linked: system_option._id
                })

                if(option) {
                    return response.json({
                        data: option,
                        message: 'Option has been retrieved successfully',
                        status: 201
                    });
                }
            }

            const {display_name} = request.body.display_name[0];

            option = await SupplierOption.create(new StoreOptionRequest().prepare(
                request.body,
                supplier_id,
                system_option ?
                    mergeDisplayNames(system_option.display_name, request.body.display_name):
                    generate_display_name(request.body.lang, display_name),
                system_option?._id,
            ));

            if (!system_option) {
                // Call external similarity service (non-blocking)
                OptionController.callSimilarityService(supplier_id, request.body);
            }


            return response.json({
                data: option,
                message: 'Option has been created successfully',
                status: 201
            })

        } catch (error) {
            console.error('Error in supplier option creation:', error);
            return response.status(200).json({
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
                    name: body.name,
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
     * Update option general information only (display_name, description, additional, images)
     *
     * @param {Object} request - The request object containing supplier_id and option data
     * @param {Object} response - The response object to return the result
     * @returns {Object} - An object containing the updated Supplier Option or an error message
     */
    static async update(request, response) {
        try {

            console.log(request.body.published)

            const { supplier_id, option_id } = request.params;
            const option = await SupplierOption.findOne({
                tenant_id: supplier_id,
                _id: new ObjectId(option_id)
            });

            if (!option) {
                return response.status(404).json({
                    data: null,
                    message: 'Option not found',
                    status: 404
                });
            }

            const updateData = {};

            if (request.body.display_name) {
                updateData.display_name = request.body.display_name || option.display_name;
            }

            if (request.body.description !== undefined) {
                updateData.description = request.body.description || option.description;
            }

            if (request.body.additional !== undefined) {
                updateData.additional = request.body.additional || option.additional;
            }

            if (request.body.media) {
                updateData.media = request.body.media ||  option.media;
            }

            updateData.published = 'published' in request.body ? request.body.published : option.published;


            const updatedOption = await SupplierOption.findOneAndUpdate(
                { tenant_id: supplier_id, _id: new ObjectId(option_id) },
                { $set: updateData },
                { new: true }
            );

            // Build the COMPLETE update object with ALL fields
            const boopsUpdateFields = {
                // Basic fields from option
                'boops.$[boop].ops.$[opt].display_name': updatedOption.display_name,
                'boops.$[boop].ops.$[opt].description': updatedOption.description,
                'boops.$[boop].ops.$[opt].media': updatedOption.media,
                'boops.$[boop].ops.$[opt].additional': updatedOption.additional
            };

            // Update all SupplierBoops that belong to this category and contain this option
            await SupplierBoops.updateMany(
                {
                    tenant_id: supplier_id,
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

            return response.json({
                data: updatedOption,
                message: 'Option general information has been updated successfully',
                status: 200
            });

        } catch (error) {
            console.error('Error updating option general info:', error);
            return response.status(500).json({
                data: null,
                message: error.message,
                status: 500
            });
        }
    }

}
