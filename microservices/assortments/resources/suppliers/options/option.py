from flask import Response, request, jsonify
from models.supplierOption import SupplierOption
from models.supplierBoops import SupplierBoops
from models.option import Option
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource, fields, marshal
from bson.json_util import dumps
import json
import math
import requests
from bson import ObjectId


# Helper to get query params with default
def get_query_param(name, default=None, cast=str):
    val = request.args.get(name)
    return default if val is None or val == "" else cast(val)

##############################
#   handel index and store  #
#############################
class SupplierOptionsApi(Resource):

    def get(self, supplier_id):
        # Get and sanitize query params
        page = get_query_param("page", 1, int)
        per_page = get_query_param("per_page", 10, int)
        find = get_query_param("filter", "")
        calc_ref = get_query_param("ref", "")
        sort_by = get_query_param("sort_by", "name")
        sort_dir = get_query_param("sort_dir", "asc")

        skip = (page - 1) * per_page
        sort_prefix = "" if sort_dir == "asc" else "-"

        # Build aggregation pipeline
        pipeline = []

        if find:
            pipeline.append({
                "$match": {
                    "name": {
                        "$regex": find,
                        "$options": "i"
                    }
                }
            })

        if calc_ref:
            pipeline.append({
                "$match": {
                    "additional.calc_ref": {
                        "$regex": calc_ref,
                        "$options": "i"
                    }
                }
            })

        pipeline.append({
            "$facet": {
                "data": [
                    {"$skip": skip},
                    {"$limit": per_page},
                    {
                        "$lookup": {
                            "from": "supplier_options",
                            "foreignField": "category",
                            "localField": "_id",
                            "as": "suppliers",
                        }
                    },
                    {
                        "$lookup": {
                            "from": "matched_options",
                            "foreignField": "category",
                            "localField": "_id",
                            "as": "matches",
                        }
                    },
                ],
                "count": [{"$count": "count"}],
            }
        })

        # Run aggregation directly with sorting
        options_cursor = (
            SupplierOption.objects(tenant_id=supplier_id)
            .order_by(sort_prefix + sort_by)
            .aggregate(pipeline)
        )

        # Convert cursor to JSON-friendly dict
        options = json.loads(dumps(*options_cursor))

        # Parse results
        items = options.get("data", [])
        count = options.get("count", [{}])[0].get("count", 0)

        last_page = math.ceil(count / per_page) if per_page else 1
        next_page = page + 1 if page < last_page else None

        # Final response
        return {
            "pagination": {
                "total": count,
                "per_page": per_page,
                "current_page": page,
                "last_page": last_page,
                "first_page_url": f"/?page=1",
                "last_page_url": f"/?page={last_page}",
                "next_page_url": f"/?page={next_page}" if next_page else None,
                "prev_page_url": f"/?page={page - 1}" if page > 1 else None,
                "path": '/',
                "from": skip,
                "to": skip + per_page,
            },
            "data": items,
        }, 200

    def post(self, supplier_id):
        body = request.get_json()
        # return linked
        supplier_option = SupplierOption.objects(tenant_id=supplier_id,
                                                 slug=slugify(body['system_key'], to_lower=True)).first()
        configure = []

        if supplier_option:
            if 'category_id' in body and body['category_id']:
                # Filter the configure list based on category_id
                filtered_data = [item for item in supplier_option['configure'] if
                                 item['category_id'] == ObjectId(body['category_id'])]

                # Extract 'configure' only if filtered_data is not empty
                supplier_option['configure'] = filtered_data[0].get('configure', []) if filtered_data else []
            return jsonify(supplier_option)
        else:

            configure.append({
                'category_id': ObjectId(body['category_id']),
                "configure": {
                    "incremental_by": body['incremental_by'] if 'incremental_by' in body else 0,
                    "dimension": body['dimension'] if 'dimension' in body else "",
                    "dynamic": body['dynamic'] if 'dynamic' in body else False,
                    "dynamic_keys": body['dynamic_keys'] if 'dynamic_keys' in body else [],
                    "start_on": body['start_on'] if 'start_on' in body else 0,
                    "end_on": body['end_on'] if 'end_on' in body else 0,
                    "generate": body['generate'] if 'generate' in body else False,
                    "dynamic_type": body['dynamic_type'] if 'dynamic_type' in body else "integer",
                    "unit": body['unit'] if 'unit' in body else "mm",
                    "width": body['width'] if 'width' in body else 0,
                    "maximum_width": body['maximum_width'] if 'maximum_width' in body else 0,
                    "minimum_width": body['minimum_width'] if 'minimum_width' in body else 0,
                    "height": body['height'] if 'height' in body else 0,
                    "maximum_height": body['maximum_height'] if 'maximum_height' in body else 0,
                    "minimum_height": body['minimum_height'] if 'minimum_height' in body else 0,
                    "length": body['length'] if 'length' in body else 0,
                    "maximum_length": body['maximum_length'] if 'maximum_length' in body else 0,
                    "minimum_length": body['minimum_length'] if 'minimum_length' in body else 0,
                    "start_cost": body['start_cost'] if 'start_cost' in body else 0,
                    "calculation_method": body['calculation_method'] if 'calculation_method' in body else [],
                }
            })

            dataToStore = {
                "sort": body['sort'] if 'sort' in body else 0,
                "tenant_name": body["tenant_name"],
                "tenant_id": supplier_id,
                "name": body['system_key'],  # option.name,
                "display_name": body['display_name'] if 'display_name' in body else [],
                "slug": slugify(body['system_key'], to_lower=True),
                "source_slug": body['source_slug'] if 'source_slug' in body else None,
                "system_key": body['system_key'] if 'system_key' in body else '',
                "description": body['description'] if 'description' in body else "",
                "information": body['information'] if 'information' in body else "",
                "media": body['media'] if 'media' in body else [],
                "published": bool(body['published']) if 'published' in body else False,
                "configure": configure,

                "input_type": body['input_type'] if 'input_type' in body else "",
                "extended_fields": body['extended_fields'] if 'extended_fields' in body else [],
                "shareable": bool(body['shareable']) if 'shareable' in body else False,
                "sku": body['sku'] if 'sku' in body else "",
                "parent": body['parent'] if 'parent' in body else False,
                "runs": body['runs'] if 'runs' in body else [],
                "additional": body['additional'] if 'additional' in body else {},

                ## has to be removed
                "incremental_by": body['incremental_by'] if 'incremental_by' in body else 0,
                "dimension": body['dimension'] if 'dimension' in body else "",
                "dynamic": body['dynamic'] if 'dynamic' in body else False,
                "dynamic_keys": body['dynamic_keys'] if 'dynamic_keys' in body else [],
                "start_on": body['start_on'] if 'start_on' in body else 0,
                "end_on": body['end_on'] if 'end_on' in body else 0,
                "generate": body['generate'] if 'generate' in body else False,
                "dynamic_type": body['dynamic_type'] if 'dynamic_type' in body else "integer",
                "unit": body['unit'] if 'unit' in body else "mm",
                "width": body['width'] if 'width' in body else 0,
                "maximum_width": body['maximum_width'] if 'maximum_width' in body else 0,
                "minimum_width": body['minimum_width'] if 'minimum_width' in body else 0,
                "height": body['height'] if 'height' in body else 0,
                "maximum_height": body['maximum_height'] if 'maximum_height' in body else 0,
                "minimum_height": body['minimum_height'] if 'minimum_height' in body else 0,
                "length": body['length'] if 'length' in body else 0,
                "maximum_length": body['maximum_length'] if 'maximum_length' in body else 0,
                "minimum_length": body['minimum_length'] if 'minimum_length' in body else 0,
                "start_cost": body['start_cost'] if 'start_cost' in body else 0,
                "calculation_method": body['calculation_method'] if 'calculation_method' in body else [],
            }

            if "linked" in body and body['linked']:
                option = Option.objects(id=body['linked']).first()
                if option:
                    dataToStore['name'] = option.name
                    dataToStore['linked'] = option
                else:
                    return {
                        "data": None,
                        "message": "Option is not exists.",
                        "status": 422
                    }, 200

            supplier_option = SupplierOption(**dataToStore).save()
            options = [{
                "name": body['system_key'],
                "sku": ""}]
            url = 'http://assortments:5000/similarity/options'
            obj = {'tenant': supplier_id, 'tenant_name': body["tenant_name"], 'options': options}

            header = {"Content-type": "application/json"}
            # return json.dumps(options)
            res = requests.post(url, json=obj, headers=header)
            return jsonify(supplier_option)


########################################
############ update delete class #######
########################################
class SupplierOptionApi(Resource):
    def get(self, supplier, slug):
        supplierOption = SupplierOption.objects(tenant_id=supplier, slug=slug).first()

        return jsonify(supplierOption)

    def put(self, supplier, option_id):
        body = request.get_json()
        opt = SupplierOption.objects(tenant_id=supplier, id=option_id).first()
        linked = None
        if opt:
            try:
                linked = opt.linked  # This triggers the reference resolution
            except Exception:
                opt['linked'] = None

        if not opt:
            return {
                "message": "Option not found.",
                "status": 404
            }, 200


        self.update_media(supplier, opt['slug'], body)

        data_to_store = {
            "sort": body['sort'] if 'sort' in body else opt['sort'],
            "tenant_name": opt["tenant_name"],
            "tenant_id": opt['tenant_id'],
            "name": opt['name'],  # option.name,
            "display_name": body['display_name'] if 'display_name' in body else opt['display_name'],
            "slug": opt['slug'],
            "source_slug": opt['source_slug'],
            "system_key": opt['system_key'],
            "description": body['description'] if 'description' in body else opt['description'],
            "information": body['information'] if 'information' in body else opt['information'],
            "media": body['media'] if 'media' in body else opt['media'],
            "published": bool(body['published']) if 'published' in body else opt['published'],
            "configure": opt['configure'],
            "input_type": opt['input_type'],
            "extended_fields": opt['extended_fields'],
            "shareable": opt['shareable'],
            "sku": opt['sku'],
            "parent": body['parent'] if 'parent' in body else opt['parent'],
            "runs": opt['runs'],
            "linked": opt['linked'],
            "additional": body['additional'] if 'additional' in body else opt['additional'],
        }

        opt.modify(**data_to_store)

        return jsonify({
            "message": "Option has been updated successfully.",
            "status": 200
        })

    def update_media(self, tenant, option, body):
        supplier_boops = SupplierBoops.objects(tenant_id=tenant).aggregate([
            {
                "$match": {
                    "boops.ops.slug": option
                }
            }
        ])
        for boops in supplier_boops:
            boop_id = boops.get('_id')
            del boops['_id']
            for box in boops['boops']:
                for opt in box['ops']:
                    if opt['slug'] == option:
                        opt['media'] = body['media']
            update = SupplierBoops.objects(tenant_id=tenant, id=boop_id).first()
            if update:
                update.modify(**boops)
