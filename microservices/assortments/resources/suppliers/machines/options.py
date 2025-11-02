from flask import Response, request, jsonify
from models.supplierOption import SupplierOption
from models.supplierBoops import SupplierBoops
from models.option import Option
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource, fields, marshal
from bson.json_util import dumps
import json
import datetime
import math
import requests
from models.supplierOption import SupplierOption
from bson.objectid import ObjectId


##############################
#   handel index and store  #
#############################
class SupplierMachineOptionsApi(Resource):
    def get(self, supplier, machine):
        options = SupplierOption.objects(tenant_id = supplier).aggregate([
            {
                "$match": {
                    "sheet_runs.machine": {
                        "$eq": ObjectId(machine)
                    }
                }
            }
        ])

        return {'data': json.loads(dumps(options)), 'status': 200}, 200

