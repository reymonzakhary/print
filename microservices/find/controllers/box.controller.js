const BoxModel = require("../models/Box");
const SupplierBoxModel = require("../models/SupplierBox");
const slugify = require('slugify')

module.exports = class Box {

    static async search(req, res) {
        var page = 1
        if (req.query.page && req.query.page != 'undefined') {
            page = req.query.page
        }
        var per_page = 10
        if (req.query.perPage) {
            per_page = req.query.perPage
        }
        var skip = page == 1 ? 0 : (per_page * page) - per_page
        var search = req.query.search == null || req.query.search == "" ? "" : req.query.search;
        var sortBy = req.query.sort_by == null || req.query.sort_by == "" ? "name" : req.query.sort_by;
        var sortDir = req.query.sort_dir == null || req.query.sort_dir == "" || req.query.sort_dir == "asc" ? "" : "-1";
        var supplier_box = await SupplierBoxModel.aggregate([
            {
                "$match": {
                    "slug": {
                        "$regex": slugify(search, { lower: true }),
                        "$options": 'i'  // case-insensitive
                    },
                    "linked": {
                        "$exists": true
                    }
                },
            },
            {
                "$project": { "linked": 1, "_id": 0 }
            },
            {
                "$group": {
                    "_id": "$linked",
                }
            },
        ])
        var boxes_id = supplier_box.map(o => o['_id'])

        var found = await BoxModel.aggregate([
            {
                "$match": {
                    "$or": [
                        { "_id": { "$in": boxes_id } },
                        {
                            "slug": {
                                "$regex": slugify(search, { lower: true }),
                                "$options": 'i'  // case-insensitive
                            }
                        }
                    ]
                },

            },
            {
                "$lookup":
                {
                    "from": "supplier_boxes",
                    "let": { "id": "$_id" },
                    "pipeline": [
                        {
                            "$match":
                            {
                                "$expr":
                                {
                                    "$and":
                                        [
                                            { "$eq": ["$$id", "$linked"] },
                                        ],
                                }
                            },
                        },
                    ],
                    "as": "linked",
                },
            },

        ])
        return res.json({ "data": found });
    }


}
