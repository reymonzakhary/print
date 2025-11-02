import uuid

from flask import Response, request, jsonify
from flask_restful import Resource
from models.option import Option
from models.supplierOption import SupplierOption
from models.manifest import Manifest
from models.supplierBoops import SupplierBoops
from slugify import slugify
from models.matchedOption import MatchedOption
from helper.helper import generate_display_names



class MergeOptionsApi(Resource):
    def post(self):
        data = request.form.to_dict(flat=True)

        new = False \
            if request.args.get('new') is None \
               or request.args.get('new') == "0" \
               or request.args.get('new') == "false" \
            else True

        if new:
            if Option.objects(slug=slugify(data["name"], to_lower=True)).count() == 0:
                option = Option(**{"name": data["name"]}).save()
            else:
                return {
                    "data": None,
                    "message": "Option already exists.",
                    "status": 422
                }, 200
        else:
            option = Option.objects(slug=slugify(data["name"], to_lower=True)).first()

        if not option:
            return {
                "message": "Target option not found.",
                "data": None,
                "status": 404
            }, 200

        for k in data:
            from_option = Option.objects(slug=slugify(data[k], to_lower=True)).first()
            if k == "name" or k == "iso" or not from_option:
                continue
            else:

                SupplierOption.objects(linked=from_option.id).modify(**{"linked": option})
                Manifest._get_collection().update_many(
                    {"boops.ops.id": from_option.id},
                    {"$pull": {"boops.$[].ops": {"id": from_option.id}}}
                )
                SupplierBoops._get_collection().update_many(
                    {"boops.ops.linked": from_option.id},
                    {"$set": {"boops.$[].ops.$[op].linked": option.id}},
                    array_filters=[{"op.linked": from_option.id}]
                )
                MatchedOption.objects(option=from_option).delete()

                if not new and from_option.name != option.name:
                    from_option.delete()
                elif new:
                    from_option.delete()

        return {
            "message": "Options have been merged.",
            "data": None,
            "status": 200
        }, 200
