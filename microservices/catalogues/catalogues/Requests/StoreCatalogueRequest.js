module.exports = class StoreCatalogueRequest {
    prepare(body, tenant_id){
        return {
            tenant_id : tenant_id,
            tenant_name : body.tenant_name,
            supplier : body.supplier,
            art_nr: body.art_nr,
            material: body.material,
            material_link: body.material_link,
            material_id: body.material_id,
            grs: body.grs,
            grs_link: body.grs_link,
            grs_id: body.grs_id,
            price: body.price,
            ean: body.ean,
            calc_type: body.calc_type,
            density: body.density,
            sheet: body.sheet,
            width: body.width,
            length: body.length,
            height: body.height,
        }
    }
}


