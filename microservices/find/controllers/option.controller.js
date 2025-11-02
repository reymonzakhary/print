const OptionModel = require("../models/Option");
const SupplierOptionModel = require("../models/SupplierOption");
const slugify = require('slugify');
const { findOneAndDelete } = require("../models/SupplierBox");

module.exports = class Option {

    /**
     *
     * @param req
     * @param res
     * @returns {{result: Error}}
     */
    static async search(req, res) {
        let page = 1
        if (req.query.page && req.query.page !== 'undefined') {
            page = req.query.page
        }

        let per_page = req.query.per_page? parseInt(req.query.per_page):10

        if (req.query.perPage) {
            per_page = req.query.perPage
        }
        let skip = page === 1 ? 0 : (per_page * page) - per_page
        let search = req.query.search === null || req.query.search === "" || req.query.search === undefined ? "" : req.query.search;
        let sortBy = req.query.sort_by === null || req.query.sort_by === "" ? "name" : req.query.sort_by;
        let sortDir = req.query.sort_dir === null || req.query.sort_dir === "" || req.query.sort_dir === "asc" ? "" : "-1";


        let supplierOption = await SupplierOptionModel.aggregate([

            {
                "$match": {
                    "slug": {
                        "$regex": slugify(search, { lower: true }),
                        "$options": 'i'  // # case-insensitive
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
        let catIds = supplierOption.map(o => o['_id']).flat()
        let found = await OptionModel.aggregate([
            {
                "$facet": {
                    "data": [
                        {
                            "$match": {
                                "$or": [
                                    { "_id": { "$in": catIds } },
                                    {
                                        "slug": {
                                            "$regex": slugify(search, { lower: true }),
                                            "$options": 'i'  // # case-insensitive
                                        }
                                    }
                                ]
                            },

                        },
                        {
                            "$lookup":
                            {
                                "from": "supplier_options",
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
                        {
                            "$addFields": {
                                "display_name": {
                                    "$let": {
                                        "vars": {
                                            "filteredEn": { "$filter": { "input": "$display_name", "as": "item", "cond": { "$eq": ["$$item.iso", "en"] } } },
                                            "filteredFr": { "$filter": { "input": "$display_name", "as": "item", "cond": { "$eq": ["$$item.iso", "fr"] } } },
                                            "filteredNl": { "$filter": { "input": "$display_name", "as": "item", "cond": { "$eq": ["$$item.iso", "nl"] } } },
                                            "filteredRU": { "$filter": { "input": "$display_name", "as": "item", "cond": { "$eq": ["$$item.iso", "ru"] } } },
                                        },
                                        "in": {
                                            "$concatArrays": [
                                                { "$slice": ["$$filteredEn", 1] },
                                                { "$slice": ["$$filteredFr", 1] },
                                                { "$slice": ["$$filteredNl", 1] },
                                                { "$slice": ["$$filteredRU", 1] }
                                            ]
                                        }
                                    }
                                }
                            }
                        },
                        {
                            "$limit": per_page
                        },
                        {
                            "$skip": skip
                        },
                    ],
                    "total": [{ "$count": "total" }]
                }
            }
        ]).then(data => {
            let items = data[0]['data']
            let count = data[0]['total'] === undefined || data[0]['total'].length === 0 ? 0 : data[0]['total']['total']
            let last_page = Math.ceil(count / per_page)
            let next_page = last_page <= page ? null : page + 1
            let first_page = 1
            if (data[0]['total'] === undefined || data[0]['total'].length === 0) {
                count = 0
            }
            else {
                count = data[0]['total'][0]['total']
            }
            return res.json({
                "data": items,
                "pagination": {
                    "current_page": page,
                    "first_page_url": "/?page=" + first_page,
                    "last_page_url": "/?page=" + last_page,
                    "prev_page_url": "/?page=" + page > 1 ? page - 1 : null,
                    "next_page_url": "/?page=" + next_page ? next_page : null,
                    "from": skip,
                    "to": skip + per_page,
                    "last_page": last_page,
                    "per_page": per_page,
                    "total": count
                }
            });
        })

    }

}
