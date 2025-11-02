const CategoryModel = require("../models/Category");
const SupplierCategoryModel = require("../models/SupplierCategory");
const SupplierBoopModel = require("../models/SupplierBoops");
const slugify = require('slugify')
const mongoose = require("mongoose");
const ObjectId = mongoose.Types.ObjectId
module.exports = class Category {

    /**
     *
     * @param req
     * @param res
     * @returns {{result: Error}}
     */
    static async index(req, res) {

        let page = 1
        if (req.query.page && req.query.page !== 'undefined') {
            page = parseInt(req.query.page)
        }
        let per_page = 10
        if (req.query.per_page) {
            per_page = parseInt(req.query.per_page)
        }
        let skip = page === 1 ? 0 : (per_page * page) - per_page
        let items = [];
        let categories = await CategoryModel.aggregate([
            {
                "$facet": {
                    "data": [
                        {"$unset": "_id"},
                        {"$skip": skip},
                        {"$limit": per_page},
                    ],
                    "count": [{
                        "$count": "count"
                    }]
                },
            }
        ])
        if (categories[0] !== undefined){
            items = categories[0]['data']
        }
        let count = 0;
        if ( categories['count'] === undefined || categories['count'].length === 0){
            count = 0
        }else{
            count = categories['count']['count']
        }

        let last_page = Math.ceil(count / per_page)
        return res.json({
                   "data": items,
                   "page": page,
                   "per_page": per_page,
                   "total": count,
                   "lastPage": last_page,
                   "status": 200,
               });
    }

    /**
     *
     * @param req
     * @param res
     * @returns {{result: Error}}
     */
    static async categoryBySlug(req, res) {
        let slug = req.params.slug
        let category = await CategoryModel.aggregate([
            {
                "$match": {
                    "slug": slug
                },
            },
            {
                "$lookup":
                    {
                        "from": "boxes",
                        // # "localField": "_id",
                        // # "foreignField": "categories",
                        "let": {"category": "$_id"},
                        "pipeline": [
                            {"$match":
                                {"$expr":
                                    {
                                        "$and":
                                            [
                                                {"$in": ["$$category", "$categories"]},
                                            ],
                                    }
                                },

                            },
                            {"$match":
                                {"slug":
                                    {
                                        "$nin":
                                            [
                                                "printing-process",
                                                "delivery-type",
                                                "quantity"
                                            ]
                                    }
                                }
                            },

                            {
                                "$lookup": {
                                    "from": "category_box_options",
                                    "let": {"box": "$_id"},
                                    "pipeline": [
                                        {"$match":
                                            {"$expr":
                                                {
                                                    "$and":
                                                        [
                                                            {"$eq": ["$$category", "$category"]},
                                                            {"$eq": ["$$box", "$box"]},
                                                        ],
                                                }
                                            },
                                        },
                                        {
                                            "$lookup": {
                                                "from": "options",
                                                "let": {"option": "$option"},
                                                "pipeline": [
                                                    {"$match":
                                                        {"$expr":
                                                            {
                                                                "$and":
                                                                    [
                                                                        {"$eq": ["$$option", "$_id"]},
                                                                    ],
                                                            }
                                                        },
                                                    },

                                                ],
                                                "as": "option"
                                            }},
                                        {"$project": {
                                            "option": {"$arrayElemAt": ["$option", 0]}
                                        }},
                                    ],
                                    "as": "options"
                                }},
                        ],
                        "as": "boxes",

                    },
            },
            {"$project": {"boxes.categories": 0,
                          "boxes.options._id": 0,
                          "boxes.options.category": 0,
                          "boxes.options.box": 0,
                        //   #   "countryInfo": { "$arrayElemAt": [ "$countryInfo", 0 ] }
                          }},

        ])
        if (category[0] !== undefined){
            let boxes = category[0]['boxes'].map(function (box) {
                box["options"] = box['options'].map(o=> o['option']).flat()
                return box;
            }).flat();

        }
        return res.json(category[0])
    }

    /**
     *
     * @param req
     * @param res
     * @returns {{result: Error}}
     */
    static async search(req, res) {
        const page = parseInt(req.query.page) || 1;
        const perPage = parseInt(req.query.per_page) || 10;
        const skip = (page - 1) * perPage;
        const search = req.query.search?.trim() || '';
        const sortBy = req.query.sort_by?.trim() || 'name';
        const sortDir = req.query.sort_dir === 'desc' ? -1 : 1;
        const uuids = req.query.contracted??[];

        // Step 1: Get initial category IDs from supplier categories
        const supplierCategories = await SupplierCategoryModel.aggregate([
            {
                $match: {
                    $or: [
                        {
                            $and: [
                                { tenant_id: { $in: uuids } },
                                { shareable: true },
                                { linked: { $exists: true, $ne: null } }
                            ]
                        },
                        {
                            $and: [
                                { tenant_id: req.query.uuid },
                                { linked: { $exists: true, $ne: null } }
                            ]
                        }
                    ],
                    ...(search && {
                        slug: {
                            $regex: slugify(search, { lower: true }),
                            $options: 'i'
                        }
                    })
                }
            },
            {
                $group: { _id: "$_id" }
            }
        ]);


        const initialCategoryIds = supplierCategories.map(cat => cat._id);

        // Step 2: Check SupplierBoopModel for those categories and validate all boops & ops are linked
        const validBoops = await SupplierBoopModel.aggregate([
            {
                $match: {
                    $or: [
                        {
                            supplier_category: { $in: initialCategoryIds },
                            shareable: true
                        },
                        {
                            supplier_category: { $in: initialCategoryIds },
                            tenant_id: req.query.uuid
                        }
                    ]

                }
            },
            {
                $project: {
                    supplier_category: 1,
                    linked: 1,
                    all_boops_valid: {
                        $allElementsTrue: {
                            $map: {
                                input: "$boops",
                                as: "boop",
                                in: {
                                    $and: [
                                        { $ne: ["$$boop.linked", null] },
                                        { $ne: ["$$boop.linked", ""] },
                                        { $gt: [{ $size: "$$boop.ops" }, 0] },
                                        {
                                            $allElementsTrue: {
                                                $map: {
                                                    input: "$$boop.ops",
                                                    as: "op",
                                                    in: {
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
                }
            },
            {
                $match: {
                    all_boops_valid: true
                }
            },
            {
                $group: {
                    _id: "$linked"
                }
            }
        ]);

        // console.log(validBoops)
        const filteredCategoryIds = validBoops.map(b => b._id);

        // Step 3: Fetch final categories
        const categories = await CategoryModel.aggregate([
            {
                $match: {
                    _id: { $in: filteredCategoryIds },
                    ...(search && {
                        slug: {
                            $regex: slugify(search, { lower: true }),
                            $options: 'i'
                        }
                    })
                }
            },
            {
                $lookup: {
                    from: "supplier_categories",
                    let: { id: "$_id" },
                    pipeline: [
                        {
                            $match: {
                                $expr: { $eq: ["$linked", "$$id"] }
                            }
                        }
                    ],
                    as: "linked"
                }
            },
            {
                $sort: { [sortBy]: sortDir }
            },
            {
                $skip: skip
            },
            {
                $limit: perPage
            }
        ]);

        return res.json({ data: categories });
    }

}
