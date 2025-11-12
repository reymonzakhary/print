import json

from flask import Response, request, jsonify
from flask_restful import Resource
from slugify import slugify, Slugify, UniqueSlugify
import requests
from bson.json_util import dumps
from helper.helper import checkSimilarity
from migrations.displayName import display_name
from models.category import Category
from models.box import Box
from models.option import Option
from bson import ObjectId
from models.supplierCategory import SupplierCategory
from models.supplierBox import SupplierBox
from models.supplierOption import SupplierOption
from bson import json_util
import json

class SimilarityCategory(Resource):

    def post(self):
        from models.category import Category
        from models.supplierCategory import SupplierCategory
        from models.unmatchedCategory import UnmatchedCategory
        from models.matchedCategory import MatchedCategory

        data = request.get_json()
        posted_data = data['categories']
        matched_category = {}
        for posted in posted_data:

            matched_words = {'found': 0}
            matched_flag = False
            unmatched_flag = True
            posted_slug_name = slugify(posted["name"], to_lower=True)

            standard_data = Category.objects()
            for standard in standard_data:

                found = checkSimilarity(posted_slug_name, standard['slug'])

                if found and found['percentage'] == 100 or posted_slug_name == standard["slug"]:
                    supplier_cat = SupplierCategory.objects(tenant_id=data['tenant'], slug=posted_slug_name).first()
                    if supplier_cat:
                        supplier_cat.update(linked=standard, name=standard["name"])
                        matched_category = supplier_cat.to_mongo().to_dict()
                    else:
                        matched_category = standard.to_mongo().to_dict()

                    matched_flag = False
                    unmatched_flag = False
                    break

                elif found and 80 < found['percentage'] < 100:
                    # matched_words.append(category)
                    if found['percentage'] > matched_words['found']:
                        biggest_simi = {
                            'found': found['percentage'], 'parent_table': standard}
                        matched_words = biggest_simi

                    unmatched_flag = False
                    matched_flag = True

            if unmatched_flag:
                unmatched_obj = {
                    'tenant_id': data["tenant"],
                    'tenant_name': data['tenant_name'],
                    'name': posted["name"],
                    'sku': posted["sku"]
                }

                if not UnmatchedCategory.objects(tenant_id=data["tenant"], name= posted["name"]).first():
                    UnmatchedCategory(**unmatched_obj).save()

            elif matched_flag:
                matched_obj = {
                    'tenant_id': data["tenant"],
                    'tenant_name': data['tenant_name'],
                    'name': posted["name"],
                    'sku': posted["sku"], 'percentage': matched_words['found'],
                    'category': matched_words['parent_table']
                }

                MatchedCategory(**matched_obj).save()
        return {"status": 200, "data": "Similarity for this categories is done", "category": json.loads(json_util.dumps(matched_category)) }


class SimilarityBox(Resource):

    def post(self):
        from models.box import Box
        from models.supplierBox import SupplierBox
        from models.unmatchedBox import UnmatchedBox
        from models.matchedBox import MatchedBox

        data = request.get_json()

        posted_data = data['boxes']
        for posted in posted_data:

            matched_words = {'found': 0}
            matched_flag = False
            unmatched_flag = True
            posted_slug_name = slugify(posted["name"], to_lower=True)

            standard_data = Box.objects()
            for standard in standard_data:

                found = checkSimilarity(posted_slug_name, standard['slug'])

                if found and found['percentage'] == 100 or posted_slug_name == standard["slug"]:
                    obj = {
                        'tenant_id': data["tenant"],
                        'tenant_name': data['tenant_name'],
                        'name': posted["name"],
                        'display_name': display_name(posted),
                        "system_key": posted["name"],
                        'slug': posted_slug_name,
                        'linked': standard}

                    supplier_box = SupplierBox.objects(tenant_id=data['tenant'], slug=posted_slug_name).first()
                    if supplier_box:
                        supplier_box.update(linked=standard, name=standard["name"])
                    else:
                        supplier_box = SupplierBox(**obj).save()

                    matched_flag = False
                    unmatched_flag = False
                    break

                elif found and 80 <= found['percentage'] < 100:
                    # matched_words.append(category)
                    if found['percentage'] > matched_words['found']:
                        bigest_simi = {
                            'found': found['percentage'], 'parent_table': standard}
                        matched_words = bigest_simi

                    unmatched_flag = False
                    matched_flag = True

            if unmatched_flag:
                unmatched_obj = {'tenant_id': data["tenant"], 'tenant_name': data['tenant_name'],
                                 'name': posted["name"]
                                 }

                try:
                    UnmatchedBox(**unmatched_obj).save()
                except:
                    pass

            elif matched_flag:
                matched_obj = {'tenant_id': data["tenant"], 'tenant_name': data['tenant_name'],
                               'name': posted["name"],
                               'percentage': matched_words['found'],
                               'box': matched_words['parent_table']}

                try:
                    MatchedBox(**matched_obj).save()
                except:
                    pass

        return {"status": 200, "data": "Similarity for this boxes is done"}


class SimilarityOption(Resource):

    def post(self):
        from models.option import Option
        from models.supplierOption import SupplierOption
        from models.unmatchedOption import UnmatchedOption
        from models.matchedOption import MatchedOption

        data = request.get_json()
        posted_data = data['options']
        for posted in posted_data:

            matched_words = {'found': 0}
            matched_flag = False
            unmatched_flag = True
            posted_slug_name = slugify(posted["name"], to_lower=True)

            standard_data = Option.objects()
            for standard in standard_data:
                found = checkSimilarity(posted_slug_name, standard['slug'])

                if found and found['percentage'] == 100 or posted_slug_name == standard["slug"]:
                    obj = {'tenant_id': data["tenant"], 'tenant_name': data['tenant_name'],
                           'name': posted["name"], 'display_name': display_name(posted),
                           'system_key': posted["name"],
                           'slug': posted_slug_name,
                           'start_cost': 0,
                           "linked": standard,
                           "shareable": False,
                           "calculation_method": [],
                           "runs": []
                    }
                    # try:
                    supplier_option = SupplierOption.objects(tenant_id=data['tenant'],
                                                             slug=posted_slug_name).first()
                    if supplier_option:
                        supplier_option.update(linked=standard, name=standard["name"])
                    else:
                        supplier_option = SupplierOption(**obj).save()

                    # except:
                    #     pass

                    matched_flag = False
                    unmatched_flag = False
                    break

                elif found and 80 <= found['percentage'] < 100:
                    # matched_words.append(category)
                    if found['percentage'] > matched_words['found']:
                        bigest_simi = {
                            'found': found['percentage'], 'parent_table': standard}
                        matched_words = bigest_simi

                    unmatched_flag = False
                    matched_flag = True

            if unmatched_flag:
                unmatched_obj = {'tenant_id': data["tenant"], 'tenant_name': data['tenant_name'],
                                 'name': posted["name"]}

                try:
                    UnmatchedOption(**unmatched_obj).save()
                except:
                    pass

            elif matched_flag:
                matched_obj = {'tenant_id': data["tenant"], 'tenant_name': data['tenant_name'],
                               'name': posted["name"],
                               'percentage': matched_words['found'],
                               'option': matched_words['parent_table']}

                try:
                    MatchedOption(**matched_obj).save()
                except:
                    pass

        return {"status": 200, "data": "Similarity for this options is done"}


class SimilarityUtil(Resource):
    def get(self):
        ############################################################
        # try:
        for supplier_category in SupplierCategory.objects():

            try:
                linked_id = supplier_category.linked.id if supplier_category.linked else None
                if linked_id and Category.objects(id=ObjectId(str(linked_id))).count() > 0:
                    continue
                linked = {"linked": None}
                supplier_category.modify(**linked)
                requests.post(
                    "http://assortments:5000/similarity/categories",

                    json={
                        'tenant': supplier_category.tenant_id,
                        'tenant_name': supplier_category.tenant_name,
                        'categories': [{
                            "name": supplier_category.system_key,
                            "sku": ""
                        }]
                    },

                    headers={
                        "Content-type": "application/json"
                    }
                )
            except Exception as e:
                pass  # Ignore missing references safely

        for supplier_box in SupplierBox.objects():
            try:
                linked_id = supplier_box.linked.id if supplier_box.linked else None
                if linked_id and Box.objects(id=ObjectId(str(linked_id))).count() > 0:
                    continue
                linked = {"linked": None}
                supplier_box.modify(**linked)
                requests.post(
                    "http://assortments:5000/similarity/boxes",

                    json={
                        'tenant': supplier_box.tenant_id,
                        'tenant_name': supplier_box.tenant_name,
                        'boxes': [{
                            "name": supplier_box.system_key,
                            "sku": ""
                        }]
                    },

                    headers={
                        "Content-type": "application/json"
                    }
                )
            except Exception as e:
                pass  # Ignore missing references safely

        for supplier_option in SupplierOption.objects():
            try:
                linked_id = supplier_option.linked.id if supplier_option.linked else None
                if linked_id and Option.objects(id=ObjectId(str(linked_id))).count() > 0:
                    continue
                linked = {"linked": None}
                supplier_option.modify(**linked)
                requests.post(
                    "http://assortments:5000/similarity/options",

                    json={
                        'tenant': supplier_option.tenant_id,
                        'tenant_name': supplier_option.tenant_name,
                        'options': [{
                            "name": supplier_option.system_key,
                            "sku": ""
                        }]
                    },

                    headers={
                        "Content-type": "application/json"
                    }
                )
            except Exception as e:
                pass  # Ignore missing references safely
        ##############################################################

        return {"status": 200, "data": "Similarity for categories/boxes/options is done"}
