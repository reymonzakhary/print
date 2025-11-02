from flask import Response, request, jsonify
from flask_restful import Resource
from models.category import Category
from models.box import Box
from models.categoryBoxOption import CategoryBoxOption
from models.supplierBox import SupplierBox
from models.manifest import Manifest
from models.supplierBoops import SupplierBoops
from models.matchedBox import MatchedBox
from slugify import slugify, Slugify, UniqueSlugify


class MergeBoxesApi(Resource):
    def post(self):
        data = request.form.to_dict(flat=True)
        # data = request.get_json()

        new = False \
            if request.args.get('new') is None \
               or request.args.get('new') == "0" \
               or request.args.get('new') == "false" \
            else True

        if new:
            if Box.objects(slug=slugify(data["name"], to_lower=True)).count() == 0:
                box = Box(**{"name": data["name"]}).save()
            else:
                return {
                           "data": None,
                           "message": "Box already exists.",
                           "status": 422
                       }, 200
        else:
            # get existing box
            box = Box.objects(slug=slugify(data["name"], to_lower=True)).first()


        if box:
            for k in data:
                from_box = Box.objects(slug=slugify(data[k], to_lower=True)).first()
                if k == "name" or k == "iso" or not from_box:
                    continue
                else:
                    # get existing box and categories from it
                    categories_from = from_box.categories
                    relation = CategoryBoxOption.objects(box=from_box)
                    if from_box.categories:
                        box.update(add_to_set__categories=categories_from)
                    for rel in relation:
                        rel.update(box=box)
                    
                    supplier_boxes = SupplierBox.objects(linked=from_box)
                    for supplier_box in supplier_boxes:
                        supplier_box.modify(**{"linked": box})

                    Manifest.objects(boops__id=from_box.id).update(pull__boops__id=from_box.id)
                    
                    SupplierBoops._get_collection().update_many(
                        {"boops.linked": from_box.id},
                        {"$set": {"boops.$[boop].linked": box.id}},
                        array_filters=[{"boop.linked": from_box.id}]
                    )
                    
                    MatchedBox.objects(box=from_box).delete()

                    if not new and from_box.name != box.name:
                        from_box.delete()
                    elif new:
                        from_box.delete()


            return {
                       "message": "Boxes has been merged.",
                       "data": None,
                       "status": 200
                   }, 200
        else:
            return {
                       "message": "Box not found.",
                       "data": None,
                       "status": 404
                   }, 200

