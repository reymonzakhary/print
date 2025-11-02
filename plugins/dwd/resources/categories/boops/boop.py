from flask import Response, request, jsonify
from flask.json import dump
from models.category import Category
from models.supplierCategory import SupplierCategory
from models.boops import Boops
from models.supplierBoops import SupplierBoops
from models.box import Box
from models.option import Option
from models.supplierBox import SupplierBox
from models.supplierOption import SupplierOption
from models.matchedCategory import MatchedCategory
from models.unmatchedCategory import UnmatchedCategory
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource, fields, marshal
from bson.json_util import dumps
import requests
import json
import datetime
import math
from resources.displayName import display_name


# ######################################### #
#              DWD class                    #
# ######################################### #

class DefaultBoops(Resource):
    def post(self, tenant, slug):
        category = SupplierCategory.objects(tenant_id=tenant, slug=slug).first()
        # create categories to the assortments api
        proxy = requests.get(f"https://api.printdeal.com/api/products/{category.sku}/attributes",
                             data={}, headers={
                "API-Secret": "Mi4hQt31Pzbab6+aiBJQUFCg7Mo3Tp/AhY4tcohVVIcqcxL7",
                "User-ID": "0ac967eb-1726-4f4d-9cab-d7be7183a78d",
                "accept": "application/vnd.printdeal-api.v2"
            })

        boops = []
        for box in proxy.json():
            if box not in ['Delivery Type', 'quantity', 'Printing Process']:
                if Box.objects(slug=slugify(box, to_lower=True), tenant_id=tenant).first():
                    boxToSave = box
                    if slug == "letterhead" and box == "Punchholes":
                        boxToSave = "Punch Holes"
                    boxObj = {

                        'id': Box.objects(name=slugify(box, to_lower=True)).first(),
                        'type': 'input',
                        'inputType': 'select',
                        'name': boxToSave,
                        'display_name': display_name({"name":boxToSave, "display_name": boxToSave}),
                        "system_key": boxToSave,
                        'slug': slugify(boxToSave, to_lower=True),
                        'ops': []
                    }
                    for option in proxy.json()[box]:
                        if isinstance(option, str):
                            if Option.objects(slug=slugify(option, to_lower=True)).first():
                                boxObj['ops'].append({
                                    'id': Option.objects(slug=slugify(option, to_lower=True)).first(),
                                    'title': option,
                                    'slug': slugify(option, to_lower=True),
                                    'excludes': []
                                })
                            else:
                                pass
                        else:
                            if Option.objects(slug=slugify(box, to_lower=True)).first():
                                boxObj['ops'].append({
                                    'id': Option.objects(slug=slugify(box, to_lower=True)).first(),
                                    'title': box,
                                    'slug': slugify(box, to_lower=True),
                                    'excludes': []
                                })
                            else:
                                pass
                else:
                    pass
                # return boxObj
                boops.append(boxObj)
        existingBoops = Boops.objects(category=category.linked).first()
        if not existingBoops:
            Boops(**{"category": category.linked, "category_name": category.name,
                     "category_slug": category.slug, "boops": boops}).save()
        else:
            existingBoops.update(boops=boops)
        return {'message': 'boops saved with success', 'code': 200}


class ImportSupplierBoops(Resource):
    def post(self, tenant, slug):
        supplier_category = SupplierCategory.objects(tenant_id=tenant, slug=slug).first()
        # create categories to the assortments api
        proxy = requests.get(f"https://api.printdeal.com/api/products/{supplier_category.sku}/attributes",
                             data={}, headers={
                "API-Secret": "Mi4hQt31Pzbab6+aiBJQUFCg7Mo3Tp/AhY4tcohVVIcqcxL7",
                "User-ID": "0ac967eb-1726-4f4d-9cab-d7be7183a78d",
                "accept": "application/vnd.printdeal-api.v2"
            })

        boops = []
        for box in proxy.json():
            if box not in ['Delivery Type', 'quantity', 'Printing Process']:
                if SupplierBox.objects(slug=slugify(box, to_lower=True)).count():
                    boxObj = {
                        'id': SupplierBox.objects(slug=slugify(box, to_lower=True), tenant_id=tenant).first().linked.id,
                        'type': 'input',
                        'inputType': 'select',
                        'name': box,
                        'slug': slugify(box, to_lower=True),
                        'ops': []
                    }
                    for option in proxy.json()[box]:
                        if type(option) is dict:
                            print(option)
                        elif SupplierOption.objects(slug=slugify(option, to_lower=True), tenant_id=tenant).count():
                            boxObj['ops'].append({

                                'id': SupplierOption.objects(slug=slugify(option, to_lower=True),
                                                             tenant_id=tenant).first().linked.id,
                                'name': option,
                                'slug': slugify(option, to_lower=True),
                                'excludes': []
                            })
                        else:
                            pass
                # return boxObj
                boops.append(boxObj)

        existing_boops = SupplierBoops.objects(supplier_category=supplier_category.id, tenant_id=tenant).first()

        if not existing_boops:
            SupplierBoops(**{
                "tenant_id": tenant,
                "tenant_name": "drukwerkdeal",
                "supplier_category": supplier_category.id,
                "linked": supplier_category.linked,
                "name": supplier_category.name,
                "slug": slugify(supplier_category.name, to_lower=True),
                'display_name': supplier_category.display_name,
                "system_key": supplier_category.name,
                "boops": boops
            }).save()
        else:
            existing_boops.update(boops=boops)
        return {'message': 'boops saved with success', 'code': 200}
