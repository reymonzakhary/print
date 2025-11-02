const SupplierCategory = require("../../Models/SupplierCategory")


class FetchCategory {
    tenant_id = ''
    slug = ''
    error = false
    category = null

    constructor (tenant_id, slug) {
        this.tenant_id = tenant_id
        this.slug = slug
    }

    async get() {
        this.category = (await SupplierCategory.aggregate([
            {
                '$match': {
                    "$and": [
                        {"tenant_id": this.tenant_id},
                        {"slug": this.slug}
                    ]
                }
            },{
                "$lookup": {

                    "from": "supplier_machines",
                    "localField": "additional.machine",
                    "foreignField": "_id",
                    "as": "machine"
                }
            },{
                "$project" : {
                    '_id': 1,
                    "machine": 1,
                    "ref_id": 1,
                    "display_name": 1,
                    "name": 1,
                    "slug": 1,
                    "linked": 1,
                    "countries": 1,
                    "category_slug": 1,
                    'start_cost': 1,
                    "tenant_id": 1,
                    "tenant_name": 1,
                    "production_days" : 1,
                    "calculation_method": 1,
                    "published": 1,
                    "production_dlv": 1,
                    "ranges": 1,
                }
            }
        ]))[0];

        if (!this.category) {
            this.error = {
                "message": "Category Not Exist",
                "status": 404,
            }
            return this
        }

        if (!this.category.published) {
            this.error = {
                "message": "Category is not published",
                "status": 404,
            }
            return this
        }

        return this
    }
}

module.exports = FetchCategory