from flask import Response, request, jsonify
from models.supplierOption import SupplierOption
from models.option import Option
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource, fields, marshal
from bson.json_util import dumps
from models.supplierBox import SupplierBox

import json
import datetime
import math
import requests


##############################
#   handel index and store  #
#############################
class SupplierBoxOptionsApi(Resource):
    def get(self, supplier, box):
        # return linked
        supplierbox = SupplierBox.objects(tenant_id=supplier, slug=slugify(box, to_lower=True)).first()
        if not supplierbox:
            return {
                       "data": None,
                       "message": "Box not exists.",
                       "status": 422
                   }, 200
        options = SupplierOption.objects(tenant_id=supplier, boxes=supplierbox)
        if not options:
            return {
                       "data": None,
                       "message": "No Options for this Box",
                       "status": 404
                   }, 200

        return {
            "data": json.loads(dumps(options))
        }, 200


    def post(self, supplier, box):
        # body = request.form.to_dict(flat=True)
        body = request.get_json()
        # return linked
        supplierbox = SupplierBox.objects(tenant_id=supplier, slug=slugify(box, to_lower=True)).first()
        if not supplierbox:
            return {
                       "data": None,
                       "message": "Box not exists.",
                       "status": 422
                   }, 422
        else:
            if SupplierOption.objects(tenant_id=supplier, slug=slugify(body['name'], to_lower=True)).count():
                return {
                           "data": None,
                           "message": "Option already exists.",
                           "status": 422
                       }, 422
            else:
                dataToStore = {
                    "tenant_id": supplier,
                    "tenant_name": body["tenant_name"],
                    "sku": body['sku'] if 'sku' in body else "",
                    "sort": body['sort'] if 'sort' in body else 0,
                    "media": body['media'] if 'media' in body else [],
                    "name": body['name'],  # option.name,
                    "system_key": body['system_key'],  # option.name,
                    "display_name": body['display_name'],
                    "slug": slugify(body['name'], to_lower=True),
                    "description": body['description'] if 'description' in body else "",
                    "unit": body['unit'] if 'unit' in body else "",
                    "width": body['width'] if 'width' in body else 0,
                    "height": body['height'] if 'height' in body else 0,
                    "maximum": body['maximum'] if 'maximum' in body else 0,
                    "minimum": body['minimum'] if 'minimum' in body else 0,
                    "incremental_by": body['incremental_by'] if 'incremental_by' in body else 0,
                    "extended_fields": body['extended_fields'] if 'extended_fields' in body else [],
                    "published": bool(body['published']) if 'published' in body else False,
                    "has_children": bool(body['has_children']) if 'has_children' in body else False,
                    "parent": bool(body['parent']) if 'parent' in body else False,
                    "input_type": body['input_type'] if 'input_type' in body else "",
                    "shareable": bool(body['shareable']) if 'shareable' in body else False,
                    "start_cost": body['start_cost'] if 'start_cost' in body else 0,
                    "calculation_method": body['calculation_method'] if 'calculation_method' in body else [],
                    "runs": body['runs'] if 'runs' in body else [],
                    "boxes": [supplierbox]
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
                    "name": body['name'],
                    "sku": ""}]
                url = 'http://assortments:5000/similarity/options'
                obj = {'tenant': supplier, 'tenant_name': body["tenant_name"], 'options': options}

                header = {"Content-type": "application/json"}
                # return json.dumps(options)
                res = requests.post(url, json=obj, headers=header)
                return jsonify(supplier_option)


class SupplierBoxOptionApi(Resource):
    def put(self, supplier, box, option):
        # update Method
        body = request.get_json()
        supplierbox = SupplierBox.objects(tenant_id=supplier, slug=slugify(box, to_lower=True)).first()
        if not supplierbox:
            return {
                       "data": None,
                       "message": "Box not exists.",
                       "status": 422
                   }, 422
        supplierOption = SupplierOption.objects(tenant_id=supplier, slug=slugify(option, to_lower=True)).first()
        if not supplierOption:
            return {
                       "data": None,
                       "message": "Option not exists.",
                       "status": 404
                   }, 404

        dataToStore = {
            "tenant_id": supplier,
            "tenant_name": body["tenant_name"],
            "sku": body['sku'] if 'sku' in body else supplierOption.sku,
            "sort": body['sort'] if 'sort' in body else supplierOption.sort,
            "name": supplierOption.name,  # option.name,
            "system_key": supplierOption.system_key,  # option.name,
            "media": body['media'] if 'media' in body else supplierOption.media,
            "display_name": body['display_name'],
            "slug": supplierOption.slug,
            "description": body['description'] if 'description' in body else "",
            "unit": body['unit'] if 'unit' in body else supplierOption.unit,
            "width": body['width'] if 'width' in body else supplierOption.width,
            "height": body['height'] if 'height' in body else supplierOption.height,
            "maximum": body['maximum'] if 'maximum' in body else supplierOption.maximum,
            "minimum": body['minimum'] if 'minimum' in body else supplierOption.minimum,
            "incremental_by": body['incremental_by'] if 'incremental_by' in body else supplierOption.incremental_by,
            "extended_fields": body['extended_fields'] if 'extended_fields' in body else supplierOption.extended_fields,
            "published": bool(body['published']) if 'published' in body else supplierOption.published,
            "has_children": bool(body['has_children']) if 'has_children' in body else supplierOption.has_children,
            "parent": bool(body['parent']) if 'parent' in body else supplierOption.parent,
            "input_type": body['input_type'] if 'input_type' in body else supplierOption.input_type,
            "shareable": bool(body['shareable']) if 'shareable' in body else supplierOption.shareable,
            "start_cost": body['start_cost'] if 'start_cost' in body else supplierOption.start_cost,
            "calculation_method": body[
                'calculation_method'] if 'calculation_method' in body else supplierOption.calculation_method,
            "runs": body['runs'] if 'runs' in body else supplierOption.runs,
            "boxes": [supplierbox]
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

        supplier_option = supplierOption.update(**dataToStore)
        supplierOption = SupplierOption.objects(tenant_id=supplier, slug=slugify(option, to_lower=True)).first()
        return jsonify(supplierOption)
