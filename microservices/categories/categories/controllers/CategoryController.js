const SupplierCategory = require("../Models/SupplierCategory");
const SupplierOption = require("../Models/SupplierOption");
const SupplierBox = require("../Models/SupplierBox");
const SystemManifest = require("../Models/SystemManifest");
const SupplierProduct = require("../Models/SupplierProduct");
const SupplierProductPrice = require("../Models/SupplierProductPrice");

const StoreCategoryRequest = require("../Requests/StoreCategoryRequest");
const UpdateCategoryRequest = require("../Requests/UpdateCategoryRequest");
const Category = require("../Models/Category");
const Box = require("../Models/Box");
const Option = require("../Models/Option");

const SupplierBoops = require("../Models/SupplierBoops");
const slugify = require('slugify');
const axios = require('axios');
const mongoose = require("mongoose");
const ObjectId = mongoose.Types.ObjectId
const {
    getCategorySpecificConfig,getCategorySpecificRuns, mergeDisplayNames, update_display_name,validateLinkedIdsAndClean
} = require('../Helpers/Helper')

module.exports = class CategoryController {

    /**
     * Retrieves the categories and returns them as a JSON response.
     *
     * @param {Object} request - The request object.
     * @param {Object} response - The response object.
     * @returns {Promise<Object>} - A promise that resolves to the JSON response.
     */
    static async index(request, response) {
        const page = parseInt(request.query.page) || 1;
        const limit = parseInt(request.query.per_page) || 10;
        const skip = (page - 1) * limit;

        const totalCount = await SupplierCategory.countDocuments({
            'tenant_id': request.params.supplier_id
        });

        // Aggregation pipeline with pagination
        const categories = await SupplierCategory.aggregate([
            {
                $match: {
                    'tenant_id': request.params.supplier_id
                },
            },
            {
                $lookup: {
                    from: "supplier_boops",
                    localField: "_id",
                    foreignField: "supplier_category",
                    as: "boops"
                }
            },
            {$skip: skip},
            {$limit: limit}
        ]);

        // Collect option IDs for batch fetching
        const optionIds = [];
        categories.forEach(category => {
            if (category.boops.length > 0) {
                category.boops[0].boops.forEach(boop => {
                    boop.ops.forEach(op => {
                        if (op && op.id) {
                            optionIds.push(new ObjectId(op.id));
                        }
                    });
                });
            }
        });

        // Fetch all supplier options in one query
        const options = await SupplierOption.aggregate([
            {
                $match: {
                    '_id': {$in: optionIds},
                    'tenant_id': request.params.supplier_id
                }
            }
        ]);

        // Create a mapping of options for easy access
        const optionsMap = {};
        options.forEach(option => {
            optionsMap[option._id.toString()] = option;
        });

        // Update categories with fetched option data
        categories.forEach(category => {
            if (category.boops.length > 0) {
                category.boops[0].boops.forEach(boop => {
                    boop.ops.forEach(op => {
                        if (op && op.id) {
                            const option = optionsMap[op.id];
                            if (option) {
                                // Update op with values from option
                                Object.assign(op, {
                                    incremental_by: option.incremental_by,
                                    name: option.name,
                                    slug: option.slug,
                                    linked: option.linked,
                                    display_name: option.display_name,
                                    dimension: option.dimension,
                                    dynamic: request.body.dynamic ?? option.dynamic,
                                    dynamic_keys: request.body.dynamic_keys ?? option.dynamic_keys,
                                    start_on: request.body.start_on || option.start_on,
                                    end_on: request.body.end_on || option.end_on,
                                    dynamic_type: request.body.dynamic_type || option.dynamic_type,
                                    generate: request.body.generate || option.generate,
                                    unit: option.unit,
                                    width: option.width,
                                    maximum_width: option.maximum_width,
                                    minimum_width: option.minimum_width,
                                    height: option.height,
                                    maximum_height: option.maximum_height,
                                    minimum_height: option.minimum_height,
                                    length: option.length,
                                    maximum_length: option.maximum_length,
                                    minimum_length: option.minimum_length,
                                    start_cost: option.start_cost,
                                    rpm: option.rpm,
                                    media: option.media
                                });
                            } else {
                                console.warn(`No option found for ID: ${op.id}`);
                            }
                        }
                    });
                    // await SupplierBoops.updateOne(
                    //     { _id: boop.id}, // Match by document ID and ops slug
                    //     {
                    //         $set: {
                    //             'boops.$[].ops.$[op].name': option.name, // Update the name or any other field
                    //             'boops.$[].ops.$[op].display_name': option.display_name, // Update the display_name
                    //             'boops.$[].ops.$[op].slug': option.slug // Update the display_name
                    //         }
                    //     },
                    //     {
                    //         arrayFilters: [{ 'op.slug': option.slug }] // Filter for the specific ops element to update
                    //     }
                    // )

                    // const result = await SupplierBoops.findOne({ _id: new ObjectId(boop.id) });
                    // console.log('Found document:', result, boop);
                });
            }
        });

        // Pagination details
        const totalPages = Math.ceil(totalCount / limit);
        const nextPage = page < totalPages ? page + 1 : null;
        const prevPage = page > 1 ? page - 1 : null;
        const from = totalCount > 0 ? skip + 1 : 0;
        const to = Math.min(skip + categories.length, totalCount);

        // Construct URLs
        const path = `${request.query.host}/api/v1/mgr/categories`;
        const firstPageUrl = `?page=1&per_page=${limit}`;
        const lastPageUrl = `?page=${totalPages}&per_page=${limit}`;
        const nextPageUrl = nextPage ? `?page=${nextPage}&per_page=${limit}` : null;
        const prevPageUrl = prevPage ? `?page=${prevPage}&per_page=${limit}` : null;

        // Return the paginated response
        return response.json({
            data: categories,
            message: 'Categories have been retrieved successfully.',
            status: 200,
            pagination: {
                total: totalCount,
                per_page: limit,
                current_page: page,
                last_page: totalPages,
                first_page_url: firstPageUrl,
                last_page_url: lastPageUrl,
                next_page_url: nextPageUrl,
                prev_page_url: prevPageUrl,
                path: path,
                from: from,
                to: to
            }
        });
    }

    /**
     * Retrieves the count of categories with sharable = true
     *
     * @param {Object} request - The request object.
     * @param {Object} response - The response object.
     * @returns {Promise<Object>} - A promise that resolves to the JSON response.
     */
    static async countSharable(request, response) {
        try {
            const supplierId = request.params.supplier_id;

            const count = await SupplierCategory.countDocuments({
                tenant_id: supplierId,
                shareable: true
            });

            return response.json({
                sharable_count: count,
                message: 'Sharable categories count retrieved successfully.',
                status: 200
            });
        } catch (error) {
            return response.json({
                        'message': e.message,
                        'status': 422
                    })
        }
    }


    static async show(request, response) {
        // Aggregation pipeline with pagination
        let category = await SupplierCategory.aggregate([
            {
                $match: {
                    'tenant_id': request.params.supplier_id,
                    'slug': request.params.category
                },
            },
            {
                $lookup: {
                    from: "supplier_boops",
                    localField: "_id",
                    foreignField: "supplier_category",
                    as: "boops"
                }
            },
            {
                $sort: {_id: 1} // Optional, sorts by _id or any other field
            },
            {
                $limit: 1 // Retrieve only the first document after sorting
            }
        ]);

        category = category[0] || null;

        // Check if category exists
        if (!category) {
            return response.json({
                message: 'Category not found.',
                status: 404
            });
        }

        // Collect option IDs for batch fetching
        const optionIds = [];
        const boxIds = [];

        if (category.boops.length > 0) {
            category.boops[0].boops.forEach(boop => {
                if (boop && boop.id) {
                    boxIds.push(new ObjectId(boop.id));
                }
                boop.ops.forEach(op => {
                    if (op && op.id) {
                        optionIds.push(new ObjectId(op.id));
                    }
                });
            });
        }

        // Fetch all supplier options in one query
        const options = await SupplierOption.aggregate([
            {
                $match: {
                    '_id': {$in: optionIds},
                    'tenant_id': request.params.supplier_id
                }
            }
        ]).exec();

        // Fetch all supplier boxes in one query
        const boxes = await SupplierBox.aggregate([
            {
                $match: {
                    '_id': {$in: boxIds},
                    'tenant_id': request.params.supplier_id
                }
            }
        ]).exec();

        // Create a mapping of options for easy access
        const optionsMap = {};
        options.forEach(option => {
            optionsMap[option._id.toString()] = option;
        });

        const boxesMap = {};
        boxes.forEach(box => {
            boxesMap[box._id.toString()] = box;
        });

        const boops = category.boops[0];

        // Update categories with fetched option data
        if (category.boops.length > 0) {
            boops.boops.forEach(boop => {
                if (boop && boop.id) {
                    const box = boxesMap[boop.id];
                    if (box) {
                        Object.assign(boop, {
                            ...box,
                            id: boop.id,
                            ops: boop.ops,
                            divided: boop.divided,
                            calc_ref: box.calc_ref,
                            appendage: box.appendage,
                            incremental: box.incremental,
                            sku: box.sku,
                        });
                    }

                    boop.ops.forEach(op => {
                        if (op && op.id) {
                            const option = optionsMap[op.id];
                            if (option) {
                                // Get category-specific configuration and runs
                                const categoryConfig = getCategorySpecificConfig(option, category._id);
                                const categoryRuns = getCategorySpecificRuns(option, category._id);

                                // Merge category-specific config with general option properties
                                // Priority: category-specific config > general option properties
                                Object.assign(op, {
                                    // General option properties
                                    name: option.name,
                                    slug: option.slug,
                                    linked: option.linked,
                                    display_name: option.display_name,
                                    media: option.media,
                                    additional: option.additional,
                                    description: option.description,

                                    // Category-specific properties (these override general ones)
                                    incremental_by: categoryConfig.incremental_by !== undefined ? categoryConfig.incremental_by : option.incremental_by,
                                    dimension: categoryConfig.dimension !== undefined ? categoryConfig.dimension : option.dimension,
                                    dynamic: categoryConfig.dynamic !== undefined ? categoryConfig.dynamic : option.dynamic,
                                    dynamic_keys: categoryConfig.dynamic_keys !== undefined ? categoryConfig.dynamic_keys : option.dynamic_keys,
                                    start_on: categoryConfig.start_on !== undefined ? categoryConfig.start_on : option.start_on,
                                    end_on: categoryConfig.end_on !== undefined ? categoryConfig.end_on : option.end_on,
                                    dynamic_type: categoryConfig.dynamic_type !== undefined ? categoryConfig.dynamic_type : option.dynamic_type,
                                    generate: categoryConfig.generate !== undefined ? categoryConfig.generate : option.generate,
                                    unit: categoryConfig.unit !== undefined ? categoryConfig.unit : option.unit,
                                    width: categoryConfig.width !== undefined ? categoryConfig.width : option.width,
                                    maximum_width: categoryConfig.maximum_width !== undefined ? categoryConfig.maximum_width : option.maximum_width,
                                    minimum_width: categoryConfig.minimum_width !== undefined ? categoryConfig.minimum_width : option.minimum_width,
                                    height: categoryConfig.height !== undefined ? categoryConfig.height : option.height,
                                    maximum_height: categoryConfig.maximum_height !== undefined ? categoryConfig.maximum_height : option.maximum_height,
                                    minimum_height: categoryConfig.minimum_height !== undefined ? categoryConfig.minimum_height : option.minimum_height,
                                    length: categoryConfig.length !== undefined ? categoryConfig.length : option.length,
                                    maximum_length: categoryConfig.maximum_length !== undefined ? categoryConfig.maximum_length : option.maximum_length,
                                    minimum_length: categoryConfig.minimum_length !== undefined ? categoryConfig.minimum_length : option.minimum_length,
                                    start_cost: categoryConfig.start_cost !== undefined ? categoryConfig.start_cost : option.start_cost,
                                    calculation_method: categoryConfig.calculation_method !== undefined ? categoryConfig.calculation_method : option.calculation_method,

                                    // Category-specific runs
                                    runs: categoryRuns,

                                    // Keep original rpm (not category-specific)
                                    rpm: option.rpm
                                });
                            } else {
                                console.warn(`No option found for ID: ${op.id}`);
                            }
                        }
                    });
                }
            });
        }

        // console.log(JSON.stringify(category.boops[0].boops));

        // Return the paginated response
        return response.json({
            data: category,
            message: 'Categories have been retrieved successfully.',
            status: 200
        });
    }

    /**
     * Retrieves a supplier category by linked ID from the database.
     *
     * @param {Request} req - The HTTP request object.
     * @param {Response} res - The HTTP response object.
     * @returns {Object} An object containing the retrieved supplier category data and status code.
     */
    static async getByLinked(req, res) {
        let category = await SupplierCategory.find({
            'tenant_id': req.params.supplier_id,
            'linked': new ObjectId(req.params.linked_id),
        })

        return res.json({
            'data': category,
            'status': 200
        })
    }


    /**
     * Retrieves the categoriy by linked_id and returns it as a JSON response.
        The showByLinkedId Method Is Same As Show Method But Fetch The Category By Linked Category ID instead of slug
     */
    static async showByLinkedId(request, response) {

        const query = request.body.shared ? {
            'tenant_id': request.params.supplier_id,
            'linked': new ObjectId(request.params.linked_id),
            'shareable': true,
            'published': true
        } : {
            'tenant_id': request.params.supplier_id,
            'linked': new ObjectId(request.params.linked_id),
            'published': true
        };

        let category = await SupplierCategory.aggregate([
            {
                $match: query
            },
            {
                $lookup: {
                    from: "supplier_boops",
                    let: { categoryId: "$_id" },
                    pipeline: [
                        {
                            $match: {
                                $expr: {
                                    $and: [
                                        { $eq: ["$supplier_category", "$$categoryId"] },
                                        { $ne: ["$linked", null] },
                                        { $ne: ["$linked", ""] },
                                        { $eq: ["$published", true] }
                                    ]
                                }
                            }
                        },
                        {
                            $addFields: {
                                "boops": {
                                    $filter: {
                                        input: "$boops",
                                        as: "boop",
                                        cond: {
                                            $and: [
                                                { $ne: ["$$boop.linked", null] },
                                                { $ne: ["$$boop.linked", ""] },
                                                { $eq: ["$$boop.published", true] }
                                            ]
                                        }
                                    }
                                }
                            }
                        },
                        {
                            $addFields: {
                                "boops": {
                                    $map: {
                                        input: "$boops",
                                        as: "boop",
                                        in: {
                                            $mergeObjects: [
                                                "$$boop",
                                                {
                                                    "ops": {
                                                        $filter: {
                                                            input: "$$boop.ops",
                                                            as: "op",
                                                            cond: {
                                                                $and: [
                                                                    { $ne: ["$$op.linked", null] },
                                                                    { $ne: ["$$op.linked", ""] }
                                                                ]
                                                            }
                                                        }
                                                    }
                                                }
                                            ]
                                        }
                                    }
                                }
                            }
                        },
                        {
                            $addFields: {
                                "boops": {
                                    $filter: {
                                        input: "$boops",
                                        as: "boop",
                                        cond: { $gt: [{ $size: "$$boop.ops" }, 0] }
                                    }
                                }
                            }
                        }
                    ],
                    as: "boops"
                }
            },
            {
                $match: {
                    "boops": { $ne: [] }
                }
            },
            {
                $sort: { _id: 1 }
            }
        ]);

        return response.json({
            data: category,
            message: 'Categories have been retrieved successfully.',
            status: 200
        });
    }



    /**
     * Store a new supplier category and its corresponding boops.
     *
     * @param {Object} request - The HTTP request object.
     * @param {Object} request.params - The parameters from the request.
     * @param {string} request.params.supplier_id - The supplier ID.
     * @param {Object} request.body - The body of the request.
     * @param {string} request.body.system_key - The system key of the category.
     * @param {string} request.body.name - The name of the category.
     * @param {string} request.body.tenant_name - The tenant name linked with the category.
     * @param {Array<string>} request.body.lang - Array of language identifiers.
     * @param {number} [request.body.sort=0] - The sort order of the category.
     * @param {string} [request.body.sku=""] - The SKU for the category.
     * @param {string} [request.body.description=""] - The description of the category.
     * @param {boolean} [request.body.shareable=false] - Indicates if the category is shareable.
     * @param {boolean} [request.body.published=false] - Indicates if the category is published.
     * @param {Array<Object>} [request.body.media=[]] - Media associated with the category.
     * @param {Object} [request.body.price_build={collection: true, semi_calculation: false, full_calculation: false}] - Price build information.
     * @param {Array<string>} [request.body.countries=[]] - Array of country identifiers.
     * @param {Array<Object>} [request.body.calculation_method=[
     *  { name: 'Fixed price', slug: 'fixed-price', active: true },
     *  { name: 'Sliding scale', slug: 'sliding-scale', active: false }
     * ]] - Calculation methods available for the category.
     * @param {Array<Object>} [request.body.dlv_days=[]] - Delivery days info.
     * @param {Array<Object>} [request.body.production_days=[
     *  { active: true, day: "mon", deliver_before: "12:00" },
     *  { active: true, day: "tue", deliver_before: "12:00" },
     *  { active: true, day: "wed", deliver_before: "12:00" },
     *  { active: true, day: "thu", deliver_before: "12:00" },
     *  { active: true, day: "fri", deliver_before: "12:00" },
     *  { active: false, day: "sat", deliver_before: "12:00" },
     *  { active: false, day: "sun", deliver_before: "12:00" }
     * ]] - Production days information.
     * @param {Array<Object>} [request.body.printing_method=[]] - Printing methods for the category.
     * @param {number} [request.body.start_cost=0] - Starting cost for the category.
     * @param {number} [request.body.vat=0.0] - Value Added Tax (VAT) rate.
     * @param {Object} response - The HTTP response object.
     *
     * @return {Promise<Object>} The created supplier category.
     */
    static async store(request, response) {

        if (await SupplierCategory.findOne({
            tenant_id: request.params.supplier_id,
            slug: slugify(request.body.name, {lower: true}),
        })) {
            return response.json({
                message: `Category already exists with the selected name ${request.body.name}`,
                status: 422,
            });
        }

        // Check if linked category exists
        if (request.body.linked) {
            let category = await Category.findById(request.body.linked);


            if (category) {
                try {
                    const supplier_category = await SupplierCategory.create(
                        new StoreCategoryRequest().prepare(
                            request.body,
                            request.params.supplier_id,
                            mergeDisplayNames(category.display_name, request.body.display_name),
                            category?._id,
                        )
                    );


                    const system_manifest = await SystemManifest.findOne({
                        'category': category._id
                    });

                    if (system_manifest) {
                        supplier_category.boops = system_manifest.boops;

                        console.log(supplier_category.boops, system_manifest.boops)
                        return response.json({
                            'data': supplier_category,
                            'status': 201
                        })
                    }

                    return response.json({
                        'data': supplier_category,
                        'status': 201
                    })

                } catch (e) {
                    return response.json({
                        'message': e.message,
                        'status': 422
                    })
                }

            }
        }

        try {
            const categories = [{
                name: request.body.name,
                sku: ''
            }];
            const url = 'http://assortments:5000/similarity/categories';
            const obj = {
                tenant: request.params.supplier_id,
                tenant_name: request.body.tenant_name,
                categories: categories
            };
            const headers = {'Content-Type': 'application/json'};
            let assortments_response = await axios.post(url, obj, {headers});


            let system_category = assortments_response.data.category;
            let supplier_category;
            if (Object.entries(system_category).length === 0){
                  supplier_category = await SupplierCategory.create(
                      new StoreCategoryRequest().prepare(
                        request.body,
                        request.params.supplier_id,
                        request.body.display_name,
                        null,
                ))
            } else {
                 supplier_category = await SupplierCategory.create(new StoreCategoryRequest().prepare(
                    request.body,
                    request.params.supplier_id,
                     mergeDisplayNames(system_category.display_name, request.body.display_name),
                    system_category._id?.$oid
                        ? new ObjectId(system_category._id.$oid)
                        : new ObjectId(system_category._id)
                ));
            }


            await SupplierBoops.create({
                tenant_id: supplier_category.tenant_id,
                tenant_name: supplier_category.tenant_name,
                supplier_category: new ObjectId(supplier_category._id),
                linked: supplier_category?.linked,
                name: supplier_category.name,
                display_name: supplier_category.display_name,
                system_key: supplier_category.system_key,
                slug: supplier_category.slug,
                boops: []
            });

            return response.json({
                'data': supplier_category,
                'status': 201
            })

        } catch (e) {
            console.log(e)
            return response.json({
                'message': e.message,
                'status': 422
            })
        }
    }

    /**
     * Updates a supplier category based on the provided request parameters and body.
     *
     * @param {Object} request - The request object containing parameters and body data.
     * @param {Object} request.params - The parameters passed in the request URL.
     * @param {string} request.params.supplier_id - The ID of the supplier tenant.
     * @param {string} request.params.category - The slug of the category to update.
     * @param {Object} request.body - The body data of the request containing update fields.
     * @param {string} request.body.name - The new name for the category.
     * @param {string} request.body.iso - The ISO code associated with the category.
     * @param {Array} [request.body.additional] - An optional array of additional data for updating machines.
     * @param {Object} response - The response object used to send back the HTTP response.
     * @return {Promise<Object>} The response JSON containing either the updated category data or an error message.
     */
    static async update(request, response) {
        const category = await SupplierCategory.findOne({
            tenant_id: request.params.supplier_id,
            slug: request.params.category
        });

        // Check if category exists
        if (!category) {
            return response.json({
                message: 'Category not found.',
                status: 404
            });
        }

        if (await SupplierCategory.findOne({
            tenant_id: request.params.supplier_id,
            slug: request.params.category,
            _id: {$ne: category._id}
        })) {
            return response.json({
                message: 'Category system key already exists.',
                status: 422,
            })
        }

        // Prepare machines array
        const machines = [];
        if (request.body.additional) {
            for (const a of request.body.additional) {
                machines.push(a.machine ? {machine: new ObjectId(a.machine)} : a);
            }
        }

        // get and update the category
        const _updatedCategory = await SupplierCategory.findOneAndUpdate(
            {_id: category._id},
            {
                $set: new UpdateCategoryRequest().prepare(
                    category,
                    request.body,
                    machines,
                    request.body.display_name
                )
            },
            {new: true} // This option returns the updated document
        );


        // update boops based on the category change
        await SupplierBoops.findOneAndUpdate({
                supplier_category: category._id,
                tenant_id: request.params.supplier_id,
            },
            {
                system_key: _updatedCategory.system_key,
                display_name:  request.body.display_name,
                shareable: _updatedCategory.shareable,
                published: _updatedCategory.published,
                linked: _updatedCategory.linked
            }).exec();

        // response
        return response.json({
            'data': _updatedCategory,
            'message': 'Category has been updated successfully.',
            'status': 200
        });
    }

    /**
     * Removes a machine from the supplier's inventory.
     *
     * @param {Object} request - The HTTP request object.
     * @param {Object} response - The HTTP response object.
     * @returns {Object} - An object representing the result of the operation. If an error occurs, the object will contain an Error instance.
     */
    static async destroy(request, response) {
        // delete boops, products,prices
        const category = await SupplierCategory.findOne({
            tenant_id: request.params.supplier_id,
            slug: request.params.category
        });

        if (!category) {
            return response.json({
                message: 'Category not found.',
                status: 404
            });
        }
        // delete boops related to supplier_category
        const supplier_prices = await SupplierProductPrice.aggregate([
            {
                $lookup: {
                    from: 'supplier_products',
                    localField: 'supplier_product',
                    foreignField: '_id',
                    as: 'product'
                }
            },
            {
                $project: {
                    _id: 1
                }
            }
        ]).exec();

        if (category) {
            await SupplierProduct.deleteMany({
                supplier_category: new ObjectId(category._id),
                tenant_id: request.params.supplier_id
            });
            const boops = await SupplierBoops.findOne({
                supplier_category: category._id,
                tenant_id: request.params.supplier_id
            });
            if (boops) {
                await boops.deleteOne();
            }
            await category.deleteOne();

            return response.json({
                message: "Category has been deleted successfully.",
                status: 200
            });
        } else {
            return response.json({
                message: "Category not found.",
                status: 404
            });
        }

    }



    /**
     * Removes a media file from the category media set.
     *
     * @param {Object} request - The HTTP request object.
     * @param {Object} request.body.filename - The name of deleted media file .
     * @param {Object} response - The HTTP response object.
     * @returns {Object} - An object representing the result of the operation. If an error occurs, the object will contain an Error instance.
     */

    static async removeMedia(request, response) {
        const filename = request.body.filename
        const category = await SupplierCategory.findOne({
            tenant_id: request.params.supplier_id,
            slug: request.params.category
        });

        if (!category) {
            return response.json({
                message: 'Category not found.',
                status: 404
            });
        }
        const result = await SupplierCategory.updateOne(
            {
                _id: category._id,
                tenant_id: request.params.supplier_id
            },
            {
                $pull: { media: filename }
            }
        );

        return response.json({
            message: `Media item '${filename}' removed from category.`,
            status: 200
        });
        }


}
