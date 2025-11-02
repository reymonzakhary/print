module.exports = class StoreMachineRequest {
    prepare(body, tenant_id){
        return {
            tenant_id : tenant_id,
            tenant_name : body.tenant_name,
            name: body.name,
            description: body.description,
            type: body.type,
            unit: body.unit,
            width: body.width,
            height: body.height,
            spm: body.spm,
            price: body.price,
            sqcm: body.sqcm,
            ean: body.ean,
            pm: body.pm,
            setup_time: body.setup_time,
            cooling_time: body.cooling_time,
            cooling_time_per: body.cooling_time_per,
            mpm: body.mpm,
            divide_start_cost: body.divide_start_cost,
            spoilage: body.spoilage === null ? 0 : body.spoilage,
            wf: body.wf,
            min_gsm: body.min_gsm,
            max_gsm: body.max_gsm,

            trim_area: body.trim_area,
            trim_area_exclude_y: body.trim_area_exclude_y,
            trim_area_exclude_x: body.trim_area_exclude_x,
            margin_right: body.margin_right,
            margin_left: body.margin_left,
            margin_top: body.margin_top,
            margin_bottom: body.margin_bottom,

            colors: body.colors,
            materials: body.materials,
            printable_frame_length_min: body.printable_frame_length_min,
            printable_frame_length_max: body.printable_frame_length_max,
            fed: body.fed,
            attributes: body.attributes
        }
    }
}


