from flask import Response, request, jsonify
from models.category import Category
from models.box import Box
from models.option import Option
from models.product import Product
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource, fields, marshal
from bson.json_util import dumps
import requests
import json
import datetime
import math

class GetAttributes(Resource):

    def post(self, slug):
        #data = request.get_json()
        # get category's sku
        if(slug == 'all'):
            cats = json.loads(Category.objects.to_json())
        else:
            cats = json.loads(Category.objects(slug = slug).to_json())

        for cat in cats:

            proxy = requests.get(f"https://api.printdeal.com/api/products/{cat['sku']}/combinations", data={},
                                headers={
                                    "API-Secret": "Mi4hQt31Pzbab6+aiBJQUFCg7Mo3Tp/AhY4tcohVVIcqcxL7",
                                    "User-ID": "0ac967eb-1726-4f4d-9cab-d7be7183a78d",
                                    "accept": "application/vnd.printdeal-api.v2"
            })
            
            prod = {}
            for product in proxy.json():
                prod['category_id'] = cat['sku']
                prod['category_name'] = cat['name']
                prod['iso'] = 'EN'
                prod['object'] = {}
                attribute = {}
                
                if("product" in product):
                    for att in product["product"]["attributes"]:
                        bx = {}
                        opt = {}
                        if Box.objects(name=att['attribute']).count() == 0:
                            bx = {'name': att['attribute'], 'iso': "EN"}
                            Box(**bx).save()

                        if Option.objects(name=att['value']).count() == 0:
                            opt = {'name': att['value'], 'iso': "EN", 'box': att['attribute']}
                            Option(**opt).save()

                        attribute = {att['attribute']: att['value']}
                        prod['object'].update(attribute)

                    price = []
                    for pr in product['product']['prices']:
                        p = {
                            "qty": pr['quantity'],
                            "delivery-days": pr['information']['deliveryDays'],
                            "p": pr['price'],
                            "ppp": pr['price'] / pr['quantity']
                        }
                        price.append(p)
                    prod['prices'] = price
                    # return prod
                    if Product.objects(category_name=cat['name'], object=prod['object']).count() == 0:
                        product = Product(**prod).save()
        resJson = {
            "status": 200,
            "data": "data has been inserted",
        }
        return resJson


class CreateBoops(Resource):

    def post(self, slug):
        cats = json.loads(Category.objects(slug = slug).to_json())
        cat_name = cats[0]['name']
        cat_pro = []
        for valid_opt in json.loads(Product.objects(category_name = cat_name).to_json()):
            cat_pro.append(valid_opt)

        for option in json.loads(Option.objects.to_json()):
            options = {}
            options_all = {}
            options_and_exclude = []
            box_name = option['box']
            
            
            for valid_opt in cat_pro:
                if(box_name in valid_opt['object'] and valid_opt['object'][box_name] == option['name']):

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
                    if(box_name in ex_valid_opt['object'] and ex_valid_opt['object'][box_name] == option['name'] and ex_valid[1] in ex_valid_opt['object'] and ex_valid_opt['object'][ex_valid[1]] == ex_valid[0]):
                        for exclude_with_nd_opt in ex_valid_opt['object'].items():
                            exclude.pop(exclude_with_nd_opt[1], None)
                            # to delete the same box options
                            if ex_valid[1] == exclude_with_nd_opt[0] or exclude_with_nd_opt[1] == option['name'] or exclude_with_nd_opt[1] in valid_with_nd_opt:
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

            Option.objects(name=option['name']).update_one(set__valid_options= options_and_exclude)