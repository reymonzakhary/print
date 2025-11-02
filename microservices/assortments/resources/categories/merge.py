import uuid

from flask import Response, request, jsonify
from flask_restful import Resource
from models.category import Category
from models.box import Box
from models.categoryBoxOption import CategoryBoxOption
from models.supplierCategory import SupplierCategory
from models.supplierBoops import SupplierBoops
from slugify import slugify, Slugify, UniqueSlugify
from helper.helper import generate_display_names
from models.manifest import Manifest
from models.matchedCategory import MatchedCategory

##############################
#   handel index and store  #
#############################
class MergeCategoriesApi(Resource):
    def post(self):
        data = request.form.to_dict(flat=True)
        #         data = request.get_json()
        new = False \
            if request.args.get('new') is None \
               or request.args.get('new') == "0" \
               or request.args.get('new') == "false" \
            else True

        if new:
            if Category.objects(slug=slugify(data["name"], to_lower=True)).count() == 0:
                sc = {
                    "name": data['name'],
                    "system_key": slugify(data['name'], to_lower=True),
                    "display_name": generate_display_names(data['name']),
                    "sku": str(uuid.uuid4()),
                    "description": None,
                    "media": [],
                    "published": True
                }

                category = Category(**sc).save()
            else:
                return {
                    "data": None,
                    "message": "Category already exists.",
                    "status": 422
                }, 200
        else:
            category = Category.objects(slug=slugify(data["name"], to_lower=True)).first()

        for k in data:
            if k == "name" or k == "iso":
                continue
            else:
                from_category = Category.objects(slug=slugify(data[k], to_lower=True)).first()
                boxes = Box.objects(categories=from_category)
                supplier_category = SupplierCategory.objects(linked=from_category)
                supplier_boops = SupplierBoops.objects(linked=from_category)
                lk = {"linked": category}
                if supplier_category:
                    for sub_cat in supplier_category:
                        sub_cat.modify(**lk)

                if supplier_boops:
                    for sub_cat in supplier_boops:
                        sub_cat.modify(**lk)

                if from_category:
                    for box in boxes:
                        box.update(pull__categories=from_category)
                        box.update(add_to_set__categories=category)
                        relation = CategoryBoxOption.objects(category=from_category, box=box)
                        for rel in relation:
                            if CategoryBoxOption.objects(category=category, box=box, option=rel['option']).count() == 0:
                                rel.update(category=category)

                    manifests = Manifest.objects(category=from_category)
                    for manifest in manifests:
                        manifest.update(category=category)

                    MatchedCategory.objects(category=from_category).delete()

                    if not new and from_category.name != category.name:
                        from_category.delete()
                    elif new:
                        from_category.delete()

        return {
            "message": "Categories has been merged.",
            "data": None,
            "status": 200
        }, 200
