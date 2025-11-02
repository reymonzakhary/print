const mongoose = require("mongoose");
const slugify = require("slugify");
const ObjectId = mongoose.Types.ObjectId
module.exports = class StoreCategoryRequest {
    prepare(body, tenant_id, display_names, linked) {
        return {
            sort: body.sort ?? null,
            tenant_id: tenant_id,
            tenant_name: body.tenant_name,
            countries: body.countries ?? [],
            sku: body.sku ?? "",
            name: body.name,
            system_key: slugify(body.system_key, { lower: true }),
            display_name: display_names,
            description: body.description ?? null,
            shareable: body.shareable ?? false,
            published: body.published ?? true,
            media: body.media ?? [],
            price_build: body.price_build ?? {
                "collection": false,
                "semi_calculation": true,
                "full_calculation": false,
                "external_calculation": false,
            },
            has_products: body.has_products ?? false,
            has_manifest: body.has_manifest ?? false,
            calculation_method: body.calculation_method ?? [
                {
                    "name": 'Fixed price',
                    "slug": 'fixed-price',
                    "active": true
                },
                {
                    "name": 'Sliding scale',
                    "slug": 'sliding-scale',
                    "active": false
                }
            ],
            dlv_days: body.dlv_days ?? [],
            printing_method: body.printing_method ?? [],
            production_days: body.production_days ?? [
                {
                    "active": true,
                    "day": "mon",
                    "deliver_before": "12:00"
                },
                {
                    "active": true,
                    "day": "tue",
                    "deliver_before": "12:00"
                },
                {
                    "active": true,
                    "day": "wed",
                    "deliver_before": "12:00"
                },
                {
                    "active": true,
                    "day": "thu",
                    "deliver_before": "12:00"
                },
                {
                    "active": true,
                    "day": "fri",
                    "deliver_before": "12:00"
                },
                {
                    "active": false,
                    "day": "sat",
                    "deliver_before": "12:00"
                },
                {
                    "active": false,
                    "day": "sun",
                    "deliver_before": "12:00"
                },
            ],
            start_cost: body.start_cost ?? 0,
            linked: linked?new ObjectId(linked):null,
            ref_id: body.ref_id ?? null,
            ref_category_name: body.ref_category_name ?? null,
            bleed: body.bleed ?? 0,
            range_list: body.range_list ?? [],
            ranges: body.ranges ?? [],
            limits: body.limits ?? [],
            free_entry: body.free_entry ?? [],
            range_around: body.range_around ?? 0,
            vat: body.vat ?? 0,
            additional: body.additional ?? [],
        };
    }
}
