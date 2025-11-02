from flask import Response, request, jsonify
from models.option import Option
from flask_restful import Resource
from bson.json_util import dumps
import json
import math
from models.categoryBoxOption import CategoryBoxOption
from models.supplierOption import SupplierOption
##############################
#   handel index and store  #
#############################


class OptionRelationsApi(Resource):
    def get(self,slug):
        page = 1 if request.args.get('page') is None or request.args.get('page') == "" else int(request.args.get('page'))
        per_page = 10 if request.args.get('per_page') is None or request.args.get('per_page') == "" else int(request.args.get('per_page'))
        skip = 0 if page == 1 else (per_page*page) - per_page
        option = Option.objects(slug=slug).first()
        relations = CategoryBoxOption.objects(option=option).aggregate([
                {
                    "$facet": {
                        "data": [
                            
                            { "$skip": skip },
                            { "$limit": per_page},
                            {
                                "$lookup": {
                                    "from": "categories", # Tag collection database name
                                    "foreignField": "_id", # Primary key of the Tag collection
                                    "localField": "category", # Reference field
                                    "as": "category",
                                }
                            },
                            {
                                "$lookup": {
                                    "from": "boxes", # Tag collection database name
                                    "foreignField": "_id", # Primary key of the Tag collection
                                    "localField": "box", # Reference field
                                    "as": "box",
                                }
                            },
                            {
                                "$project": {
                                    "box.categories":0,
                                    "_id":0,
                                    "option":0,
                                    "category._id":0,
                                    "box._id":0
                                }
                            }
                            ,{
                                "$group": {
                                    "_id":1,
                                    "data":{
                                        "$push":{
                                            "category":{
                                                "$first":"$category"
                                             },
                                            "box":{
                                                "$first":"$box"
                                             },
                                        }
                                    }
                                }
                            }
                            
                        ],
                        "count": [{
                            "$count": "count"
                        }]
                    },
                }
            ])
        relations = json.loads(dumps(*relations))
        items = relations['data'][0]['data']
        count = 0 if len(relations['count']) == 0 else json.loads(dumps(*relations['count']))['count']
        last_page = math.ceil(count/per_page)
        next_page = None if last_page <= page else page+1
        first_page = 1
        if len(relations['count']) == 0:
            count =  0
        else:
            count = json.loads(dumps(*relations['count']))['count']

        return {
            "total" : count,
            "per_page" : per_page,
            "current_page" : page,
            "last_page" : math.ceil(count/per_page),
            "first_page_url": "/?page="+str(first_page),
            "last_page_url": "/?page="+str(last_page),
            "next_page_url": "/?page="+str(next_page) if next_page else None,
            "prev_page_url": "/?page="+str(page -1) if page > 1 else None,
            "path": '/',
            "from": skip,
            "to": skip+per_page,
            "data" : items,
        }, 200
