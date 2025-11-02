const mongoose = require("mongoose");
const slugify = require("slugify");
const ObjectId = mongoose.Types.ObjectId;

module.exports = class StoreSupplierBoxRequest {
    prepare(body, tenant_id, display_names, linked) {
        return {
            sort: body.sort ?? 0,
            tenant_id: tenant_id,
            tenant_name: body.tenant_name ?? null,
            sku: body.sku ?? "",
            name: body.name,
            slug: body.slug ?? slugify(body.name, { lower: true }),
            display_name: display_names,
            system_key: slugify(body.system_key || body.name, { lower: true }),
            input_type: body.input_type ?? null,
            incremental: body.incremental ?? false,
            select_limit: body.select_limit ?? 0,
            option_limit: body.option_limit ?? 0,
            sqm: body.sqm ?? false,
            appendage: body.appendage ?? false,
            calculation_type: body.calculation_type ?? "",
            calc_ref: body.calc_ref ?? "",
            linked: linked ? new ObjectId(linked) : null,
            description: body.description ?? null,
            media: body.media ?? [],
            shareable: body.shareable ?? false,
            published: body.published ?? true,
            additional: body.additional ?? [],
        };
    }
}