import json

from flask import request, jsonify
from models.supplierCategory import SupplierCategory
from models.supplierBoops import SupplierBoops
from models.supplierBox import SupplierBox
from models.box import Box
from models.option import Option
from models.supplierOption import SupplierOption
from models.supplierMachine import SupplierMachine
from bson.json_util import dumps
from bson import ObjectId
from flask_restful import Resource
import requests

########################################
############ create class ##############
########################################
from slugify import slugify


class SupplierCategoryObjectApi(Resource):

    def get(self, supplier_id, slug):
        supplier_category = supplier_category = SupplierCategory.objects(slug=slug, tenant_id=supplier_id).first()
        if not supplier_category:
            return {
                "category": [],
                "message": "Supplier dose not have this Category.",
                "status": 404
            }, 200
        return jsonify(supplier_category)


#######
class SupplierCategoryBoopsApi(Resource):
    def get(self, supplier_id, slug):
        supplier_category = SupplierCategory.objects(slug=slug, tenant_id=supplier_id).first()

        if not supplier_category:
            return {
                "boops": [],
                "message": "Supplier dose not have this Category.",
                "status": 404
            }, 200

        boops = SupplierBoops.objects(slug=slug, tenant_id=supplier_id).first()

        if boops:
            for p in boops:
                if p == 'boops':
                    for b in boops[p]:
                        # update box
                        box_index = boops[p].index(b)
                        for o in b['ops']:
                            # update option
                            op_index = boops[p][boops[p].index(b)]['ops'].index(o)
                            boops[p][box_index]['ops'][op_index]['media'] = SupplierOption.objects(
                                id=o['id']).first().media
        else:
            boops = dumps({
                "supplier_category": supplier_category.id,
                "divided": False,
            })

        extras = {
            'calculation_method': supplier_category['calculation_method'],
            'dlv_days': supplier_category['dlv_days'],
            'has_products': supplier_category['has_products'],
            'price_build': supplier_category['price_build'],
            'printing_method': supplier_category['printing_method'],
            'production_days': supplier_category['production_days'],
            'start_cost': supplier_category['start_cost'],
            'description': supplier_category['description'],
            'shareable': supplier_category['shareable'],
            'additional': supplier_category['additional'],
            'media': supplier_category['media'],
        }

        return jsonify(boops, dumps(extras), supplier_category)

    def post(self, supplier_id, slug):
        # TODO We can Refactor -> move it on own function for Reusable Update & Create
        body = request.get_json(force=True)
        # check if SupplierCategory exists
        supplier_category = SupplierCategory.objects(slug=slug, tenant_id=supplier_id).first()

        if not supplier_category:
            return {
                "message": "Supplier dose not have this Category.",
                "status": 422
            }, 422
        else:
            boops = []
            for box in body['boops']:
                # if we don't have Box for this Category Create one
                if not SupplierBox.objects(slug=box['slug'], tenant_id=supplier_id).count():
                    systemBox = Box.objects(slug=box['slug']).first()
                    boxToSave = {
                        "tenant_id": supplier_id,
                        "tenant_name": body['tenant_name'],
                        "name": systemBox['name'] if systemBox else box['name'],
                        # FIXME Run Similarity or Not
                        "display_name": box['display_name'],
                        "description": box['description'] if "description" in box else "",
                        "slug": box['slug'],
                        "media": box['media'] if 'media' in box else [],
                        "appendage": box['appendage'],
                        "calculation_type": box['calculation_type'],
                        "sqm": box['sqm'],
                        "input_type": box['input_type'],
                        "published": True
                    }
                    # if systemBox:
                    #     return systemBox.id
                    supplierbox = SupplierBox(**boxToSave).save()
                # else get this Box from document
                else:
                    supplierbox = SupplierBox.objects(slug=box['slug'], tenant_id=supplier_id).first()
                    systemBox = Box.objects(slug=box['slug']).first()
                boxObj = {
                    'id': supplierbox.id,
                    "name": box['name'],
                    "display_name": box['display_name'],
                    "slug": box['slug'],
                    "description": box['description'] if "description" in box else "",
                    "sqm": box['sqm'] if "sqm" in box else "",
                    "appendage": box['appendage'] if "appendage" in box else "",
                    "calculation_type": box['calculation_type'] if "calculation_type" in box else "",
                    "media": box['media'] if "media" in box else [],
                    "input_type": box['input_type'],
                    "linked": systemBox.id if systemBox else None,
                    "published": True,
                    'ops': []
                }

                for option in box['ops']:
                    if not SupplierOption.objects(slug=option['slug'], tenant_id=supplier_id).count():
                        systemOption = Option.objects(slug=option['slug']).first()
                        optionToSave = {
                            "tenant_id": supplier_id,
                            "tenant_name": body['tenant_name'],
                            "name": systemOption['name'] if systemOption else option['name'],
                            "display_name": option['display_name'],
                            "slug": option['slug'],
                            "description": option['description'] if "description" in option else "",

                            "dimension": option['dimension'] if "dimension" in option else '2d',
                            "dynamic": option['dynamic'] if "dynamic" in option else False,
                            "unit": option['unit'] if 'unit' in option else "cm",
                            "media": option['media'] if "media" in option else [],
                            "width": option['width'] if "width" in option else 0,
                            "maximum_width": option['maximum_width'] if 'maximum_width' in option else 0,
                            "minimum_width": option['minimum_width'] if 'minimum_width' in option else 0,
                            "height": option['height'] if "height" in option else 0,
                            "maximum_height": option['maximum_height'] if 'maximum_height' in option else 0,
                            "minimum_height": option['minimum_height'] if 'minimum_height' in option else 0,
                            "length": option['length'] if 'length' in option else 0,
                            "maximum_length": option['maximum_length'] if 'maximum_length' in option else 0,
                            "minimum_length": option['minimum_length'] if 'minimum_length' in option else 0,
                            "start_cost": option['start_cost'] if 'start_cost' in option else 0,
                            "rpm": option['rpm'] if 'rpm' in option else 0,

                            "incremental_by": option['incremental_by'] if 'incremental_by' in option else 0,
                            "information": option['information'] if 'information' in option else "",
                            "extended_fields": option['extended_fields'] if 'extended_fields' in option else [],
                            "input_type": option['input_type'] if 'input_type' in option else 'radio',
                        }
                        if systemOption:
                            optionToSave['linked'] = systemOption.id
                        supplierOption = SupplierOption(**optionToSave).save()
                        if not systemOption:
                            # run similarty
                            options = [{
                                "name": body['name'],
                                "sku": ""}]
                            url = 'http://assortments:5000/similarity/options'
                            obj = {'tenant': supplier_id, 'tenant_name': body["tenant_name"], 'options': options}

                            header = {"Content-type": "application/json"}

                            requests.post(url, json=obj, headers=header)
                    else:
                        supplierOption = SupplierOption.objects(slug=option['slug'], tenant_id=supplier_id).first()
                        systemOption = Option.objects(slug=option['slug']).first()

                    boxObj['ops'].append({
                        'id': supplierOption.id,
                        "name": option['name'],
                        "display_name": option['display_name'],
                        "slug": option['slug'],
                        "description": option['description'] if "description" in option else "",
                        "dimension": option['dimension'] if "dimension" in option else '2d',
                        "dynamic": option['dynamic'] if "dynamic" in option else False,
                        "unit": option['unit'] if 'unit' in option else "cm",
                        "media": option['media'] if "media" in option else [],
                        "width": option['width'] if "width" in option else 0,
                        "maximum_width": option['maximum_width'] if 'maximum_width' in option else 0,
                        "minimum_width": option['minimum_width'] if 'minimum_width' in option else 0,
                        "height": option['height'] if "height" in option else 0,
                        "maximum_height": option['maximum_height'] if 'maximum_height' in option else 0,
                        "minimum_height": option['minimum_height'] if 'minimum_height' in option else 0,
                        "length": option['length'] if 'length' in option else 0,
                        "maximum_length": option['maximum_length'] if 'maximum_length' in option else 0,
                        "minimum_length": option['minimum_length'] if 'minimum_length' in option else 0,
                        "start_cost": option['start_cost'] if 'start_cost' in option else 0,
                        "rpm": option['rpm'] if 'rpm' in option else 0,
                        "incremental_by": option['incremental_by'] if 'incremental_by' in option else "",
                        "information": option['information'] if 'information' in option else "",
                        'input_type': option['input_type'] if 'input_type' in option else "",
                        "linked": systemOption.id if systemOption else None,
                        'excludes': option['excludes'] if 'excludes' in option else [],
                    })

                boops.append(boxObj)

            existingBoops = SupplierBoops.objects(supplier_category=supplier_category.id, tenant_id=supplier_id).first()

            if not existingBoops:
                SupplierBoops(**{
                    "tenant_id": supplier_id,
                    "tenant_name": body['tenant_name'],
                    "supplier_category": supplier_category.id,
                    "linked": supplier_category.linked,
                    "name": supplier_category.name,
                    "display_name": supplier_category.display_name,
                    "system_key": supplier_category.system_key,
                    "slug": supplier_category.slug,
                    "boops": boops
                }).save()

                supplier_category.modify(**{
                    "has_manifest": True,
                })

                return {'data': body, 'status': 201}

            return {
                "message": "Supplier boops already  exists ",
                "status": 422
            }, 422

    def put(self, supplier_id, slug):
        # check if SupplierCategory exists
        supplier_category = SupplierCategory.objects(slug=slug, tenant_id=supplier_id).first()

        if not supplier_category:
            return {
                "message": "Supplier dose not have this Category.",
                "status": 422
            }, 200
        else:
            body = request.get_json(force=True)
            boops = []
            for box in body['boops']:
                if not SupplierBox.objects(slug=box['slug'], tenant_id=supplier_id).count():
                    system_box = Box.objects(slug=box['slug']).first()
                    if system_box:
                        SupplierBox(**{
                            "tenant_id": supplier_id,
                            "tenant_name": body['tenant_name'],
                            "name": system_box['name'],
                            "display_name": box['display_name'],
                            "system_key": box.get('system_key', slugify(box['name'], to_lower=True)),
                            "description": box.get('description', ""),
                            "slug": box['slug'],
                            "media": box.get('media', []),
                            "sqm": box.get('sqm', False),
                            "appendage": box.get('sqm', False),
                            "calculation_type": box.get('calculation_type', ''),
                            "input_type": box['input_type'],
                            "linked": system_box.id,
                            "published": True
                        }).save()
                    else:
                        SupplierBox(**{
                            "tenant_id": supplier_id,
                            "tenant_name": body['tenant_name'],
                            "name": box['name'],
                            "system_key": box.get('system_key', slugify(box['name'], to_lower=True)),
                            "description": box.get('description', ""),
                            "display_name": box['display_name'],
                            "slug": box['slug'],
                            "media": box.get('media', []),
                            "sqm": box.get('sqm', False),
                            "appendage": box.get('appendage', False),
                            "calculation_type": box.get('calculation_type', ''),
                            "input_type": box['input_type'],
                            "linked": "",
                            "published": True
                        }).save()
                ref_obj_box = ObjectId(box['ref_box']) if ("ref_box" in box) and (box['ref_box'] is not None) else ""
                supplier_box = SupplierBox.objects(slug=box['slug'], tenant_id=supplier_id).first()
                box_obj = {
                    'id': supplier_box.id,
                    "name": box['name'],
                    "display_name": box['display_name'],
                    "system_key": box.get('system_key', slugify(box['name'], to_lower=True)),
                    "slug": box['slug'],
                    "description": box['description'] if "description" in box else "",
                    "ref_box": ref_obj_box if ("ref_box") in box else "",
                    "sqm": box.get('sqm', False),
                    "appendage": box.get('appendage', False),
                    "calculation_type": box.get('calculation_type', False),
                    "media": box.get('media', []),
                    "input_type": box['input_type'],
                    "linked": self.linked(supplier_box.linked),
                    "published": True,
                    "divider": box.get('divider'),
                    'ops': []
                }
                if "ops" not in box:
                    return {
                        "message": "Box {} does not have any Options".format(box['name']),
                        "status": 404
                    }, 200

                for option in box['ops']:
                    if not SupplierOption.objects(slug=option['slug'], tenant_id=supplier_id).count():
                        systemOption = Option.objects(slug=option['slug']).first()
                        if systemOption:
                            SupplierOption(**{
                                "tenant_id": supplier_id,
                                "tenant_name": body['tenant_name'],
                                "name": systemOption['name'],
                                "display_name": option['display_name'],
                                "system_key": option.get('system_key', slugify(option['name'], to_lower=True)),
                                "slug": option['slug'],
                                "description": option['description'] if "description" in option else "",
                                "unit": option.get('system_key', "cm"),
                                "width": option.get('width', systemOption['width']),
                                "maximum_width": option.get('maximum_width', systemOption['maximum_width']),
                                "minimum_width": option.get('minimum_width', systemOption['minimum_width']),
                                "height": option.get('height', systemOption['height']),
                                "maximum_height": option.get('maximum_height', systemOption['maximum_height']),
                                "minimum_height": option.get('minimum_height', systemOption['minimum_height']),
                                "length": option.get('minimum_width', 0),
                                "maximum_length": option.get('maximum_length', systemOption['maximum_length']),
                                "minimum_length": option.get('minimum_length', systemOption['minimum_length']),

                                "dimension": option.get('dimension', systemOption['dimension']),

                                "incremental_by": option.get('incremental_by', 0),
                                "rpm": option['rpm'] if 'rpm' in option else 0,
                                "information": option['information'] if 'information' in option else "",
                                "dynamic": option['dynamic'] if 'dynamic' in option else False,
                                "input_type": option.get('input_type', systemOption['input_type']),
                                "linked": systemOption.id
                            }).save()
                        else:
                            SupplierOption(**{
                                "tenant_id": supplier_id,
                                "tenant_name": body['tenant_name'],
                                "name": systemOption['name'],
                                "display_name": option['display_name'],
                                "system_key": option.get('system_key', slugify(option['name'], to_lower=True)),
                                "slug": option['slug'],
                                "description": option['description'] if "description" in option else "",
                                "unit": option.get('system_key', "cm"),
                                "width": option.get('width', systemOption['width']),
                                "maximum_width": option.get('maximum_width', systemOption['maximum_width']),
                                "minimum_width": option.get('minimum_width', systemOption['minimum_width']),
                                "height": option.get('height', systemOption['height']),
                                "maximum_height": option.get('maximum_height', systemOption['maximum_height']),
                                "minimum_height": option.get('minimum_height', systemOption['minimum_height']),
                                "length": option.get('minimum_width', 0),
                                "maximum_length": option.get('maximum_length', systemOption['maximum_length']),
                                "minimum_length": option.get('minimum_length', systemOption['minimum_length']),

                                "dimension": option.get('dimension', systemOption['dimension']),

                                "incremental_by": option.get('incremental_by', 0),
                                "rpm": option['rpm'] if 'rpm' in option else 0,
                                "information": option['information'] if 'information' in option else "",
                                "dynamic": option['dynamic'] if 'dynamic' in option else False,
                                "input_type": option['input_type'],
                                "linked": ""
                            }).save()
                    ref_obj = ObjectId(box['ref_option']) if ("ref_option" in box) and (
                            box['ref_option'] is not None) else ""
                    supplier_option = SupplierOption.objects(slug=option['slug'], tenant_id=supplier_id).first()
                    box_obj['ops'].append({
                        'id': supplier_option.id,
                        "ref_option": ObjectId(option.get('ref_option')) if option.get(
                            'ref_option') is not None else None,
                        "name": option['name'],
                        "display_name": option['display_name'],
                        "system_key": option.get('system_key', slugify(option['name'], to_lower=True)),
                        "slug": option['slug'],
                        "description": option['description'] if "description" in option else "",
                        "media": option['media'] if "media" in option else [],

                        "dimension": option['dimension'] if "dimension" in option else '2d',
                        "dynamic": option['dynamic'] if "dynamic" in option else False,
                        "unit": option['unit'] if 'unit' in option else "cm",
                        "width": option['width'] if "width" in option else 0,
                        "maximum_width": option['maximum_width'] if 'maximum_width' in option else 0,
                        "minimum_width": option['minimum_width'] if 'minimum_width' in option else 0,
                        "height": option['height'] if "height" in option else 0,
                        "maximum_height": option['maximum_height'] if 'maximum_height' in option else 0,
                        "minimum_height": option['minimum_height'] if 'minimum_height' in option else 0,
                        "length": option['length'] if 'length' in option else 0,
                        "maximum_length": option['maximum_length'] if 'maximum_length' in option else 0,
                        "minimum_length": option['minimum_length'] if 'minimum_length' in option else 0,
                        "start_cost": option['start_cost'] if 'start_cost' in option else 0,
                        "rpm": option['rpm'] if 'rpm' in option else 0,

                        "information": option['information'] if 'information' in option else "",
                        'input_type': option['input_type'] if 'input_type' in option else "",
                        "linked": self.linked(supplier_option.linked),
                        'excludes': option['excludes'] if 'excludes' in option else "",
                    })

                boops.append(box_obj)

            existingBoops = SupplierBoops.objects(supplier_category=supplier_category.id, tenant_id=supplier_id).first()
            if not existingBoops:
                res = SupplierBoops(**{
                    "tenant_id": supplier_id,
                    "tenant_name": body['tenant_name'],
                    "supplier_category": supplier_category.id,
                    "linked": supplier_category.linked,
                    "name": supplier_category.name,
                    "system_key": supplier_category.system_key,
                    "display_name": supplier_category.display_name,
                    "slug": supplier_category.slug,
                    "divided": body['divided'] if 'divided' in body else False,
                    "boops": boops
                }).save()
                supplier_category.modify(**{
                    "has_manifest": True,
                })
            else:
                existingBoops.update(
                    boops=boops,
                    divided=body['divided'] if 'divided' in body else False
                )
                supplier_category.modify(**{
                    "has_manifest": True,
                })
                res = SupplierBoops.objects(supplier_category=supplier_category.id, tenant_id=supplier_id).first()

            return jsonify(data=res, message="boops updated with success", code=200)

    # fix old one if exists
    def linked(self, linked):
        if linked:
            link_id = json.loads(dumps(linked['id']))
            return ObjectId(link_id['$oid'])
        return ""


class OpenProductBoopsApi(Resource):
    def put(self, supplier_id, slug):
        body = request.get_json(force=True)

        # check if SupplierCategory exists
        supplier_category = SupplierCategory.objects(slug=slug, tenant_id=supplier_id).first()
        if not supplier_category:
            return {
                "message": "Supplier dose not have this Category.",
                "status": 422
            }, 200

        supplier_boops = SupplierBoops.objects(supplier_category=supplier_category.id, tenant_id=supplier_id).first()

        if supplier_boops:

            supplier_boops.update(
                boops=self.get_open_product_boops(supplier_id, body, supplier_boops),
                divided=body['divided'] if 'divided' in body else False
            )

            supplier_category.modify(**{"has_manifest": True})
            res = SupplierBoops.objects(supplier_category=supplier_category.id, tenant_id=supplier_id).first()

            return jsonify(data=res, message="boops updated with success", code=200)

        else:
            res = SupplierBoops(**{
                "tenant_id": supplier_id,
                "tenant_name": body['tenant_name'],
                "supplier_category": supplier_category.id,
                "linked": supplier_category.linked,
                "name": supplier_category.name,
                "system_key": supplier_category.system_key,
                "display_name": supplier_category.display_name,
                "slug": supplier_category.slug,
                "divided": body['divided'] if 'divided' in body else False,
                "boops": self.create_open_product_boops(supplier_id, body)
            }).save()
            supplier_category.modify(**{"has_manifest": True})
            return jsonify(data=res, message="boops updated with success", code=200)

    def create_open_product_boops(self, supplier_id, body):
        boops = []
        for bodyBoop in body['boops']:
            box = self.create_box_and_options(supplier_id, bodyBoop, body)
            boops.append(box)
        return boops

    def get_open_product_boops(self, supplier_id, body, supplierBoops):
        # 1) if not box add the box
        # 2) if box exists check the options
        # 3) if options not in the box
        boops = {}
        for supplierBoop in supplierBoops.boops:
            boops.update({supplierBoop['slug']: supplierBoop})

        for bodyBoop in body['boops']:
            request_boop_slug = bodyBoop['slug']
            boop = boops.get(request_boop_slug)
            if boop is None:  # box does not exists in the boops so create it with options and append to the boops
                # manifest
                box = self.create_box_and_options(supplier_id, bodyBoop, body)  # create box with its options
                boops.update({request_boop_slug: box})
            else:
                for bodyOption in bodyBoop['ops']:  # attach option if option in the body not exists on the boops
                    # manifest options
                    body_option_slug = bodyOption['slug']
                    supplier_option = [o for o in boop['ops'] if o['slug'] == body_option_slug]
                    if len(supplier_option) == 0:  # option does not exists in the box should create the option and
                        # update the box with the new option object
                        created_option = self.create_option(supplier_id, bodyOption, body)
                        boop['ops'].append(created_option)
                        boops.update({boop['slug']: boop})
        return list(boops.values())

    def create_box_and_options(self, supplier_id, box, body):
        if not SupplierBox.objects(slug=box['slug'], tenant_id=supplier_id).count():
            system_box = Box.objects(slug=box['slug']).first()
            if system_box:
                SupplierBox(**{
                    "tenant_id": supplier_id,
                    "tenant_name": body['tenant_name'],
                    "name": system_box['name'],
                    "display_name": box['display_name'],
                    "system_key": box.get('system_key', slugify(box['name'], to_lower=True)),
                    "description": box.get('description', ""),
                    "slug": box['slug'],
                    "media": box.get('media', []),
                    "sqm": box.get('sqm', False),
                    "appendage": box.get('sqm', False),
                    "calculation_type": box.get('calculation_type', ''),
                    "input_type": box['input_type'],
                    "linked": system_box.id,
                    "published": True
                }).save()
            else:
                SupplierBox(**{
                    "tenant_id": supplier_id,
                    "tenant_name": body['tenant_name'],
                    "name": box['name'],
                    "system_key": box.get('system_key', slugify(box['name'], to_lower=True)),
                    "description": box.get('description', ""),
                    "display_name": box['display_name'],
                    "slug": box['slug'],
                    "media": box.get('media', []),
                    "sqm": box.get('sqm', False),
                    "appendage": box.get('appendage', False),
                    "calculation_type": box.get('calculation_type', ''),
                    "input_type": box['input_type'],
                    "linked": "",
                    "published": True
                }).save()
        ref_obj_box = ObjectId(box['ref_box']) if ("ref_box" in box) and (box['ref_box'] is not None) else ""
        supplier_box = SupplierBox.objects(slug=box['slug'], tenant_id=supplier_id).first()
        box_obj = {
            'id': supplier_box.id,
            "name": box['name'],
            "display_name": box['display_name'],
            "system_key": box.get('system_key', slugify(box['name'], to_lower=True)),
            "slug": box['slug'],
            "description": box['description'] if "description" in box else "",
            "ref_box": ref_obj_box if ("ref_box") in box else "",
            "sqm": box.get('sqm', False),
            "appendage": box.get('appendage', False),
            "calculation_type": box.get('calculation_type', False),
            "media": box.get('media', []),
            "input_type": box['input_type'],
            "linked": supplier_box.linked['id'],
            "published": True,
            "divider": box.get('divider'),
            'ops': []
        }

        for option in box['ops']:
            box_obj['ops'].append(
                self.create_option(supplier_id, option, body)
            )
        return box_obj

    def create_option(self, supplier_id, option, body):
        if not SupplierOption.objects(slug=option['slug'], tenant_id=supplier_id).count():
            systemOption = Option.objects(slug=option['slug']).first()
            if systemOption:
                SupplierOption(**{
                    "tenant_id": supplier_id,
                    "tenant_name": body['tenant_name'],
                    "name": systemOption['name'],
                    "display_name": option['display_name'],
                    "system_key": option.get('system_key', slugify(option['name'], to_lower=True)),
                    "slug": option['slug'],
                    "description": option['description'] if "description" in option else "",
                    "unit": option.get('system_key', "cm"),
                    "width": option.get('width', systemOption['width']),
                    "maximum_width": option.get('maximum_width', systemOption['maximum_width']),
                    "minimum_width": option.get('minimum_width', systemOption['minimum_width']),
                    "height": option.get('height', systemOption['height']),
                    "maximum_height": option.get('maximum_height', systemOption['maximum_height']),
                    "minimum_height": option.get('minimum_height', systemOption['minimum_height']),
                    "length": option.get('minimum_width', 0),
                    "maximum_length": option.get('maximum_length', systemOption['maximum_length']),
                    "minimum_length": option.get('minimum_length', systemOption['minimum_length']),

                    "dimension": option.get('dimension', systemOption['dimension']),

                    "incremental_by": option.get('incremental_by', 0),
                    "rpm": option['rpm'] if 'rpm' in option else 0,
                    "information": option['information'] if 'information' in option else "",
                    "dynamic": option['dynamic'] if 'dynamic' in option else False,
                    "input_type": option.get('input_type', systemOption['input_type']),
                    "linked": systemOption.id
                }).save()
            else:
                SupplierOption(**{
                    "tenant_id": supplier_id,
                    "tenant_name": body['tenant_name'],
                    "name": systemOption['name'],
                    "display_name": option['display_name'],
                    "system_key": option.get('system_key', slugify(option['name'], to_lower=True)),
                    "slug": option['slug'],
                    "description": option['description'] if "description" in option else "",
                    "unit": option.get('system_key', "cm"),
                    "width": option.get('width', systemOption['width']),
                    "maximum_width": option.get('maximum_width', systemOption['maximum_width']),
                    "minimum_width": option.get('minimum_width', systemOption['minimum_width']),
                    "height": option.get('height', systemOption['height']),
                    "maximum_height": option.get('maximum_height', systemOption['maximum_height']),
                    "minimum_height": option.get('minimum_height', systemOption['minimum_height']),
                    "length": option.get('minimum_width', 0),
                    "maximum_length": option.get('maximum_length', systemOption['maximum_length']),
                    "minimum_length": option.get('minimum_length', systemOption['minimum_length']),

                    "dimension": option.get('dimension', systemOption['dimension']),

                    "incremental_by": option.get('incremental_by', 0),
                    "rpm": option['rpm'] if 'rpm' in option else 0,
                    "information": option['information'] if 'information' in option else "",
                    "dynamic": option['dynamic'] if 'dynamic' in option else False,
                    "input_type": option['input_type'],
                    "linked": ""
                }).save()

        supplier_option = SupplierOption.objects(slug=option['slug'], tenant_id=supplier_id).first()

        return {
            'id': supplier_option.id,
            "ref_option": ObjectId(option.get('ref_option')) if option.get(
                'ref_option') is not None else None,
            "name": option['name'],
            "display_name": option['display_name'],
            "system_key": option.get('system_key', slugify(option['name'], to_lower=True)),
            "slug": option['slug'],
            "description": option['description'] if "description" in option else "",
            "media": option['media'] if "media" in option else [],

            "dimension": option['dimension'] if "dimension" in option else '2d',
            "dynamic": option['dynamic'] if "dynamic" in option else False,
            "unit": option['unit'] if 'unit' in option else "cm",
            "width": option['width'] if "width" in option else 0,
            "maximum_width": option['maximum_width'] if 'maximum_width' in option else 0,
            "minimum_width": option['minimum_width'] if 'minimum_width' in option else 0,
            "height": option['height'] if "height" in option else 0,
            "maximum_height": option['maximum_height'] if 'maximum_height' in option else 0,
            "minimum_height": option['minimum_height'] if 'minimum_height' in option else 0,
            "length": option['length'] if 'length' in option else 0,
            "maximum_length": option['maximum_length'] if 'maximum_length' in option else 0,
            "minimum_length": option['minimum_length'] if 'minimum_length' in option else 0,
            "start_cost": option['start_cost'] if 'start_cost' in option else 0,
            "rpm": option['rpm'] if 'rpm' in option else 0,

            "information": option['information'] if 'information' in option else "",
            'input_type': option['input_type'] if 'input_type' in option else "",
            "linked": ObjectId(supplier_option.linked['id']),
            'excludes': option['excludes'] if 'excludes' in option else "",
        }
