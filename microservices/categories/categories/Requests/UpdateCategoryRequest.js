const mongoose = require('mongoose');
const slugify = require("slugify");
const ObjectId = mongoose.Types.ObjectId;
module.exports = class UpdateCategoryRequest {
    prepare(
        category,
        request,
        machines,
        display_names
    ) {
        return {
            name: category.name,
            sort: request.sort ?? 0,
            // slug: slugify(request.name, { lower: true }),
            sku: request.sku ?? category.sku,
            display_name: display_names,
            system_key: request.system_key? slugify(request.system_key, { lower: true }) : slugify(category.system_key, { lower: true }),
            production_days: request.production_days ?? category.production_days,
            production_dlv: request.production_dlv ?? category.production_dlv,
            description: request.description ?? category.description,
            shareable: request.shareable ?? category.shareable,
            published: request.published ?? category.published,
            linked: !category.linked && request.linked? new ObjectId(request.linked) :category.linked,
            media: request.media ?? category.media,
            price_build: request.price_build ?? category.price_build,
            countries: request.countries ?? category.countries,
            has_products: request.has_products ?? category.has_products,
            has_manifest: request.has_manifest ?? category.has_manifest,
            calculation_method: request.calculation_method ?? category.calculation_method,
            ranges: request.ranges ?? category.ranges,
            range_list: request.range_list ?? category.range_list,
            free_entry: request.free_entry ?? category.free_entry,
            limits: request.limits ?? category.limits,
            bleed: request.bleed ?? category.bleed,
            range_around: request.range_around ?? category.range_around,
            dlv_days: request.dlv_days ?? category.dlv_days,
            printing_method: request.printing_method ?? category.printing_method,
            start_cost: request.start_cost ?? category.start_cost,
            additional: machines?? category.additional,
            vat: request.vat ?? category.vat,
        };
    }
}
