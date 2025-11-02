const SupplierBox = require("../Models/SupplierBox");
const Box = require("../Models/Box");

const SupplierBoops = require("../Models/SupplierBoops");
const slugify = require('slugify');
const mongoose = require("mongoose");
const ObjectId = mongoose.Types.ObjectId;
const {
    mergeDisplayNames,
    generate_display_name
} = require('../Helpers/Helper');
const {
    handleLinkedBox,
    handleStandaloneBox,
} = require('../Helpers/Helpers');

module.exports = class BoxController {

    /**
     * Retrieves the Boxes and returns them as a JSON response.
     *
     * @param {Object} request - The request object.
     * @param {Object} response - The response object.
     * @returns {Promise<Object>} - A promise that resolves to the JSON response.
     */
    static async index(request, response) {
        try {
            // Parse pagination parameters
            const page = parseInt(request.query.page) || 1;
            const limit = parseInt(request.query.per_page) || 10;
            const skip = (page - 1) * limit;

            // Get supplier_id from params
            const supplierId = request.params.supplier_id;

            // Build query filter
            const filter = { tenant_id: supplierId };

            // Get total count for pagination
            const totalCount = await SupplierBox.countDocuments(filter);

            // Fetch paginated data
            /**
             * @type {Array<Object>}
             */
            const boxes = await SupplierBox.aggregate([
                { $match: filter },
                { $skip: skip },
                { $limit: limit }
            ]).exec();

            // Calculate pagination metadata
            const totalPages = Math.ceil(totalCount / limit);
            const hasNextPage = page < totalPages;
            const hasPrevPage = page > 1;

            // Calculate range
            const from = totalCount > 0 ? skip + 1 : 0;
            const to = Math.min(skip + boxes.length, totalCount); // Fixed: was categories.length

            // Build base URL (fixed path to match resource)
            const host = request.query.host || request.get('host');
            const basePath = `${host}/api/v1/mgr/supplier/${supplierId}/boxes`;

            // Build pagination URLs
            const buildPageUrl = (pageNum) => {
                return pageNum ? `${basePath}?page=${pageNum}&per_page=${limit}` : null;
            };

            const paginationUrls = {
                first_page_url: buildPageUrl(1),
                last_page_url: buildPageUrl(totalPages),
                next_page_url: hasNextPage ? buildPageUrl(page + 1) : null,
                prev_page_url: hasPrevPage ? buildPageUrl(page - 1) : null,
                path: basePath
            };

            // Return successful response
            return response.json({
                data: boxes,
                message: 'Supplier boxes have been retrieved successfully.',
                status: 200,
                pagination: {
                    total: totalCount,
                    per_page: limit,
                    current_page: page,
                    last_page: totalPages,
                    from: from,
                    to: to,
                    ...paginationUrls
                }
            });

        } catch (error) {
            // Handle errors
            console.error('Error fetching supplier boxes:', error);
            return response.status(422).json({
                data: [],
                message: error.message,
                status: 422
            });
        }
    }


    /**
     * Retrieves and returns a specific supplier box based on the provided supplier ID and box slug.
     * Performs an aggregation query with optional sorting and pagination to find the desired box.
     * Returns an error response if the box is not found.
     *
     * @param {Object} request - The request object containing parameters such as supplier_id and box slug.
     * @param {Object} response - The response object used to send the results or error messages.
     * @return {Object} The response object containing the retrieved box data and accompanying status and message.
     */
    static async show(request, response) {
            // Aggregation pipeline with pagination
            let box = await SupplierBox.aggregate([
                {
                    $match: {
                        'tenant_id': request.params.supplier_id,
                        'slug': request.params.box
                    },
                },
                {
                $sort: {_id: 1} // Optional, sorts by _id or any other field
                },
                {
                $limit: 1 // Retrieve only the first document after sorting
                }
            ]);

            // box = box[0] || null;

        // Check if category exists
            if (!box[0]) {
                return response.json({
                    message: 'Box not found.',
                    status: 404
                });
            }

        // Return the paginated response
            return response.json({
                data: box,
            message: 'Categories have been retrieved successfully.',
                status: 200
            });
    }


    /**
     * Handles the creation of a new supplier box. Checks for the existence of a box
     * with the same name and slug under a specific tenant. If a linked box is provided
     * and exists, it processes the linked box creation; otherwise, it creates a standalone box.
     *
     * @param {Object} req - The request object containing the supplier ID in `req.params.supplier_id`
     * and box details in `req.body`.
     * @param {Object} res - The response object used to return the status and result of the operation.
     * @return {Promise<Object>} JSON response containing the created box object, a success message,
     * and the HTTP status code. If an error occurs, returns an error message and a 422 status code.
     */
    static async store(req, res) {
        try {
            const supplierId = req.params.supplier_id;
            const body = req.body;

            const existingBox = await SupplierBox.findOne({
                tenant_id: supplierId,
                slug: slugify(body.name, { lower: true, strict: true })
            });

            if (existingBox) {
                return res.status(201).json({
                    data: existingBox,
                    message: 'Box has been created successfully.',
                    status: 201
                });
            }

            let newSupplierBox;

            if (body.linked) {
                const systemBox = await Box.findById(new ObjectId(body.linked));

                if (!systemBox) {
                    body.linked = null;
                }else{
                    const display_names = systemBox?.display_name ?
                        mergeDisplayNames(systemBox.display_name, body.display_name) :
                        generate_display_name(body.lang, body.name);


                    newSupplierBox = await handleLinkedBox(
                        supplierId,
                        body,
                        display_names
                    );
                }
            } else {
                newSupplierBox = await handleStandaloneBox(
                    supplierId, 
                    body, 
                    generate_display_name(body.lang, body.name)
                );
            }


            return res.status(201).json({
                data: newSupplierBox,
                message: 'Supplier box has been created successfully.',
                status: 201
            });
        } catch (error) {
            console.error('Error in supplier box post:', error);
            return res.status(200).json({ 
                message: error.message,
                status: 422
            });
        }
    }

    /**
     * Updates a supplier box with the provided data. If the box does not exist, a 404 response is returned.
     * This method also handles updating related "boops" and prepares the update data dynamically by considering the existing values.
     *
     * @param {Object} req - The HTTP request object containing the supplier ID, box slug, and body with box update data.
     * @param {Object} req.params - URL parameters containing `supplier_id` and `box`.
     * @param {string} req.params.supplier_id - The unique identifier of the supplier.
     * @param {string} req.params.box - The slug of the box to be updated.
     * @param {Object} req.body - The body of the request containing fields to update.
     * @param {Object} res - The HTTP response object for sending the response back to the client.
     * @return {Promise<void>} - A promise that resolves to send a JSON response containing the updated box or error details.
     */
    static async update(req, res) {
        try {
            const supplierId = req.params.supplier_id;
            const boxSlug = req.params.box;
            const body = req.body;

            // Find the existing box
            const box = await SupplierBox.findOne({
                tenant_id: supplierId,
                slug: boxSlug
            });

            if (!box) {
                return res.status(200).json({
                    message: 'Box not found.',
                    status: 404
                });
            }

            // Prepare update data exactly like Python version
            const dataToStore = {
                display_name: body.display_name !== undefined ? body.display_name : box.display_name,
                system_key: body.system_key !== undefined ? body.system_key : box.system_key,
                sort: body.sort !== undefined ? parseInt(body.sort) : 0,
                input_type: body.input_type !== undefined ? body.input_type : "",
                calc_ref: body.calc_ref !== undefined ? body.calc_ref : "",
                linked: body.linked !== undefined ? new ObjectId(body.linked) : box.linked,
                incremental: body.incremental !== undefined ? Boolean(body.incremental) : false,
                select_limit: body.select_limit !== undefined ? parseInt(body.select_limit) : 0,
                option_limit: body.option_limit !== undefined ? parseInt(body.option_limit) : 0,
                sqm: body.sqm !== undefined ? Boolean(body.sqm) : false,
                media: body.media !== undefined ? body.media : [],
                description: body.description !== undefined ? body.description : "",
                shareable: body.shareable !== undefined ? Boolean(body.shareable) : false,
                start_cost: body.start_cost !== undefined ? body.start_cost : 0,
                published: body.published !== undefined ? Boolean(body.published) : false,
                appendage: body.appendage !== undefined ? Boolean(body.appendage) : false,
                additional: body.additional !== undefined ? body.additional : []
            };

            // Update boops if needed
            await BoxController._updateBoops(supplierId, body, dataToStore);

            // Update the box
            await SupplierBox.updateOne(
                { tenant_id: supplierId, slug: boxSlug },
                { $set: dataToStore }
            );

            // Fetch and return updated box
            const updatedCategory = await SupplierBox.findOne({
                tenant_id: supplierId,
                slug: boxSlug
            });

            return res.json(updatedCategory);

        } catch (error) {
            console.error('Error updating supplier box:', error);
            return res.status(200).json({
                message: error.message,
                status: 500
            });
        }
    }

    /**
     * Updates the boops data for a given tenant, category, and box.
     *
     * @param {string} tenant - The tenant identifier.
     * @param {Object} body - The request body containing category and box identifiers.
     * @param {Object} dataToStore - The data to store for the boops, including properties like display name, system key, media, description, etc.
     *
     * @return {Promise<void>} Resolves when the update operation completes.
     */
    static async _updateBoops(tenant, body, dataToStore) {
        const categoryId = body.category_id;
        const boxId = body.id;

        if (!categoryId || !boxId) {
            return;
        }

        const query = {
            tenant_id: tenant,
            "boops.id": new ObjectId(boxId)
        };

        if (categoryId) {
            try {
                query.supplier_category = new ObjectId(categoryId);
            } catch (error) {
                // Skip if invalid ObjectId
            }
        }

        await SupplierBoops.updateOne(
            query,
            {
                $set: {
                    "boops.$.display_name": dataToStore.display_name,
                    "boops.$.system_key": dataToStore.system_key,
                    "boops.$.media": dataToStore.media,
                    "boops.$.description": dataToStore.description,
                    "boops.$.published": dataToStore.published,
                    "boops.$.appendage": dataToStore.appendage,
                    "boops.$.input_type": dataToStore.input_type,
                    "boops.$.calc_ref": dataToStore.calc_ref,
                    "boops.$.linked": dataToStore.linked
                }
            }
        );
    }
}