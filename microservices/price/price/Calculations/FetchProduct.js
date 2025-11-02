const { json } = require("express")
const SupplierProduct = require("../Models/SupplierProduct")


class FetchProduct {
    tenant_id = ''
    slug = ''
    error = false
    category = null
    filter = []

    constructor () { }

    whereObject(product) {
        /** where  */
        for (let key in product) {
            if(!key.startsWith('_')) {
                this.filter.push({
                    "$match" : {
                        'object.key': {$eq: key},
                        'object.value': {$eq: product[key]}
                    }
                })
            }
        }
        return this
    }

    where(filter) {
        for (let key in filter) 
            if(!key.startsWith('_')) 
                this.filter.push({'$match': filter[key]})

        return this
    }

    async first() {
        /** Fetch the supplier product */
        let product =  await SupplierProduct.aggregate([
            {
                "$lookup": {
                    "from": "supplier_product_prices",
                    "localField": "_id",
                    "foreignField": "supplier_product",
                    "as": "prices"
                }
            },
            ...this.filter,
            {
                "$project" : {
                    "tenant_name": 1,
                    "host_id": 1,
                    "tenant_id": 1,
                    "category_name": 1,
                    "category_display_name": 1,
                    "category_slug": 1,
                    "linked": 1,
                    "supplier_category": 1,
                    "shareable": 1,
                    "published": 1,
                    "object": 1,
                    "runs": 1,
                    "create_at": 1,
                    "created_at": 1,
                    "additional": 1,
                    "prices": 1
                }
            }
        ])

        return product[0]
    }
}

module.exports = FetchProduct