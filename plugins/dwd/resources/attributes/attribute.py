from flask import Response, request, jsonify
from models.category import Category
from models.box import Box
from models.boops import Boops
from models.option import Option
from models.categoryBoxOption import CategoryBoxOption
from models.unhandledProduct import UnhandledProduct
from models.supplierProduct import SupplierProduct
from models.supplierProductPrice import SupplierProductPrice
from models.finalProduct import FinalProduct
from models.supplierCategory import SupplierCategory
from models.supplierBox import SupplierBox
from models.supplierOption import SupplierOption
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource, fields, marshal
from bson.json_util import dumps
from bson.objectid import ObjectId
import requests
import json
import datetime
import math
from resources.displayName import display_name
# from migrations.displayName import display_name

class GetAttributes(Resource):

    def post(self, tenant, slug):
        data = request.get_json()
        # get category's sku
        translationTable = str.maketrans("éàèùâêîôûç", "eaeuaeiouc")

        cat = SupplierCategory.objects(tenant_id=tenant, slug=slug).first()

        proxy = requests.get(f"https://api.printdeal.com/api/products/{cat.sku}/combinations", data={},
                             headers={
                                 "API-Secret": "Mi4hQt31Pzbab6+aiBJQUFCg7Mo3Tp/AhY4tcohVVIcqcxL7",
                                 "User-ID": "0ac967eb-1726-4f4d-9cab-d7be7183a78d",
                                 "accept": "application/vnd.printdeal-api.v2"
                             })
        prod = {}
        sys_cat = Category.objects(slug=slug).first()
        for product in proxy.json():

            prod['tenant_id'] = tenant
            prod['host_id'] = data['host_id'] if 'host_id' in data else tenant
            prod['tenant_name'] = data['tenant_name']
            prod['category_name'] = cat.linked.name
            prod['category_display_name'] = cat.display_name
            prod['category_slug'] = cat.linked.slug
            prod['supplier_category'] = cat.id
            prod['shareable'] = True
            prod['published'] = True
            prod['object'] = []
            prod['linked'] = sys_cat.id if sys_cat else ""
            if "product" in product:
                pm = 'digital'
                dlv = ''
                for att in product["product"]["attributes"]:
                    box = {}
                    option = {}

                    if SupplierBox.objects(slug=slugify(att['attribute'].translate(translationTable), to_lower=True)).count() == 0:
                        url = 'http://assortments:5000/similarity/boxes'
                        obj = {'tenant': tenant, 'tenant_name': 'dwd', 'boxes': [{"name": att['attribute']}]}
                        header = {"Content-type": "application/json"}
                        res = requests.post(url, json=obj, headers=header)

                        unHandeldItem = {
                            "tenant_id":tenant,
                            "tenant_name": data['tenant_name'],
                            "box_name":att['attribute'],
                            "option_name":att['value'],
                            "type":"box",
                            "object": product["product"]["attributes"],
                            "sku":cat.sku
                        }
                        UnhandledProduct(**unHandeldItem).save()
                        pass
                        # return json.loads(dumps({"boxes": [{"name": att['attribute']}]}))
                    else:
                        box = SupplierBox.objects(slug=slugify(att['attribute'].translate(translationTable), to_lower=True)).first()

                    if SupplierOption.objects(slug=slugify(att['value'], to_lower=True)).count() == 0:
                        url = 'http://assortments:5000/similarity/options'
                        obj = {'tenant': tenant, 'tenant_name': 'dwd', 'options': [{"name": att['value']}]}
                        header = {"Content-type": "application/json"}
                        res = requests.post(url, json=obj, headers=header)
                        unHandeldItem = {
                            "tenant_id":tenant,
                            "tenant_name":"dwd",
                            "box_name":att['attribute'],
                            "option_name":att['value'],
                            "type":"option",
                            "object": product["product"]["attributes"],
                            "sku":cat.sku
                        }
                        UnhandledProduct(**unHandeldItem).save()
                        pass
                        # return json.loads(dumps({"options": [{"name": att['value']}]}))

                    else:
                        option = SupplierOption.objects(slug=slugify(att['value'].translate(translationTable), to_lower=True)).first()
                    if not box:
                        pass
                    if box.slug == "printing-process" and option:
                        pm = option.name
                    if box.slug == "delivery-type" and option:
                        dlv = option.name

                    if box.slug not in ['printing-process', 'delivery-type']:
                        if not option:
                            pass
                        else:

                            supplier_option = SupplierOption.objects(slug=slugify(att['value'], to_lower=True), tenant_id=tenant).first()
                            supplier_box = SupplierBox.objects(slug=slugify(box.slug, to_lower=True), tenant_id=tenant).first()

                            option_link = Option.objects(slug=slugify(att['value'], to_lower=True)).first()
                            box_link = Box.objects(slug=slugify(box.slug, to_lower=True)).first()


                            prod['object'].append({
                                "key_link": ObjectId(box_link.id) if box_link else "",
                                "key": box.slug,
                                "display_key": display_name(box.name),
                                "box_id": supplier_box.id,
                                "value_link": ObjectId(option_link.id) if option_link else "",
                                "value": slugify(att['value'], to_lower=True),
                                "display_value": display_name(option.name),
                                "option_id": supplier_option.id,
                            })

                ## check if product exists
                if prod['object']:
                    if SupplierProduct.objects(object=prod['object']).count() == 0:
                        productId = SupplierProduct(**prod).save()
                        price = []
                    else:
                        productId = SupplierProduct.objects(object=prod['object']).first()

                    for pr in product['product']['prices']:
                        price = int(pr['price'] * 100)
                        p = {
                            "supplier_product": productId.id,
                            "supplier_id": tenant,
                            "supplier_name": data['tenant_name'],
                            "host_id":  data['host_id'] if 'host_id' in data else tenant,
                            "tables": {
                                "pm": pm,
                                "qty": pr['quantity'],
                                "dlv": {
                                    "title": "",
                                    "days": pr['information']['deliveryDays']
                                },
                                "p": price,
                                "ppp":price / pr['quantity']
                            }
                        }

                        if SupplierProductPrice.objects(**p).count() == 0:
                            SupplierProductPrice(**p).save()
                        else:
                            pass

        return jsonify({
            "status": 200,
            "data": "data has been inserted",
        })


class CreateBoops(Resource):
    def get(self, tenant, slug):
        cats = json.loads(SupplierCategory.objects(tenant_id=tenant, slug=slugify(slug, to_lower=True)).to_json())
        proxy = requests.get(f"https://api.printdeal.com/api/products/8765db37-756e-4c7f-b618-71027534f296/attributes",
                             data={},
                             headers={
                                 "API-Secret": "Mi4hQt31Pzbab6+aiBJQUFCg7Mo3Tp/AhY4tcohVVIcqcxL7",
                                 "User-ID": "0ac967eb-1726-4f4d-9cab-d7be7183a78d",
                                 "accept": "application/vnd.printdeal-api.v2"
                             })

        boops = []
        for box in proxy.json():
            boops = box

        return boops
        return proxy.json()

    def post(self, tenant, slug):

        cats = SupplierCategory.objects(tenant_id=tenant, slug=slugify(slug, to_lower=True)).first()
        # return jsonify(cats)
        proxy = requests.get(f"https://api.printdeal.com/api/products/{cats['sku']}/attributes",
                             data={},
                             headers={
                                 "API-Secret": "Mi4hQt31Pzbab6+aiBJQUFCg7Mo3Tp/AhY4tcohVVIcqcxL7",
                                 "User-ID": "0ac967eb-1726-4f4d-9cab-d7be7183a78d",
                                 "accept": "application/vnd.printdeal-api.v2"
                             })

        boops = []
        for box in proxy.json():
            # SupplierBox.objects()
            boops.append(box)

        return boops
        cat_name = cats[0]['name']
        cat_pro = []
        for valid_opt in json.loads(Product.objects(category_name=cat_name).to_json()):
            cat_pro.append(valid_opt)

        for option in json.loads(Option.objects.to_json()):
            options = {}
            options_all = {}
            options_and_exclude = []
            box_name = option['box']

            for valid_opt in cat_pro:
                if (box_name in valid_opt['object'] and valid_opt['object'][box_name] == option['name']):

                    for valid in valid_opt['object'].items():
                        if valid[1] != option['name']:
                            options.update({valid[1]: valid[0]})
                        options_all.update({valid[1]: valid[0]})

            # second selection
            for ex_valid in options.items():
                valid_with_nd_opt = {}
                exclude = {}
                exclude_all = {}
                # ex.update(options)
                # for ex_valid_opt in saxo_products.find({"object."+box_name: option['name'], "object."+ex_valid[1]: ex_valid[0]}):
                for ex_valid_opt in cat_pro:
                    if (box_name in ex_valid_opt['object'] and ex_valid_opt['object'][box_name] == option['name'] and
                            ex_valid[1] in ex_valid_opt['object'] and ex_valid_opt['object'][ex_valid[1]] == ex_valid[
                                0]):
                        for exclude_with_nd_opt in ex_valid_opt['object'].items():
                            exclude.pop(exclude_with_nd_opt[1], None)
                            # to delete the same box options
                            if ex_valid[1] == exclude_with_nd_opt[0] or exclude_with_nd_opt[1] == option['name'] or \
                                    exclude_with_nd_opt[1] in valid_with_nd_opt:
                                pass
                            else:
                                exclude.update(
                                    {exclude_with_nd_opt[1]: exclude_with_nd_opt[0]})

                            exclude_all.update(
                                {exclude_with_nd_opt[1]: exclude_with_nd_opt[0]})

                # return json.loads(dumps({'opt': options, 'ex': exclude}))

                options_alln = {}

                for x in options_all.items():
                    if x[1] == ex_valid[1] or x[0] in exclude_all:
                        pass
                    else:
                        options_alln.update({x[0]: x[1]})

                options_and_exclude.append(
                    {'option': ex_valid[0], 'box': ex_valid[1], 'exclude': options_alln})

            # return json.loads(dumps({"options": options, "options and include": options_and_exclude}))

            Option.objects(name=option['name']).update_one(set__valid_options=options_and_exclude)


class FinalBoops(Resource):

    def post(self, cat_slug):
        data = request.get_json()

        if cat_slug == 'all':
            cats = json.loads(Category.objects().to_json())
        else:
            cats = json.loads(Category.objects(slug=cat_slug).to_json())
        # for cat in cats:
        cat = cats[0]
        supplierCat = json.loads(SupplierCategory.objects(slug=cat['slug']).to_json())
        supplierCat = supplierCat[0]
        cat_pro = []
        for valid_opt in json.loads(Product.objects(category_name=cat['name']).to_json()):
            cat_pro.append(valid_opt)
        return {
            'tenant_id': data['tenant_id'],
            'tenant_name': data['tenant_name'],
            'supplier_category_id': supplierCat['_id'],
            'category_id': cat['_id'],
            'category_name': cat['name'],
            'boops': cat_pro
        }
        return cats
        cat_pro = []
        for valid_opt in json.loads(Product.objects(category_name=cat_name).to_json()):
            cat_pro.append(valid_opt)

        for option in json.loads(Option.objects.to_json()):
            options = {}
            options_all = {}
            options_and_exclude = []
            box_name = option['box']

            for valid_opt in cat_pro:
                if (box_name in valid_opt['object'] and valid_opt['object'][box_name] == option['name']):

                    for valid in valid_opt['object'].items():
                        if valid[1] != option['name']:
                            options.update({valid[1]: valid[0]})
                        options_all.update({valid[1]: valid[0]})

            # second selection
            for ex_valid in options.items():
                valid_with_nd_opt = {}
                exclude = {}
                exclude_all = {}
                # ex.update(options)
                # for ex_valid_opt in saxo_products.find({"object."+box_name: option['name'], "object."+ex_valid[1]: ex_valid[0]}):
                for ex_valid_opt in cat_pro:
                    if (box_name in ex_valid_opt['object'] and ex_valid_opt['object'][box_name] == option['name'] and
                            ex_valid[1] in ex_valid_opt['object'] and ex_valid_opt['object'][ex_valid[1]] == ex_valid[
                                0]):
                        for exclude_with_nd_opt in ex_valid_opt['object'].items():
                            exclude.pop(exclude_with_nd_opt[1], None)
                            # to delete the same box options
                            if ex_valid[1] == exclude_with_nd_opt[0] or exclude_with_nd_opt[1] == option['name'] or \
                                    exclude_with_nd_opt[1] in valid_with_nd_opt:
                                pass
                            else:
                                exclude.update(
                                    {exclude_with_nd_opt[1]: exclude_with_nd_opt[0]})

                            exclude_all.update(
                                {exclude_with_nd_opt[1]: exclude_with_nd_opt[0]})

                # return json.loads(dumps({'opt': options, 'ex': exclude}))

                options_alln = {}

                for x in options_all.items():
                    if x[1] == ex_valid[1] or x[0] in exclude_all:
                        pass
                    else:
                        options_alln.update({x[0]: x[1]})

                options_and_exclude.append(
                    {'option': ex_valid[0], 'box': ex_valid[1], 'exclude': options_alln})

            # return json.loads(dumps({"options": options, "options and include": options_and_exclude}))

            Option.objects(name=option['name']).update_one(set__valid_options=options_and_exclude)


class FinalProducts(Resource):
    def post(self, tenant, cat_slug):

        data = request.get_json()

        if cat_slug == 'all':
            cats = json.loads(Category.objects().to_json())
        else:
            cats = json.loads(SupplierCategory.objects(slug=cat_slug).to_json())
        cat = cats[0]

        supplierCat = SupplierCategory.objects(tenant_id=tenant, slug=cat_slug).first()

        return supplierCat
        res = []
        prod = {}
        for valid_opt in json.loads(Product.objects(category_name=cat['name']).to_json()):
            return valid_opt
            prod['atters'] = {}
            attrebute = []
            for box in valid_opt['object']:
                attrebute.append({
                    'key': box,
                    'value': valid_opt['object'][box]
                })
            prod['atters'] = attrebute
            prod['supplier_category_id'] = supplierCat['_id']
            prod['category_id'] = cat['_id']
            prod['category_name'] = valid_opt['category_name']
            prod['supplier_category_name'] = cat['name']
            res.append(prod)
            FinalProduct(**prod).save()
        return res
