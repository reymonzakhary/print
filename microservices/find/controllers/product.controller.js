const CategoryModel = require("../models/Category");
const SupplierProduct = require("../models/SupplierProduct");
const SupplierCategory = require("../models/SupplierCategory");
var mongoose = require('mongoose');
const {log} = require("debug");
let ObjectId = mongoose.Types.ObjectId
module.exports = class Product {

    /**
     * handle finder filter
     * @param req
     * @param res
     * @returns {Promise<void>}
     */
    static async productsBySlug(req, res) {
        let slug = req.params.slug
        let data = req.body
        let supplierIds = data.suppliers.map((sId) => {
            return sId['supplier_id'];
        })

        let host_ids = data.suppliers.map((sId) => {
            return sId['host_id']
        })

        let me = data.me

        let page = 1
        if (req.query.page && req.query.page !== 'undefined') {
            page = req.query.page
        }

        let per_page = 10
        if (req.query.perPage) {
            per_page = req.query.perPage
        }

        let filter = data['product'] !== undefined ? data['product'] : {};
        let dlv = data.dlv;
        let qty = data.qty;
        let newPerPage = parseInt(per_page) * parseInt(page)
        let newSkip = parseInt(newPerPage) - parseInt(per_page)
        let category_ids


        let catObject = await CategoryModel.aggregate(
            [
                {
                    "$match": {
                        "$and": [
                            {"slug": slug},
                            {"published": true}
                        ]
                    }
                },
                {
                    "$lookup": {
                        "from": "supplier_categories",
                        "let": {"id": "$_id"},
                        "pipeline": [
                            {
                                "$match":
                                    {
                                        "$expr":
                                            {
                                                "$or":
                                                    [
                                                        {
                                                            "$and": [
                                                                {"$in": ["$tenant_id", supplierIds]},
                                                                {"$eq": ["$linked", "$$id"]},
                                                                {"$eq": ["$published", true]},
                                                                {"$eq": ["$shareable", true]}
                                                            ]
                                                        },
                                                        {
                                                            "$and": [
                                                                {"$in": ["$tenant_id", [me.supplier_id]]},
                                                                {"$eq": ["$linked", "$$id"]},
                                                                {"$eq": ["$published", true]},
                                                                {"$eq": ["$shareable", false]}
                                                            ]
                                                        }
                                                    ]
                                            }
                                    }
                            },
                            {
                                "$project": {
                                    "_id": 1,
                                    "price_build" : 1
                                }
                            }
                        ],
                        "as": "category_ids"
                    },
                },
                {
                    "$project": {
                        "category_ids._id": 1,
                        "category_ids.price_build": 1,
                    }
                }
            ]
        );


        if (!catObject.length) {
            return res.json({
                'message': "Category doesn't exists",
                'status': 404
            }, 200);
        }
        category_ids = catObject[0].category_ids.map(i => {
            return i._id;
        })

        let category_calculation = catObject['0'].category_ids

        let product = []
        let delivery_type = []

        product.push(
            {
                "$match": {
                    "$and": [{"supplier_category": {"$in": category_ids}}]
                }
            }
        )

        if (Object.keys(filter).length > 0) {
            Object.keys(filter).forEach(att => {
                if (att === "Delivery Type") {
                    delivery_type = filter[att]
                } else {
                    product[0]['$match']['$and'].push(
                        {
                            'object.key_link': ObjectId(att),
                            'object.value_link': {"$in": filter[att].split(',').map((k) => ObjectId(k))}
                        }
                    );
                }
            });
        }

        let pricesMatches = [{"$eq": ["$supplier_product", "$$id"]}]

        if (supplierIds) {
            pricesMatches.push({"supplier_id": ["$in", supplierIds]})
            pricesMatches.push({"host_id": ["$in", host_ids]})
            product.push({
                "$match": {
                    "$and": [
                        {
                            "tenant_id": {
                                "$in": supplierIds
                            }
                        },
                        {
                            "host_id": {
                                "$in": host_ids
                            }
                        }
                    ]
                }
            })

        }

        if (dlv) {
            let dlvs = dlv.split(',')
            let dlvl = dlvs.length
            if (dlvl > 1) {
                pricesMatches.push(
                    {
                        "$lte": [
                            "$tables.dlv.days", parseInt(dlvs[1])
                        ]
                    }
                )
            }

            pricesMatches.push(
                {
                    "$gte": [
                        "$tables.dlv.days", parseInt(dlvs[0])
                    ],
                }
            )
        }

        if (qty) {
            let qtys = qty.split(',')
            let qtyl = qtys.length
            if (qtyl > 1) {
                pricesMatches.push(
                    {
                        "$lte": [
                            "$tables.qty", parseInt(qtys[1])
                        ]
                    }
                )
            }

            pricesMatches.push(
                {
                    "$gte": [
                        "$tables.qty", parseInt(qtys[0])
                    ],
                }
            )
        }
        product.push({
            "$lookup": {
                "from": "supplier_product_prices",
                "let": {"id": "$_id"},
                "pipeline": [
                    {
                        "$match":
                            {
                                "$expr":
                                    {
                                        "$and": pricesMatches
                                    }
                            }
                    }
                ],

                "as": "prices",
            },
        })

        // product.push({"$unwind": { "path": "$prices"}})


        // product.push({
        //     "$group": {
        //         _id: {
        //             _id: "$_id",
        //             tenant_name :"$tenant_name",
        //             host_id : "$host_id",
        //             tenant_id : "$tenant_id",
        //             category_name : "$category_name",
        //             category_display_name : "$category_display_name",
        //             category_slug : "$category_slug",
        //             linked : "$linked",
        //             supplier_category: "$supplier_category",
        //             shareable : "$shareable",
        //             published : "$published",
        //             object : "$object",
        //             runs: "$runs",
        //             created_at: "$create_at",
        //             additional : "$additional",
        //
        //         },
        //         prices: { "$push": "$prices" }
        //     }
        // })
        product.push({
            "$facet": {
                "count": [{"$count": "total"}],
                "data": [{"$skip": newSkip}, {"$limit": newPerPage}]
            }
        })
        await SupplierProduct.aggregate(product).then(result => {
            let total = 0
            let res_data = []
            if (result) {
                if (result[0]['count'] && result[0]['count'].length) {
                    total = result[0]['count'][0]['total']
                }
                if (result[0]['data'] && result[0]['data'].length) {
                    res_data = result[0]['data']
                }
            }




            return res.json({
                "data": res_data,
                "page": page,
                "per_page": per_page,
                "total": total,
                "pagination": {
                    "first_page_url": "",
                    "last_page_url": "",
                    "prev_page_url": "",
                    "next_page_url": "",
                    "current_page": page,
                    "from": newSkip,
                    "to": newPerPage,
                    "page": page,
                    "per_page": per_page,
                    "total": total,
                    "lastPage": Math.ceil(parseInt(total) / parseInt(per_page)),
                    "last_page": Math.ceil(parseInt(total) / parseInt(per_page)),
                },
                "lastPage": Math.ceil(parseInt(total) / parseInt(per_page)),
                "status": 200,
            });
        })

    }

    static async getProducts(req, res) {
        let slug = req.params.slug
        let data = req.body

        let category = await SupplierCategory.find({
            'slug': slug,
            'tenant_id': req.body.tenant_id,
            'published': true
        })
        if (!category.length){
            return res.json({
                'message': 'Category Not exists',
                'status' : 404
            })
        }
        let filter = data['product'] !== undefined ? data['product'] : {};
        let dlv = data.dlv;
        let qty = data.qty;
        let query = []
        let delivery_type = []

        query.push(
            {
                "$match": {
                    "$and": [{"supplier_category": category[0]._id}]
                }
            }
        )

        if (Object.keys(filter).length > 0) {
            Object.keys(filter).forEach(att => {
                if (att === "Delivery Type") {
                    delivery_type = filter[att]
                } else {
                    query[0]['$match']['$and'].push(
                        {
                            'object.key_link': ObjectId(att),
                            'object.value_link': {"$in": filter[att].split(',').map((k) => ObjectId(k))}
                        }
                    );
                }
            });
        }

        let pricesMatches = [{"$eq": ["$supplier_query", "$$id"]}]

        pricesMatches.push({"supplier_id": req.body.tenant_id})
        query.push({
            "$match": {
                "$and": [
                    {
                        "tenant_id": req.body.tenant_id
                    }
                ]
            }
        })
        if (dlv) {
            let dlvs = dlv.split(',')
            let dlvl = dlvs.length
            if (dlvl > 1) {
                pricesMatches.push(
                    {
                        "$lte": [
                            "$tables.dlv.days", parseInt(dlvs[1])
                        ]
                    }
                )
            }

            pricesMatches.push(
                {
                    "$gte": [
                        "$tables.dlv.days", parseInt(dlvs[0])
                    ],
                }
            )
        }

        if (qty) {
            let qtys = qty.split(',')
            let qtyl = qtys.length
            if (qtyl > 1) {
                pricesMatches.push(
                    {
                        "$lte": [
                            "$tables.qty", parseInt(qtys[1])
                        ]
                    }
                )
            }

            pricesMatches.push(
                {
                    "$gte": [
                        "$tables.qty", parseInt(qtys[0])
                    ],
                }
            )
        }
        query.push({
            "$lookup": {
                "from": "supplier_product_prices",
                "let": {"id": "$_id"},
                "pipeline": [
                    {
                        "$match":
                            {
                                "$expr":
                                    {
                                        "$and": pricesMatches
                                    }
                            }
                    }
                ],

                "as": "prices",
            },
        })

        let product =  await SupplierProduct.aggregate(query).then(result=>{
            res.json(result)
        })
    }
}
