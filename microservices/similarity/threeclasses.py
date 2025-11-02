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

DB = Database()

categories = DB.db["categories"]
boxes = DB.db["boxes"]
options = DB.db["options"]

suppliers_categories = DB.db["suppliers_categories"]
matched_categories = DB.db["matched_categories"]
unmatched_categories = DB.db["unmatched_categories"]

suppliers_boxes = DB.db["suppliers_boxes"]
matched_boxes = DB.db["matched_boxes"]
unmatched_boxes = DB.db["unmatched_boxes"]

suppliers_options = DB.db["suppliers_options"]
matched_options = DB.db["matched_options"]
unmatched_options = DB.db["unmatched_options"]


###################################
# similarity class
###################################

class SimilarityCategories(Resource):

    def post(self):
        data = request.get_json()
        standard_categories = json.loads(dumps(categories.find(
            {}, {"_id": 0, "media": 0, "sorting": 0, "id": 0})))

        posted_categories = data['categories']

        for posted in posted_categories:

            matched_cats = []
            unmatched_flag = True
            posted_name = slugify(posted['name'], to_lower=True)

            for category in standard_categories:
                found = checkSimilarity(posted_name, category['slug'])
                if(found and found['percentage'] == 100 or posted_name == category["slug"]):
                    obj = {
                        "tenant": {
                            "name": data['tenant_name'],
                            "id": data["tenant"]
                        },
                        "standard": category['slug'],
                        "name": posted["name"],
                        "sku": posted['sku'],
                        "slug": posted_name
                    }
                    suppliers_categories.update_one(
                        obj, {"$setOnInsert": obj}, upsert=True)

                    matched_cats = []
                    unmatched_flag = False
                    break

                elif(found and found['percentage'] > 80 and found['percentage'] < 100):

                    matched_cats.append(category)
                    unmatched_flag = False

            if(unmatched_flag == True):
                unmatched = {
                    "tenant": {
                        "name": data['tenant_name'],
                        "id": data['tenant'],
                    },
                    "object": {
                        "name": posted["name"],
                        "sku": posted['sku'],
                        "slug": posted_name
                    }
                }
                unmatched_categories.update_one(
                    unmatched, {"$setOnInsert": unmatched}, upsert=True)

            elif(matched_cats):
                for category in matched_cats:

                    matched = {
                        "standard": category['slug'],
                        "similarity": found,
                        "object": {
                            "tenant": {
                                "name": data['tenant_name'],
                                "id": data["tenant"]
                            },
                            "name": posted["name"],
                            "sku": posted['sku'],
                            "slug": posted_name
                        }
                    }
                    matched_categories.update(
                        matched, {"$setOnInsert": matched}, upsert=True)

        return jsonify({"status": 200, "data": "data has been updated"})


###################################
#        similarity class         #
###################################

class SimilarityBoxes(Resource):

    def post(self):
        data = request.get_json()

        standard_boxes = json.loads(dumps(boxes.find(
            {}, {"_id": 0, "categories": 0})))

        posted_boxes = data['boxes']

        for posted in posted_boxes:

            matched_bxs = []
            unmatched_flag = True
            posted_name = slugify(posted['name'], to_lower=True)

            for box in standard_boxes:
                found = checkSimilarity(posted_name, box['slug'])
                if(found and found['percentage'] == 100 or posted_name == box["slug"]):
                    obj = {
                        "tenant": {
                            "name": data['tenant_name'],
                            "id": data["tenant"]
                        },
                        "standard": box['slug'],
                        "iso": box['iso'],
                        "name": posted['name'],
                        "sku": posted['sku'],
                        "slug": posted_name,
                        "information": box['information']
                    }

                    suppliers_boxes.update_one(
                        obj, {"$setOnInsert": obj}, upsert=True)

                    matched_bxs = []
                    unmatched_flag = False
                    break

                elif(found and found['percentage'] > 80 and found['percentage'] < 100):

                    matched_bxs.append(box)
                    unmatched_flag = False

            if(unmatched_flag == True):
                unmatched = {
                    "tenant": {
                        "name": data['tenant_name'],
                        "id": data['tenant'],
                    },
                    "object": {
                        "name": posted["name"],
                        "sku": posted['sku'],
                        "slug": posted_name
                    }
                }
                unmatched_boxes.update_one(
                    unmatched, {"$setOnInsert": unmatched}, upsert=True)

            elif(matched_bxs):
                for box in matched_bxs:
                    matched = {
                        "standard": box['slug'],
                        "similarity": found,
                        "object": {
                            "tenant": {
                                "name": data['tenant_name'],
                                "id": data["tenant"]
                            },
                            "name": posted["name"],
                            "iso": box['iso'],
                            "sku": posted['sku'],
                            "slug": posted_name,
                            "information": box['information']
                        }
                    }
                    matched_categories.update(
                        matched, {"$setOnInsert": matched}, upsert=True)

        return jsonify({"status": 200, "data": "data has been updated"})


###################################
#        similarity class         #
###################################

class SimilarityObtions(Resource):

    def post(self):
        data = request.get_json()

        standard_options = json.loads(dumps(options.find(
            {}, {"slug": 1, "name": 1})))

        posted_options = data['options']

        for posted in posted_options:

            matched_opt = []
            unmatched_flag = True
            posted_name = slugify(posted['name'], to_lower=True)

            for option in standard_options:
                found = checkSimilarity(posted_name, option['slug'])
                if(found and found['percentage'] == 100 or posted_name == option["slug"]):
                    obj = {
                        "tenant": {
                            "name": data['tenant_name'],
                            "id": data["tenant"]
                        },
                        "standard": option['slug'],
                        "name": posted['name'],
                        "slug": posted_name,
                    }

                    suppliers_options.update_one(
                        obj, {"$setOnInsert": obj}, upsert=True)

                    matched_opt = []
                    unmatched_flag = False
                    break

                elif(found and found['percentage'] > 80 and found['percentage'] < 100):

                    matched_opt.append(option)
                    unmatched_flag = False

            if(unmatched_flag == True):
                unmatched = {
                    "tenant": {
                        "name": data['tenant_name'],
                        "id": data['tenant'],
                    },
                    "object": {
                        "name": posted["name"],
                        "slug": posted_name
                    }
                }
                unmatched_options.update_one(
                    unmatched, {"$setOnInsert": unmatched}, upsert=True)

            elif(matched_opt):
                for option in matched_opt:
                    matched = {
                        "standard": option['slug'],
                        "similarity": found,
                        "object": {
                            "tenant": {
                                "name": data['tenant_name'],
                                "id": data["tenant"]
                            },
                            "name": posted["name"],
                            "sku": posted['sku'],
                            "slug": posted_name,
                        }
                    }
                    matched_categories.update(
                        matched, {"$setOnInsert": matched}, upsert=True)

        return jsonify({"status": 200, "data": "data has been updated"})


api.add_resource(SimilarityCategories, "/categories/similarity")
api.add_resource(SimilarityBoxes, "/boxes/similarity")
api.add_resource(SimilarityObtions, "/options/similarity")
api.add_resource(Similarity, "/similarity/<string:table>")

if __name__ == "__main__":
    ENVIRONMENT_DEBUG = os.environ.get("APP_DEBUG", False)
    ENVIRONMENT_PORT = os.environ.get("APP_PORT", 5000)
    app.run(host="0.0.0.0", port=ENVIRONMENT_PORT, debug=ENVIRONMENT_DEBUG)
