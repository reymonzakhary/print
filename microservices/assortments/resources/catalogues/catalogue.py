from flask import Response, request, jsonify
from models.catalogue import Catalogue
from models.option import Option
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource, fields, marshal
from bson.json_util import dumps
import json
import datetime
import math


##############################
#   handle index and store  #
#############################
class CatalogueApi(Resource):
    def get(self):
        page = 1 if request.args.get('page') is None or request.args.get('page') == "" else int(
            request.args.get('page'))
        per_page = 10 if request.args.get('per_page') is None or request.args.get('per_page') == "" else int(
            request.args.get('per_page'))
        skip = 0 if page == 1 else (per_page * page) - per_page
        filters = "" if request.args.get('filter') is None or request.args.get('filter') == "" else request.args.get(
            'filter')
        sort_by = "name" if request.args.get('sort_by') is None or request.args.get(
            'sort_by') == "" else request.args.get('sort_by')
        sort_dir = "" if request.args.get('sort_dir') is None or request.args.get('sort_dir') == "" or request.args.get(
            'sort_dir') == "asc" else "-"

        catalogues = Catalogue.objects.order_by(sort_dir + sort_by).aggregate([
#             {
#                 "$match": {
#                     "name": {
#                         "$regex": filters,
#                         "$options": 'i'  # case-insensitive
#                     }
#                 }
#             },

            {
                "$facet": {
                    "data": [
#                         {
#                             "$match": {"name": {
#                                 "$regex": filters,
#                                 "$options": 'i'  # case-insensitive
#                             }
#                             }
#                         },
                        {"$skip": skip},
                        {"$limit": per_page},
                        {
                            "$lookup": {
                                "from": "options",  # Tag collection database name
                                "foreignField": "_id",  # Primary key of the Tag collection
                                "localField": "grs",  # Reference field
                                "as": "grs",
                            },
                        },
                        {
                            "$lookup": {
                                "from": "options",  # Tag collection database name
                                "foreignField": "_id",  # Primary key of the Tag collection
                                "localField": "material",  # Reference field
                                "as": "materials",
                            }
                        },
                    ],
                    "count": [{
                        "$count": "count"
                    }]
                },
            }
        ])
        catalogues = json.loads(dumps(*catalogues))
        items = catalogues['data']
        if len(catalogues['count']) == 0:
            count = 0
        else:
            count = json.loads(dumps(*catalogues['count']))['count']

        last_page = math.ceil(count / per_page)
        next_page = None if last_page <= page else page + 1
        first_page = 1

        return {
                "pagination": {
                    "total": count,
                    "per_page": per_page,
                    "current_page": page,
                    "last_page": math.ceil(count / per_page),
                    "first_page_url": "/?page=" + str(first_page),
                    "last_page_url": "/?page=" + str(last_page),
                    "next_page_url": "/?page=" + str(next_page) if next_page else None,
                    "prev_page_url": "/?page=" + str(page - 1) if page > 1 else None,
                    "path": '/',
                    "from": skip,
                    "to": skip + per_page,
                },
                "data": items,
           }, 200


    def post(self):
        body = request.form.to_dict(flat=True)
        #         body = request.get_json()
        additional = {} if 'additional' not in body else body['additional']
        material = Option.objects(slug=body['material']).first()
        grs = Option.objects(slug=body['grs']).first()

        if material is None:
            return jsonify({
                "message": 'We couldn\'t find a material with that name',
                'status': 422
            })

        if grs is None:
            return jsonify({
                "message": 'We couldn\'t find a grs with that name',
                'status': 422
            })
        if Catalogue.objects(material=material, grs=grs).first():
            return jsonify({
                "message": 'The catalogue already exists.',
                'status': 422
            })

        catalogue = Catalogue(
            material=material,
            grs=grs,
            additional= additional
        ).save()

        return jsonify({
            "message": "Catalogue has been created successfully",
            "status": 201,
            "data": catalogue
        })
