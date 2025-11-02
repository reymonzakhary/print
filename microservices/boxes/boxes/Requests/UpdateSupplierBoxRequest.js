const mongoose = require('mongoose');
const slugify = require("slugify");
const ObjectId = mongoose.Types.ObjectId;

module.exports = class UpdateSupplierBoxRequest {
    prepare(
        supplierBox,
        request,
        display_names
    ) {
        return {
            row_id: request.row_id ?? supplierBox.row_id,
            sort: request.sort ?? supplierBox.sort,
            sku: request.sku ?? supplierBox.sku,
            name: supplierBox.name,
            source_slug: request.source_slug ?? supplierBox.source_slug,
            display_name: display_names,
            system_key: request.system_key 
                ? slugify(request.system_key, { lower: true }) 
                : slugify(supplierBox.system_key, { lower: true }),
            input_type: request.input_type ?? supplierBox.input_type,
            incremental: request.incremental ?? supplierBox.incremental,
            select_limit: request.select_limit ?? supplierBox.select_limit,
            option_limit: request.option_limit ?? supplierBox.option_limit,
            sqm: request.sqm ?? supplierBox.sqm,
            appendage: request.appendage ?? supplierBox.appendage,
            calculation_type: request.calculation_type ?? supplierBox.calculation_type,
            linked: !supplierBox.linked && request.linked 
                ? new ObjectId(request.linked) 
                : supplierBox.linked,
            description: request.description ?? supplierBox.description,
            media: request.media ?? supplierBox.media,
            shareable: request.shareable ?? supplierBox.shareable,
            published: request.published ?? supplierBox.published,
            additional: request.additional ?? supplierBox.additional,
        };
    }
}