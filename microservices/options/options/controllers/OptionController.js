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
            const buildPageUrl = (pageNum) => pageNum ? `${basePath}?page=${pageNum}&per_page=${limit}` : null;

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

            const validation = ValidationHelper.validateRequired(request.body, ['name']);
            if (!validation.isValid) {
                return response.status(422).json({
                    data: null,
                    message: `Missing required fields: ${validation.missingFields.join(', ')}`,
                    status: 422
                });
            }

            const sanitizedName = ValidationHelper.sanitizeString(request.body.name);
            const slug = slugify(sanitizedName, { lower: true });

            // Check for duplicate option
            const existingOption = await SupplierOption.findOne({
                tenant_id: supplier_id,
                slug : slug
            });

            if (existingOption) {
                return response.json({
                    data: existingOption,
                    message: 'Option has been retrieved successfully',
                    status: 201
                });
            }

            // Validate linked option if provided
            let linkedOption = null;
            if (request.body.linked) {
                linkedOption = await Option.findById(request.body.linked);
                if (!linkedOption) {
                    return response.status(422).json({
                        data: null,
                        message: 'Linked Option does not exist',
                        status: 422
                    });
                }
            }
            const display_name = linkedOption?.display_name ?
                mergeDisplayNames(linkedOption?.display_name, request.body.display_name) :
                generate_display_name(request.body.lang, sanitizedName);

            const optionData = new StoreOptionRequest().prepare(
                request.body,
                supplier_id,
                display_name,
                linkedOption?._id
            );

            const newSupplierOption = await SupplierOption.create(optionData);

            // Call external similarity service (non-blocking)
            OptionController.callSimilarityService(supplier_id, request.body);

            return response.status(201).json({
                data: newSupplierOption,
                message: 'Option has been created successfully',
                status: 201
            });

        } catch (error) {
            console.error('Error in supplier option creation:', error);
            return response.status(500).json({
                data: null,
                message: error.message,
                status: 500
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

}
