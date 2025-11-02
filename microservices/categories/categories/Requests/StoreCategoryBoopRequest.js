const mongoose = require(mongoose);
const slugify = require("slugify");
const ObjectId = mongoose.Types.ObjectId
module.exports = class StoreCategoryBoopRequest {
    prepare(body, tenant_id, slug, display_names) {
        return {
            boops: '',
            display_name: display_names,
            generated: true,
            name: body.name,
            system_key: slugify(body.system_key, { lower: true }),
            published: true,
            ref_boops_name: '',
            ref_id: '',
            shareable: false,
            // slug: slugify(body['system_key'], to_lower=True),
            tenant_id: tenant_id,
            tenant_name: body.tenant_name
        };
    }
}
