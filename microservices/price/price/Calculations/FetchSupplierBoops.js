const SupplierBoops = require('../Models/SupplierBoops')

class FetchSupplierBoops {
    tenant_id = ''
    slug = ''
    error = false
    supplierBoops = null

    constructor (tenant_id, slug) {
        this.tenant_id = tenant_id
        this.slug = slug
    }



    async first() {
        let boops = (await SupplierBoops.aggregate([
            {
                '$match': {
                    "$and": [
                        {"tenant_id": this.tenant_id},
                        {"slug": this.slug}
                    ]
                }
            },{
                "$project" : {
                    "_id": 1,
                    "tenant_id": 1,
                    "ref_id": 1,
                    "ref_boops_name": 1,
                    "tenant_name": 1,
                    "supplier_category": 1,
                    "linked": 1,
                    "display_name,": 1,
                    "system_key": 1,
                    "shareable": 1,
                    "published": 1,
                    "generated": 1,
                    "name": 1,
                    "slug": 1,
                    "boops": 1,
                    "additional": 1,
                }
            }
        ]))[0]
        return boops
    }
}

module.exports = FetchSupplierBoops