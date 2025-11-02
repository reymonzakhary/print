from flask import Response, request, jsonify
from models.supplierProduct import SupplierProduct
from models.supplierCategory import SupplierCategory
from models.matchedCategory import MatchedCategory
from models.unmatchedCategory import UnmatchedCategory
from models.categoryBoxOption import CategoryBoxOption
from models.box import Box
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource, fields, marshal
from bson.json_util import dumps
import json
import datetime
import math
import requests


##############################
#   handel index and store  #
#############################
class SupplierCategoyExportApi(Resource):
    def post(self, supplier_id, slug):
        data = request.get_json()
        products = SupplierProduct.objects(tenant_id=supplier_id, category_slug=slug).aggregate(
            [
                {
                    "$project": {"_id": 0, "tenant_name": 0, "tenant_id": 0, "created_at": 0, "linked": 0}
                }
            ]
        )
        products = json.loads(dumps(products))
        list = []
        # generate cols
        cols = [
            "category_display_name",
            "shareable",
            "published",
        ]
        outcols = cols[:]
        # return products
        for product in products:

            row = []
            # complete cols 
            for col in cols:
                if col in outcols:
                    if col == "category_display_name":
                        row.append(self.getDisplayname(product[col], data['lang']))
                    else:
                        row.append(product[col])
            for obj in product['object']:
                if self.getDisplayname(obj['display_key'], data['lang']) not in cols:
                    cols.append(self.getDisplayname(obj['display_key'], data['lang']))
                row.append(self.getDisplayname(obj['display_value'], data['lang']))
            row.append("")
            row.append("")
            row.append("")
            row.append("")
            row.append("")
            row.append("")
            row.append("")
            row.append("")
            list.append(row)

        cols.append("printing_method")
        cols.append("dlv_type")
        cols.append("dlv_days")
        cols.append("qty")
        cols.append("price")
        cols.append("incremental_by")
        cols.append("from")
        cols.append("to")
        # return cols
        data = {"path": f"{supplier_id}/exports/{slug}.xlsx", "cols": cols, "data": list}
        # return data
        url = 'http://filemanager:5000/excel/create'
        header = {"Content-type": "application/json"}
        file = requests.post(url, json=data, headers=header)
        return file.json()

    def getDisplayname(self, data, lang):

        if not type(data) == list:
            return data
        else:
            dname = list(filter(lambda x: (x['iso'] == lang), data))
            if dname:
                return dname[0]['display_name']
            else:
                return data[0]['display_name']
