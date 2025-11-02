from flask import Response, request, jsonify
from models.category import Category
from models.categoryBoxOption import CategoryBoxOption
from models.box import Box
from models.option import Option
from models.supplierBoops import SupplierBoops
from models.matchedOption import MatchedOption
from models.unmatchedOption import UnmatchedOption
from models.supplierOption import SupplierOption
from flask_restful import Resource
import json
import math
from bson.json_util import dumps
from bson import ObjectId
from slugify import slugify, Slugify, UniqueSlugify
import requests


##############################
#   handel index and store  #
#############################
class CategoryBoxOptionsApi(Resource):
    def get(self, cat_slug, box_slug):

        categoryData = Category.objects(slug=cat_slug).first()
        boxData = Box.objects(slug=box_slug).first()
        if not json.loads(dumps(categoryData)) or not json.loads(dumps(boxData)):
            return {
                "message": 'The requested URL was not found on the server. If you entered the URL manually please check your spelling and try again.',
                "status": 404,
            }, 404
        else:
            optionRelation = CategoryBoxOption.objects(category=categoryData.id, box=boxData.id).aggregate([
                {
                    "$lookup": {
                        "from": "options",  # Tag collection database name
                        "foreignField": "_id",  # Primary key of the Tag collection
                        "localField": "option",  # Reference field
                        "as": "option",
                    },
                }
            ])

            optionRelation = json.loads(dumps(optionRelation))
            items = optionRelation

            options = []
            for optRelation in items:
                if 'option' in optRelation:
                    option = optRelation['option']
                    #                 return json.loads(dumps(option[0]['slug']))
                    option = Option.objects(slug=option[0]['slug']).aggregate([
                        {
                            "$lookup": {
                                "from": "supplier_options",  # Tag collection database name
                                "foreignField": "option",  # Primary key of the Tag collection
                                "localField": "_id",  # Reference field
                                "as": "suppliers",
                            },
                        },
                        {
                            "$lookup": {
                                "from": "matched_options",  # Tag collection database name
                                "foreignField": "option",  # Primary key of the Tag collection
                                "localField": "_id",  # Reference field
                                "as": "matches",
                            }
                        },
                        {
                            "$lookup": {
                                "from": "options",  # Tag collection database name
                                "foreignField": "children",  # Primary key of the Tag collection
                                "localField": "_id",  # Reference field
                                "as": "children",
                            }
                        },
                        {
                            "$project": {"_id": 0, "children.children": 0}
                        }
                    ])
                    options.append(json.loads(dumps(*option)))

            return {
                "data": options
            }, 200

    def post(self, cat_slug, box_slug):
        body = request.form.to_dict(flat=True)
        #         body = request.get_json()
        category = Category.objects(slug=cat_slug).first()

        box = Box.objects(slug=box_slug).first()

        option = Option.objects(slug=slugify(body['name'], to_lower=True)).first()
        if not option:
            option = Option(**body).save()

        if CategoryBoxOption.objects(category=category, box=box, option=option).count() == 0:
            op = CategoryBoxOption(**{
                "category": category,
                "box": box,
                "option": option
            }).save()

        return jsonify({
            "message": "Option created successfully",
            "status": 201,
            "data": option
        })


class SupplierCategoryBoxOptionsApi(Resource):
    def post(self, supplier_id, cat_id):
        body = request.get_json()
        # return linked
        supplier_option = SupplierOption.objects(tenant_id=supplier_id,
                                                 slug=slugify(body['system_key'], to_lower=True)).first()

        if supplier_option:
            return jsonify(supplier_option)
        else:
            dataToStore = {
                "sort": body['sort'] if 'sort' in body else 0,
                "tenant_name": body["tenant_name"],
                "tenant_id": supplier_id,
                "name": body['system_key'],  # option.name,
                "display_name": body['display_name'] if 'display_name' in body else [],
                "slug": slugify(body['system_key'], to_lower=True),
                "system_key": body['system_key'] if 'system_key' in body else '',
                "description": body['description'] if 'description' in body else "",
                "information": body['information'] if 'information' in body else "",
                "media": body['media'] if 'media' in body else [],
                "published": bool(body['published']) if 'published' in body else False,
                "input_type": body['input_type'] if 'input_type' in body else "",
                "extended_fields": body['extended_fields'] if 'extended_fields' in body else [],
                "shareable": bool(body['shareable']) if 'shareable' in body else False,
                "sku": body['sku'] if 'sku' in body else "",
                "parent": body['parent'] if 'parent' in body else False,
                "configure": [{
                    'category_id': ObjectId(cat_id),
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
                }],
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

                "runs": [{
                    'category_id': ObjectId(cat_id),
                    "start_cost": body['start_cost'] if 'start_cost' in body else 0,
                    'runs': body['runs'],
                }] if 'runs' in body else [],
                "additional": body['additional'] if 'additional' in body else {}
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
                    }, 422

            supplier_option = SupplierOption(**dataToStore).save()
            options = [{
                "name": body['system_key'],
                "sku": ""}]
            url = 'http://assortments:5000/similarity/options'
            obj = {'tenant': supplier_id, 'tenant_name': body["tenant_name"], 'options': options}

            header = {"Content-type": "application/json"}
            res = requests.post(url, json=obj, headers=header)
            return jsonify(supplier_option)


class SupplierCategoryBoxOptionApi(Resource):
    def get(self, supplier_id, cat_id, option_id):
        option = SupplierOption.objects(tenant_id=supplier_id, id=ObjectId(option_id)).get()

        if not option:
            return {
                'message': 'Option not found',
                'status': 404
            }, 200
        runs = None
        configure = None
        for run in option.runs:
            if run.get('category_id') == ObjectId(cat_id):
                runs = run.get('runs')
        option.runs = runs if not None else []

        for config in option.configure:
            if config.get('category_id') == ObjectId(cat_id):
                configure = config.get('configure')
        option.configure = configure if not None else []

        return jsonify({
            "data": option,
            "status": 200,
            "message": ""
        })

    def put(self, supplier_id, cat_id, option_id):
        body = request.get_json()

        supplier_option = SupplierOption.objects(tenant_id=supplier_id,
                                                 id=ObjectId(option_id)).first()
        if supplier_option is None:
            return {
                "data": None,
                "message": "Option not found",
                "status": 404
            }, 404

        # Helper function to get value with fallback
        def get_value(key, default=None):
            return body.get(key, getattr(supplier_option, key, default))

        # Get existing runs and configure arrays
        runs = list(getattr(supplier_option, 'runs', []))
        configure = list(getattr(supplier_option, 'configure', []))

        # Update category-specific runs (remove existing + add new)
        runs = [run for run in runs if run.get('category_id') != ObjectId(cat_id)]
        runs.append({
            'category_id': ObjectId(cat_id),
            "start_cost": body.get('start_cost', 0),
            'runs': body.get('runs', []),
        })

        # Default configuration template
        default_config = {
            "incremental_by": 0,
            "dimension": "",
            "dynamic": False,
            "dynamic_keys": [],
            "start_on": 0,
            "end_on": 0,
            "generate": False,
            "dynamic_type": "",
            "unit": "cm",
            "width": 0,
            "maximum_width": 0,
            "minimum_width": 0,
            "height": 0,
            "maximum_height": 0,
            "minimum_height": 0,
            "length": 0,
            "maximum_length": 0,
            "minimum_length": 0,
            "start_cost": 0,
            "calculation_method": [],
        }

        # Update category-specific configuration (remove ALL existing + add new)
        existing_config = {}
        # Find the most recent configuration for this category (last one in the list)
        for config in configure:
            if config.get("category_id") == ObjectId(cat_id):
                existing_config = config.get("configure", {})

        # Remove ALL configurations for this category (handles duplicates)
        configure = [config for config in configure if config.get("category_id") != ObjectId(cat_id)]

        # Merge configurations: body > existing_config > default_config
        new_config = {
            key: body.get(key, existing_config.get(key, default_value))
            for key, default_value in default_config.items()
        }

        configure.append({
            "category_id": ObjectId(cat_id),
            "configure": new_config
        })

        # Build update data using helper function
        data_to_store = {
            "sort": get_value('sort', 0),
            "tenant_name": supplier_option['tenant_name'],
            "tenant_id": supplier_option['tenant_id'],
            "display_name": get_value('display_name'),
            "system_key": get_value('system_key'),
            "description": get_value('description'),
            "information": get_value('information'),
            "media": get_value('media'),
            "input_type": get_value('input_type'),
            "extended_fields": get_value('extended_fields'),
            "published": bool(body['published']) if 'published' in body else getattr(supplier_option, 'published',
                                                                                     False),
            "shareable": bool(body['shareable']) if 'shareable' in body else getattr(supplier_option, 'shareable',
                                                                                     False),
            "sku": get_value('sku'),
            "parent": get_value('parent'),
            "runs": runs,
            "sheet_runs": get_value('sheet_runs'),
            "configure": configure,
            "additional": get_value('additional'),
            "incremental_by": get_value('incremental_by'),
            "dimension": get_value('dimension'),
            "dynamic": get_value('dynamic'),
            "dynamic_keys": get_value('dynamic_keys'),
            "start_on": get_value('start_on'),
            "end_on": get_value('end_on'),
            "generate": get_value('generate'),
            "dynamic_type": get_value('dynamic_type'),
            "unit": get_value('unit'),
            "width": get_value('width'),
            "maximum_width": get_value('maximum_width'),
            "minimum_width": get_value('minimum_width'),
            "height": get_value('height'),
            "maximum_height": get_value('maximum_height'),
            "minimum_height": get_value('minimum_height'),
            "length": get_value('length'),
            "maximum_length": get_value('maximum_length'),
            "minimum_length": get_value('minimum_length'),
            "start_cost": get_value('start_cost'),
            "calculation_method": get_value('calculation_method')
        }

        # Handle linked option
        if body.get("linked"):
            option = Option.objects(id=body['linked']).first()
            if option:
                data_to_store['name'] = option.name
                data_to_store['linked'] = option
            else:
                data_to_store['linked'] = None
                options = [{
                    "name": body['system_key'],
                    "sku": ""
                }]
                url = 'http://assortments:5000/similarity/options'
                obj = {
                    'tenant': supplier_id,
                    'tenant_name': body["tenant_name"],
                    'options': options
                }
                header = {"Content-type": "application/json"}
                requests.post(url, json=obj, headers=header)

        supplier_option.modify(**data_to_store)

        supplier_option.reload()

        def option_to_ops(option):
            return {
                "id": option.id,
                "ref_option": None, # what's that
                "name": option.name,
                "display_name": option.display_name,
                "system_key": option.system_key,
                "slug": option.slug,
                "source_slug": option.source_slug,
                "description": option.description,
                "media": option.media,
                "dimension": option.dimension,
                "dynamic": option.dynamic,
                "sheet_runs": option.sheet_runs,
                "unit": option.unit,
                "width": option.width,
                "maximum_width": option.maximum_width,
                "minimum_width": option.minimum_width,
                "height": option.height,
                "maximum_height": option.maximum_height,
                "minimum_height": option.minimum_height,
                "length": option.length,
                "maximum_length": option.maximum_length,
                "minimum_length": option.minimum_length,
                "start_cost": option.start_cost,
                "rpm": option.rpm,
                "information": option.information,
                "input_type": option.input_type,
                "linked": ObjectId(option.linked.id) if option.linked else None,
                "dynamic_keys": option.dynamic_keys,
                "start_on": option.start_on,
                "end_on": option.end_on,
                "dynamic_type": option.dynamic_type,
                "generate": option.generate,
                "dynamic_object": getattr(option, "dynamic_object", None),
            }


        ops_data = option_to_ops(supplier_option)  

        existing_boops = SupplierBoops.objects(
            supplier_category=ObjectId(cat_id),
            boops__ops__id=ObjectId(option_id)
        ).only("boops__ops").first()
        if existing_boops:

            current_ops = None
            for boop in existing_boops.boops:
                for ops in boop.get("ops", []):   
                    if str(ops.get("id")) == str(option_id):  
                        current_ops = dict(ops)
                        break

            merged_ops = {**current_ops, **ops_data}

            SupplierBoops._get_collection().update_one(
                {
                    "supplier_category": ObjectId(cat_id)
                },
                {
                    "$set": {
                        "boops.$[b].ops.$[o]": merged_ops
                    }
                },
                array_filters=[
                    {"b.ops.id": ObjectId(option_id)},
                    {"o.id": ObjectId(option_id)}
                ]
            )


        return {
            "message": "Option has been updated successfully.",
            "status": 200,
            "data": None
        }


class CategoryBoxOptionApi(Resource):
    def get(self, cat_slug, box_slug, option_slug):
        option = Option.objects.get_or_404(slug=option_slug)
        return jsonify({
            "data": option,
            "status": 200,
            "message": ""
        })

    def put(self, cat_slug, box_slug, option_slug):
        body = request.form.to_dict(flat=True)
        #         body = request.get_json()
        body["slug"] = slugify(body["name"], to_lower=True)
        body = {k: True if v == "1" else False if v == "0" else v for k, v in body.items()}
        option = Option.objects(slug=option_slug).first()

        option.update(**body)
        return {
            "message": "box has been updated successfully.",
            "status": 200,
            "data": None
        }

    def delete(self, cat_slug, box_slug, option_slug):
        # check if category has relation
        category = Category.objects(slug=cat_slug).first()
        box = Box.objects(slug=box_slug).first()
        option = Option.objects(slug=option_slug).first()
        relation = CategoryBoxOption.objects(category=category, box=box, option=option)
        if relation:
            relation.delete()
            return {
                "data": None,
                "message": "Option has been deleted successfully",
                "status": 200
            }, 200
        else:
            return {
                "data": None,
                "message": "Option not found",
                "status": 404
            }, 404


########################################
############ attach class ##############
########################################
class AttachSupplierOptionApi(Resource):

    def __init__(self):
        self.option = None

    def _process_option_request(self, nature_obj, obj_key, slug, tenant_id):
        nature_obj = nature_obj.objects(slug=slug, tenant_id=tenant_id).first()
        if not nature_obj:
            return {"status": 422, "message": "Option not found",
                    "errors": [{"option": "Invalid box slug or tenant ID."}]}, 200

        changes = {obj_key: self.option}
        SupplierOption.objects(slug=slug, tenant_id=tenant_id).modify(**changes)
        nature_obj.delete()

        return {
            "status": 200,
            "data": {"tenant": tenant_id, "slug": slug},
            "message": "Data has been successfully updated",
        }, 200

    def post(self, slug):
        data = request.form.to_dict(flat=True)
        tenant_id, option_slug = data.get("tenant_id"), data.get("slug")
        self.option = Option.objects(slug=slug).first()

        if not self.option:
            return {"status": 422, "message": "Option not found", "errors": [{"box": "Invalid option slug."}]}, 200

        if data.get("type") == "matches":
            return self._process_option_request(MatchedOption, "linked", option_slug, tenant_id)
        elif data.get("type") == "unmatched":
            return self._process_option_request(UnmatchedOption, "linked", option_slug, tenant_id)
        elif data.get("type") == "suppliers":
            SupplierOption.objects(slug=option_slug, tenant_id=tenant_id).update(linked=self.option)
            return {
                "status": 200,
                "data": {"tenant": tenant_id, "slug": option_slug},
                "message": "Supplier option has been attached successfully to option.",
            }, 200
        else:
            return {
                "status": 422,
                "message": "Invalid type.",
                "errors": [{"type": ["The type key field is required or not found."]}],
            }, 422


########################################
############ detach class ##############
########################################
class DetachSupplierOptionApi(Resource):
    def post(self, slug):

        data = request.form.to_dict(flat=True)
        #         data = request.get_json()
        # get slug of category
        option = Option.objects(slug=slug).first()

        try:
            suppliers = SupplierOption.objects(slug=data["slug"], tenant_id=data["tenant_id"]).first()
            print(data, suppliers)
            sp = {
                "name": suppliers['name'],
                "tenant_id": suppliers['tenant_id'],
                "tenant_name": suppliers['tenant_name'],
                "sku": suppliers['sku'],
                "description": suppliers['description'],
                "published": suppliers['published']
            }

            UnmatchedOption(**sp).save()
            linked = {
                "linked": None
            }
            suppliers.modify(**linked)

            resJson = {
                "status": 200,
                "data": {
                    "tenant": data["tenant_id"],
                    "slug": data["slug"],
                },
                "message": "data has been detached successfully",
            }

            return resJson, 200
        except Exception as e:
            print(e)
            return {"message": "Option was not found 4639", "status": 404}, 404


class AttachOptionToBoxApi(Resource):

    def post(self, cat_slug, box_slug, option_slug):
        data = request.form.to_dict(flat=True)
        #         data = request.get_json()
        category = Category.objects(slug=cat_slug).first()
        box = Box.objects.get_or_404(slug=box_slug, categories=category)
        option = Option.objects(slug=option_slug).first()

        toBox = Box.objects(slug=data['slug']).first()
        if toBox:
            CategoryBoxOption.objects(category=category, box=box, option=option).update(**{
                "category": category,
                "box": toBox,
                "option": option
            })
            return {"message": "Option has been moved to new box.", "status": 200}, 200
        else:
            return {"message": "Box was not found", "status": 404}, 404
