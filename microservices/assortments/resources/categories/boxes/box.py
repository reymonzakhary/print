from flask import Response, request, jsonify
from models.category import Category
from models.box import Box
from models.matchedBox import MatchedBox
from models.unmatchedBox import UnmatchedBox
from models.supplierBox import SupplierBox
from models.categoryBoxOption import CategoryBoxOption
from flask_restful import Resource
import json
from bson.json_util import dumps
import math
from slugify import slugify, Slugify, UniqueSlugify


##############################
#   handel index and store  #
#############################
class CategoryBoxesApi(Resource):
    def get(self, cat_slug):
        page = 1 if request.args.get('page') is None or request.args.get('page') == "" else int(
            request.args.get('page'))
        per_page = 10 if request.args.get('per_page') is None or request.args.get('per_page') == "" else int(
            request.args.get('per_page'))
        skip = 0 if page == 1 else (per_page * page) - per_page
        filter = "" if request.args.get('filter') is None or request.args.get('filter') == "" else request.args.get(
            'filter')

        categoryData = Category.objects(slug=cat_slug).first()
        if not json.loads(dumps(categoryData)):
            return {
                       "message": "The requested URL was not found on the server. If you entered the URL manually please check your spelling and try again.",
                       "status": 404,
                   }, 404
        else:
            boxes = Box.objects.aggregate([
                {
                    "$match": {
                        "categories": categoryData.id
                    }
                },
                {
                    "$project": {
                        "_id": 0
                    }
                },
                {
                    "$lookup": {
                        "from": "supplier_boxes",  # Tag collection database name
                        "foreignField": "box",  # Primary key of the Tag collection
                        "localField": "_id",  # Reference field
                        "as": "suppliers",
                    },
                },
                {
                    "$lookup": {
                        "from": "matched_boxes",  # Tag collection database name
                        "foreignField": "box",  # Primary key of the Tag collection
                        "localField": "_id",  # Reference field
                        "as": "matches",
                    }
                }
            ])

            boxes = json.loads(dumps(boxes))
            items = boxes
            return {
                       "data": items
                   }, 200

    def post(self, cat_slug):
        body = request.form.to_dict(flat=True)
        #         body = request.get_json()
        category = Category.objects(slug=cat_slug).first()
        if category:
            try:
                box = Box.objects(slug=slugify(body['name'], to_lower=True)).first()
                if not box:
                    box = Box(**body).save()

                box.update(add_to_set__categories=category)
            except:
                return {
                           "message": "This box name already exists.",
                           "status": 422,
                       }, 422
            return jsonify({
                "message": "Box has been created successfully",
                "status": 201,
                "data": box
            })
        else:
            return {
                       "data": None,
                       "message": "Category was not found.",
                       "status": 404
                   }, 404


##############################
#   handel show box          #
#############################
class CategoryBoxApi(Resource):
    def get(self, cat_slug, box_slug):
        req_fields = [
            'sort', 'name', 'slug', 'description',
            'media', 'sqm', 'published', 'input_type', 'created_at'
        ]
        category = Category.objects(slug=cat_slug).first()

        box = Box.objects.only(*req_fields).get_or_404(slug=box_slug, categories=category)
        return jsonify({
            "data": box,
            "status": 200,
            "message": None
        })

    def put(self, cat_slug, box_slug):
        body = request.form.to_dict(flat=True)
        #         body = request.get_json()
        body["slug"] = slugify(body["name"], to_lower=True)
        body = {k: True if v == "1" else False if v == "0" else v for k, v in body.items()}
        category = Category.objects(slug=cat_slug).first()
        box = Box.objects(slug=box_slug, categories=category)
        if box:
            box.update(**body)
            return {
                "message": "box has been updated successfully.",
                "status": 200,
                "data": None
            }
        else:
            return {
                       "message": "box not found.",
                       "status": 404,
                       "data": None
                   }, 404

    def delete(self, cat_slug, box_slug):
        # check if category has relation
        category = Category.objects(slug=cat_slug).first()
        box = Box.objects.get_or_404(slug=box_slug, categories=category)
        box.update(pull__categories=category)
        return {
                   "data": None,
                   "message": "Box has been Unlinked successfully",
                   "status": 200
               }, 200


########################################
############ attach class ##############
########################################
class AttachSupplierBoxApi(Resource):

    def __init__(self):
        self.box = None

    def _process_box_request(self, nature_obj, obj_key, slug, tenant_id):
        nature_obj = nature_obj.objects(slug=slug, tenant_id=tenant_id).first()
        if not nature_obj:
            return {"status": 422, "message": "Box not found",
                    "errors": [{"box": "Invalid box slug or tenant ID."}]}, 200

        changes = {obj_key: self.box}
        SupplierBox.objects(slug=slug, tenant_id=tenant_id).modify(**changes)
        nature_obj.delete()

        return {
            "status": 200,
            "data": {"tenant": tenant_id, "slug": slug},
            "message": "Data has been successfully updated",
        }, 200

    def post(self, slug):
        data = request.form.to_dict(flat=True)
        tenant_id, box_slug = data.get("tenant_id"), data.get("slug")
        self.box = Box.objects(slug=slug).first()

        if not self.box:
            return {
                "status": 422,
                "message": "Box not found",
                "errors": [{"box": "Invalid box slug."}]
            }, 200

        if data.get("type") == "matches":
            return self._process_box_request(MatchedBox, "linked", box_slug, tenant_id)
        elif data.get("type") == "unmatched":
            return self._process_box_request(UnmatchedBox, "linked", box_slug, tenant_id)
        elif data.get("type") == "suppliers":
            SupplierBox.objects(slug=box_slug, tenant_id=tenant_id).update(linked=self.box)
            return {
                "status": 200,
                "data": {"tenant": tenant_id, "slug": box_slug},
                "message": "Supplier box has been attached successfully to box.",
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
class DetachSupplierBoxApi(Resource):
    def post(self, slug):

        data = request.form.to_dict(flat=True)
        #         data = request.get_json()
        # get slug of category
        box = Box.objects(slug=slug).first()

        try:
            suppliers = SupplierBox.objects(slug=data["slug"], tenant_id=data["tenant_id"]).first()
            sp = {
                "name": suppliers['name'],
                "tenant_id": suppliers['tenant_id'],
                "tenant_name": suppliers['tenant_name'],
                "sku": suppliers['sku'],
                "description": suppliers['description'],
                "published": suppliers['published']
            }

            UnmatchedBox(**sp).save()

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
        except:
            return {"message": "Category was not found", "status": 404}, 404


class AttachBoxToCategoryApi(Resource):
    def post(self, cat_slug, box_slug):
        data = request.form.to_dict(flat=True)
        #         data = request.get_json()
        category = Category.objects(slug=cat_slug).first()
        toCategory = Category.objects(slug=data["slug"]).first()
        box = Box.objects.get_or_404(slug=box_slug, categories=category)
        relation = CategoryBoxOption.objects(category=category, box=box)
        if toCategory:
            box.update(pull__categories=category)
            box.update(add_to_set__categories=toCategory)
            for rel in relation:
                if CategoryBoxOption.objects(category=toCategory, box=box, option=rel['option']).count() == 0:
                    rel.update(category=toCategory)
                else:
                    data = CategoryBoxOption.objects(category=category, box=box, option=rel['option']).delete()

            return {
                       "message": "Box has been moved to new category.",
                       "status": 200
                   }, 200
        else:
            return {
                       "message": "Category was not found",
                       "status": 404
                   }, 404
