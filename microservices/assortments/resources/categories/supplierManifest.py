import json

from flask import jsonify
from models.supplierBoops import SupplierBoops
from models.supplierCategory import SupplierCategory
from models.box import Box
from models.option import Option
from flask_restful import Resource
from bson import ObjectId


def serialize_option(opt):
    return {
        "id": str(opt.id) if getattr(opt, "id", None) else "",
        "sort": opt.sort,
        "name": opt.name,
        "slug": opt.slug,
        "sku": opt.sku,
        "unit": opt.unit,
        "media": opt.media or [],
        "additional": getattr(opt, "additional", []),
        "dimension": opt.dimension,
        "excludes": getattr(opt, "excludes", []),
        "start_on": opt.start_on,
        "end_on": opt.end_on,
        "generate": opt.generate,
        "system_key": opt.system_key,
        "linked": str(opt.linked.id) if getattr(opt, "linked", None) else "",
        "dynamic": opt.dynamic,
        "dynamic_type": getattr(opt, "dynamic_type", ""),
        "dynamic_keys": opt.dynamic_keys,
        "extended_fields": getattr(opt, "extended_fields", []),
        "rpm": getattr(opt, "rpm", 0),
        "runs": getattr(opt, "runs", []),
        "incremental_by": opt.incremental_by,
        "published": opt.published,
        "shareable": getattr(opt, "shareable", False),
        "parent": getattr(opt, "parent", False),
        "has_children": opt.has_children,
        "start_cost": getattr(opt, "start_cost", 0),
        "calculation_method": getattr(opt, "calculation_method", 0),
        "height": getattr(opt, "height", 0),
        "minimum_height": getattr(opt, "minimum_height", 0),
        "maximum_height": getattr(opt, "maximum_height", 0),
        "width": getattr(opt, "width", 0),
        "minimum_width": getattr(opt, "minimum_width", 0),
        "maximum_width": getattr(opt, "maximum_width", 0),
        "length": getattr(opt, "length", 0),
        "minimum_length": getattr(opt, "minimum_length", 0),
        "maximum_length": getattr(opt, "maximum_length", 0),
        "display_name": opt.display_name,
    }



def serialize_box(box):
        return {
            "id": str(box.id) if getattr(box, "id", None) else "",
            "system_key": box.system_key,
            "name": box.name,
            "calculation_type": getattr(box, "calculation_type", ""),
            "tenant_id": getattr(box, "tenant_id", ""),
            "tenant_name": getattr(box, "tenant_name", ""),
            "description": box.description,
            "slug": box.slug,
            "option_limit": getattr(box, "option_limit", 0),
            "select_limit": getattr(box, "select_limit", 0),
            "sort": box.sort,
            "linked": str(box.linked.id) if getattr(box, "linked", None) else "",
            "published": box.published,
            "incremental": getattr(box, "incremental", False),
            "shareable": getattr(box, "shareable", True),
            "sqm": getattr(box, "sqm", 0),
            "appendage": getattr(box, "appendage", False),
            "ops": [serialize_option(opt) for opt in getattr(box, "ops", [])],
            "display_name": box.display_name,
        }


def serialize_sup_boops(sup_boops):
    return {
        "id": str(sup_boops.id),
        "name": sup_boops.name,
        "sort": getattr(sup_boops, "sort", 0),
        "description": getattr(sup_boops, "description", ""),
        "slug": sup_boops.slug,
        "tenant_id": sup_boops.tenant_id,
        "ref_id": getattr(sup_boops, "ref_id", ""),
        "ref_boops_id": getattr(sup_boops, "ref_boops_id", ""),
        "ref_boops_name": sup_boops.ref_boops_name,
        "category": str(sup_boops.linked.id) if sup_boops.linked else "",
        "tenant_name": sup_boops.tenant_name,
        "divided": sup_boops.divided,
        "system_key": sup_boops.system_key,
        "shareable": sup_boops.shareable,
        "published": sup_boops.published,
        "generated": sup_boops.generated,
        "has_products": getattr(sup_boops, "has_products", False),
        "has_manifest": True,  # you can flag this explicitly
        "start_cost": getattr(sup_boops, "start_cost", 0),
        "vat": getattr(sup_boops, "vat", 0),
        "shared": getattr(sup_boops, "shared", []),
        "display_name": sup_boops.display_name,
        "boops": [serialize_box(b) for b in sup_boops.boops],
        "additional": getattr(sup_boops, "additional", {}),
    }


class SupplierManifestApi(Resource):
    

    def get(self, category, tenant_id):
        category = SupplierCategory.objects(linked=category, tenant_id=tenant_id).first()
        sup_boops = SupplierBoops.objects(supplier_category=category, tenant_id=tenant_id).first()

        if not sup_boops:
            return jsonify({
                "message": "Manifest not found",
                "status": 422
            })

        sys_boops = []
        for sup_box in sup_boops.boops:
            sys_box = None

            if not sup_box['linked']:
                sys_box = Box.objects(slug=sup_box.slug).first()
            elif isinstance(sup_box['linked'], str):
                sys_box = Box.objects(id=ObjectId(sup_box['linked'])).first()
            elif isinstance(sup_box['linked'], Box):
                sys_box = sup_box['linked']

            if not sys_box:
                dbox = {
                    "sort": sup_box['sort'],
                    "name": sup_box['name'],
                    "display_name": sup_box['display_name'],
                    "system_key": sup_box['system_key'],
                    "slug":sup_box['slug'],
                    "description": sup_box['description'],
                    "sqm": sup_box['sqm'],
                    "appendage": sup_box['appendage'],
                    "published": sup_box['published'],
                    "input_type": sup_box['input_type'],
                }
                sys_box = Box(**dbox).save()

            # Now moving on to the options
            sys_box.ops = []
            for sup_opt in sup_box['ops']:
                sys_opt = None

                if not sup_opt['linked']:
                    sys_opt = Option.objects(slug=sup_opt.slug).first()
                elif isinstance(sup_opt['linked'], str):
                    sys_opt = Option.objects(id=ObjectId(sup_opt['linked'])).first()
                elif isinstance(sup_opt['linked'], Option):
                    sys_opt = sup_opt['linked']

                # print("\n===========================\n", Option.objects(id=ObjectId(sup_opt['linked'])).first(), "\n===========================\n", isinstance(sup_opt['linked'], str), "\n===========================\n")
                if not sys_opt:
                    payload = {
                        "sort": sup_opt['sort'],
                        "name": sup_opt['name'],
                        "display_name": sup_opt['display_name'],
                        "sku": sup_opt['sku'],
                        "slug": sup_opt['slug'],
                        "system_key": sup_opt['system_key'],
                        "description": sup_opt['description'],
                        "media": sup_opt['media'],
                        "dimension": sup_opt['dimension'],
                        "dynamic": sup_opt['dynamic'],
                        "dynamic_keys": sup_opt['dynamic_keys'],
                        "start_on": sup_opt['start_on'],
                        "end_on": sup_opt['end_on'],
                        "generate": sup_opt['generate'],
                        "unit": sup_opt['unit'],
                        "incremental_by": sup_opt['incremental_by'],
                        "published": sup_opt['published'],
                        "has_children": sup_opt['has_children'],
                        "configure": sup_opt['configure'],
                        "checked": sup_opt['checked']
                    }
                    sys_opt = Option(**payload).save()
                
                sys_box.ops.append(sys_opt)

            sys_boops.append(sys_box)

        sup_boops.boops = sys_boops            

        return jsonify(serialize_sup_boops(sup_boops))
        
    


    

