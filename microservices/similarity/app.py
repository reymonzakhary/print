import os
from flask import Flask, jsonify, request, Response
from flask_restful import Api, Resource
from factory.database import Database
from bson.json_util import dumps
from slugify import slugify, Slugify, UniqueSlugify
from helpers.helper import generateUUID, checkSimilarity
from bson import json_util

import json
import difflib
import requests
import csv


app = Flask(__name__)
api = Api(app)


###################################
# similarity class
###################################


class Similarity(Resource):

    def post(self, table):
        DB = Database()

        categories = DB.db[table]

        suppliers_categories = DB.db["supplier_"+table]
        matched_categories = DB.db["matched_"+table]
        unmatched_categories = DB.db["unmatched_"+table]

        data = request.get_json()

        matched_c = []
        posted_categories = data[table]
        for posted in posted_categories:
            matched_cats = []

            matched_flag = False
            unmatched_flag = True
            posted_name = slugify(posted['name'], to_lower=True)

            standard_categories = categories.find(
                {}, {"_id": 0, "media": 0, "sorting": 0, "id": 0})

            for category in standard_categories:
                found = checkSimilarity(posted["name"], category['name'])

                # matched_c.append(
                #     {"err": posted_name, 'stn': category['slug'], 'sim': found})

                if found and found['percentage'] == 100 or posted["name"] == category["name"]:
                    obj = {
                        "tenant": {
                            "name": data['tenant_name'],
                            "id": data["tenant"]
                        },
                        "standard": category['name'],
                        "name": posted["name"],
                        # "sku": posted['sku'],
                        "slug": posted_name
                    }
                    suppliers_categories.update_one(
                        obj, {"$setOnInsert": obj}, upsert=True)

                    matched_flag = False
                    unmatched_flag = False
                    break

                elif found and 50 < found['percentage'] < 100:

                    category['found'] = found
                    matched_cats.append(category)
                    unmatched_flag = False
                    matched_flag = True

            if unmatched_flag:
                unmatched = {
                    "tenant": {
                        "name": data['tenant_name'],
                        "id": data['tenant'],
                    },
                    "object": {
                        "name": posted["name"],
                        # "sku": posted['sku'],
                        "slug": posted_name
                    }
                }
                unmatched_categories.update_one(
                    unmatched, {"$setOnInsert": unmatched}, upsert=True)

            elif matched_flag:
                for cat in matched_cats:
                    matched = {
                        "standard": cat['slug'],
                        "percentage": cat['found']['percentage'],
                        "tenant": {
                            "name": data['tenant_name'],
                            "id": data["tenant"]
                        },
                        "object": {

                            "name": posted["name"],
                            # "sku": posted['sku'],
                            "slug": posted_name
                        }
                    }
                    matched_categories.update(
                        matched, {"$setOnInsert": matched}, upsert=True)

        return jsonify({"status": 200, "data": "data has been updated"})


api.add_resource(Similarity, "/similarity/<string:table>")

if __name__ == "__main__":
    ENVIRONMENT_DEBUG = os.environ.get("APP_DEBUG", False)
    ENVIRONMENT_PORT = os.environ.get("APP_PORT", 5000)
    app.run(host="0.0.0.0", port=ENVIRONMENT_PORT, debug=ENVIRONMENT_DEBUG)
