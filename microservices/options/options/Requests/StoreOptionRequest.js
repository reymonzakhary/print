const mongoose = require("mongoose");
const slugify = require("slugify");
const ObjectId = mongoose.Types.ObjectId;

module.exports = class StoreOptionRequest {
    prepare(body, tenant_id, display_names, linked) {
        return {
            sort: body.sort ?? 0,
            name: body.name,
            display_name: display_names,
            slug: body.name ? slugify(body.name, { lower: true }) : '',
            tenant_id: tenant_id,
            system_key: body.system_key ? slugify(body.system_key, { lower: true }) : slugify(body.name, { lower: true }),
            has_children: body.has_children ?? false,
            extended_fields: body.extended_fields ?? {},
            dimension: body.dimension ?? '2d',
            dynamic: body.dynamic ?? false,
            width: body.width ?? 0,
            maximum_width: body.maximum_width ?? 0,
            minimum_width: body.minimum_width ?? 0,
            height: body.height ?? 0,
            maximum_height: body.maximum_height ?? 0,
            minimum_height: body.minimum_height ?? 0,
            length: body.length ?? 0,
            maximum_length: body.maximum_length ?? 0,
            minimum_length: body.minimum_length ?? 0,
            parent: body.parent ?? true,
            start_cost: body.start_cost ?? 0,
            boxes: body.boxes ?? [],
            additional: body.additional ?? {},
            tenant_name: body.tenant_name,
            unit: body.unit ?? 'mm',
            maximum: body.maximum,
            minimum: body.minimum,
            incremental_by: body.incremental_by ?? 0,
            information: body.information ?? '',
            input_type: body.input_type ?? '',
            dynamic_object: body.dynamic_object ?? {},
            dynamic_keys: body.dynamic_keys ?? [],
            start_on: body.start_on ?? 0,
            end_on: body.end_on ?? 0,
            generate: body.generate ?? false,
            dynamic_type: body.dynamic_type ?? 'integer',
            linked: linked ? new ObjectId(linked) : null,
            shareable: body.shareable ?? false,
            published: body.published ?? true,
            description: body.description ?? '',
            media: body.media ?? [],
            sku: body.sku ?? '',
            rpm: body.rpm ?? 0,
            sheet_runs: body.sheet_runs ?? [],
            runs: body.runs ?? [],
            configure: body.configure ?? [],
            calculation_method : body.calculation_method ?? 'qty'
        };
    }
}