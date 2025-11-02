import os
from flask import Flask, jsonify, request, Response
from flask_restful import Api, Resource
from factory.database import Database
from bson.json_util import dumps
from slugify import slugify, Slugify, UniqueSlugify
from helpers.helper import generateUUID
from bson import json_util

import json
import difflib
import requests
import csv


app = Flask(__name__)
api = Api(app)

DB = Database()

# ######################################### #
#             Saxo class                    #
# ######################################### #


class Saxo(Resource):
    # get all categories
    ###################

    def get(self, table=''):

        post_data = request.get_json()

        # Get data from obj
        tenant = post_data["tenant"]
        tenant_name = post_data["tenant_name"]

        saxo_products = DB.db["saxo_products"]
        saxo_categories = DB.db["saxo_categories"]
        saxo_options = DB.db["saxo_options"]
        saxo_boxes = DB.db["saxo_boxes"]
        saxo_boops = DB.db["saxo_boops"]

        plus = ["Standard+", "DeliveryTimesStandard+",
                "Express+", "DeliveryTimesExpress+"]
        csvTrans = {}
        # JSON file for translation
        with open('saxo/PropertyValueIdToPropertyValue_Mapping_AllLanguages.csv', newline='') as csvfile:
            reader = csv.reader(csvfile, delimiter=",")
            for row in reader:
                if row[0] in csvTrans:
                    csvTrans[row[0]].update({row[9]: row})
                else:
                    csvTrans[row[0]] = {row[9]: row}

        f = open(
            # 'saxo/Notepads with spiral binding.6125.13112020 110511.saxoprint.de_1.json', "r")
            'saxo/Stickers.6125.13112020 110920.saxoprint.de_1.json', "r")
        # 'saxo/NYT-815.6125.13112020 110536.saxoprint.de_1.json', "r")

        # Reading from file
        data = json.loads(f.read().encode('utf-8'))

        # Iterating through the json

        for i in data:
            newI = {}
            objectI = {}
            objectIP = {}
            twoObject = []
            check = False
            check_plus = False
            for st, nd in i.items():
                if(st in csvTrans):
                    if(nd in csvTrans[st]):
                        if(st == '6'):
                            title = "category_name"
                            if(check == False):
                                check = True
                                if(csvTrans[st][nd][12] == 'NULL'):
                                    csvTrans[st][nd][12] = csvTrans[st][nd][10]
                                category_check = saxo_categories.find({'slug': slugify(
                                    csvTrans["6"][i["6"]][12], to_lower=True)})
                                if category_check.count() == 0:
                                    idc = str(generateUUID())
                                    obj = {
                                        "id": idc,
                                        "name": csvTrans[st][nd][12],
                                        "slug": slugify(csvTrans[st][nd][12], to_lower=True),
                                        "media": {
                                            "name": csvTrans[st][nd][12],
                                            "path": "",
                                            "mimetype": "jpg",
                                            "size": 11212
                                        },
                                        "sorting": []
                                    }
                                    saxo_cat_id = saxo_categories.insert(obj)

                                    saxo_boops.insert({'supplier_id': tenant, 'supplier_name': tenant_name,
                                                       'category_id': saxo_cat_id, 'category_name': csvTrans[st][nd][12], 'boops': []})

                                else:
                                    for x in category_check:
                                        cat = x

                                    saxo_cat_id = ['_id']

                        else:
                            cat = {"name": csvTrans["6"][i["6"]][12], "slug": slugify(
                                csvTrans["6"][i["6"]][12], to_lower=True)}

                            title = csvTrans[st][nd][3].replace('.', '')

                            boxes_check = saxo_boxes.find({"name": title})
                            if boxes_check.count() == 0:
                                idb = str(generateUUID())

                                obj = {
                                    "id": idb,
                                    "name": title,
                                    "slug": slugify(title, to_lower=True),
                                    "information": "info",
                                    "iso": "EN",
                                    "categories": [cat]
                                }
                                saxo_bx_id = saxo_boxes.insert(obj)
                            else:
                                for x in boxes_check:
                                    bx = x
                                saxo_bx_id = bx['_id']
                                if(cat not in bx["categories"]):
                                    bx["categories"].append(cat)
                                    saxo_boxes.update(
                                        {"name": bx['name']}, {"$set": {"categories": bx["categories"]}})

                            box = {"name": title, "slug": slugify(
                                title, to_lower=True)}
                            options_check = saxo_options.find(
                                {"name": csvTrans[st][nd][12]})

                            if options_check.count() == 0:
                                ido = str(generateUUID())

                                objo = {
                                    "id": ido,
                                    "name": csvTrans[st][nd][12],
                                    "slug": slugify(csvTrans[st][nd][12], to_lower=True),
                                    "information": "info",
                                    "iso": "EN",
                                    "relationships": [{"category": cat, "box": box}]
                                }
                                saxo_opt_id = saxo_options.insert(objo)
                            else:
                                for x in options_check:
                                    bx = x
                                saxo_opt_id = bx['_id']
                                if({"category": cat, "box": box} not in bx["relationships"]):
                                    bx["relationships"].append(
                                        {"category": cat, "box": box})
                                    saxo_options.update(
                                        {"name": bx['name']}, {"$set": {"relationships": bx["relationships"]}})

                            newI.update(
                                {title: csvTrans[st][nd][12]})

                            # boops
                            box_name = title
                            option_name = csvTrans[st][nd][12]
                            cat_name = csvTrans["6"][i["6"]][12]
                            # boop = {'boops': []}

                            # saxo_boop = saxo_boops.find({"boops.title":box_name},{"boops": { "$elemMatch": {"title":box_name}}})
                            saxo_bo = []

                            bx_chk = False
                            for x in saxo_boops.find({"category_name": cat_name}):
                                saxo_boops_obj = x

                            for x, bx in enumerate(saxo_boops_obj['boops']):
                                if bx['title'] == box_name:
                                    bx_chk = True
                                    opt_chk = False
                                    for y, opt in enumerate(bx['ops']):
                                        if opt['title'] == option_name:
                                            opt_chk = True
                                            break

                                    if opt_chk == False:
                                        bx['ops'].append(
                                            {'id': saxo_opt_id, 'title': option_name, 'excludes': []})

                                saxo_bo.append(bx)

                            if bx_chk == False:
                                saxo_bo.append({'id': saxo_bx_id, 'type': 'input', 'inputType': 'select', 'title': box_name, 'value': box_name, 'ops': [
                                               {'id': saxo_opt_id, 'title': option_name, 'excludes': []}]})

                            if bx_chk == False or opt_chk == False:
                                saxo_boops.update({"category_name": cat_name}, {
                                                  "$set": {"boops": saxo_bo}})

                else:
                    st = st.replace('Auflage', 'qty')
                    st = st.replace('Portal', 'iso')
                    st = st.replace('.', '')
                    objectI.update({st: nd})
                    objectIP.update({st: nd})
                    if(st in plus):
                        check_plus = True
                        objectIP.pop(st.replace('+', ''))
                        objectI.pop(st)

            # if in collection update else insert
            saxo_category = saxo_products.find({"object": newI})

            if saxo_category.count() == 0:
                twoObject.append(objectI)
                if(check_plus == True):
                    twoObject.append(objectIP)
                saxo_products.insert({
                    "category_id": str(generateUUID()),
                    "category_name": csvTrans["6"][i["6"]][12],
                    "object": newI,
                    "prices": twoObject
                })
            else:
                for x in saxo_category:
                    cat = x

                cat["prices"].append(objectI)
                if(check_plus == True):
                    cat["prices"].append(objectIP)

                saxo_products.update(
                    {"category_name": csvTrans["6"][i["6"]][12]}, {"$set": {"prices": cat["prices"]}})

        # Closing file
        f.close()

        resJson = {
            "status": 200,
            "data": "Saxo data has been inserted",
        }

        return jsonify(resJson)

    # post all categories
    ###################
    def post(self, table):
        post_data = request.get_json()

        # Get data from obj
        tenant = post_data["tenant"]
        tenant_name = post_data["tenant_name"]
        saxo_categories = DB.db["saxo_"+table]
        categories = []
        saxo_category = saxo_categories.find()

        for x in saxo_category:
            categories.append(
                {'name': x['name'], 'sku': ''})

        # get all attributes
        url = 'http://similarity:5000/similarity/'+table
        myobj = {'tenant': tenant,
                 'tenant_name': tenant_name, table: categories}
        header = {"Content-type": "application/json"}
        # return json.dumps(categories)
        res = requests.post(url, json=myobj, headers=header)

        return res.json()

# take the second element for sort


def take_second(elem):
    return elem[1]


class SaxoBoops(Resource):
    # get all categories
    ###################

    def get(self, cat):

        saxo_products = DB.db["saxo_products"]
        saxo_categories = DB.db["saxo_categories"]
        saxo_options = DB.db["saxo_options"]
        saxo_boxes = DB.db["saxo_boxes"]
        saxo_boops = DB.db["saxo_boops"]

        # if in collection update else insert

        for option in saxo_options.find():
            options = []
            options_and_exclude = []
            box_name = option['relationships'][0]['box']['name']
            for valid_opt in saxo_products.find({"object."+box_name: option['name']}):
                for valid in valid_opt['object'].items():
                    if valid[1] != option['name']:
                        options.append(
                            {'option': valid[1], 'box': valid[0], 'exclude': {}})

            for ex_valid in options:
                # return json.loads(dumps(ex_valid))
                valid_with_nd_opt = {}
                exclude = {}
                # ex.update(options)
                for ex_valid_opt in saxo_products.find(
                        {"object."+box_name: option['name'], "object."+ex_valid['box']: ex_valid['option']}):
                    for exclude_with_nd_opt in ex_valid_opt['object'].items():
                        exclude.pop(exclude_with_nd_opt[1], None)
                        # to delete the same box options
                        if ex_valid['box'] != exclude_with_nd_opt[0]:
                            if exclude_with_nd_opt[1] not in valid_with_nd_opt:
                                exclude.update(
                                    {exclude_with_nd_opt[1]: exclude_with_nd_opt[0]})

                        valid_with_nd_opt.update(
                            {exclude_with_nd_opt[1]: exclude_with_nd_opt[0]})

                        # return json.loads(dumps({ex_valid['box']: exclude_with_nd_opt[0]}))
                options.update({valid[1]+'exclude': exclude})

            return json.loads(dumps({"options": options, "valid with second option": valid_with_nd_opt, "exclude": exclude}))

            saxo_options.update(
                {"name": option['name']}, {"$set": {"valid_options": options}})

        resJson = {
            "status": 200,
            "data": "Valid options inserted",
        }

        return jsonify(resJson)


api.add_resource(Saxo, "/saxo/<string:table>")
api.add_resource(SaxoBoops, "/saxo/boops/<string:cat>")


if __name__ == "__main__":
    ENVIRONMENT_DEBUG = os.environ.get("APP_DEBUG", False)
    ENVIRONMENT_PORT = os.environ.get("APP_PORT", 5000)
    app.run(host="0.0.0.0", port=ENVIRONMENT_PORT, debug=ENVIRONMENT_DEBUG)
