from flask import Response, request, jsonify
from models.category import Category
from models.supplierCategory import SupplierCategory
from models.matchedCategory import MatchedCategory
from models.unmatchedCategory import UnmatchedCategory
from models.categoryBoxOption import CategoryBoxOption
from models.manifest import Manifest
from models.supplierBoops import SupplierBoops
from models.box import Box
from slugify import slugify, Slugify, UniqueSlugify
from flask_restful import Resource, fields, marshal
from bson.json_util import dumps
import json
import datetime
import math

from helper.helper import generate_display_names


##############################
#   handel index and store  #
#############################
class CategoriesApi(Resource):
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

        categories = Category.objects.order_by(sort_dir + sort_by).aggregate([
            {
                "$match": {
                    "name": {
                        "$regex": filters,
                        "$options": 'i'  # case-insensitive
                    }
                }
            },

            {
                "$facet": {
                    "data": [
                        {
                            "$match": {"name": {
                                "$regex": filters,
                                "$options": 'i'  # case-insensitive
                            }
                            }
                        },
                        {"$skip": skip},
                        {"$limit": per_page},
                        {
                            "$lookup": {
                                "from": "supplier_categories",  # Tag collection database name
                                "foreignField": "linked",  # Primary key of the Tag collection
                                "localField": "_id",  # Reference field
                                "as": "suppliers",
                            },
                        },
                    ],
                    "count": [{
                        "$count": "count"
                    }]
                },
            }
        ])
        categories = json.loads(dumps(*categories))
        items = categories['data']
        if len(categories['count']) == 0:
            count = 0
        else:
            count = json.loads(dumps(*categories['count']))['count']

        last_page = math.ceil(count / per_page)
        next_page = None if last_page <= page else page + 1
        first_page = 1

        return {
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
            "data": items,
        }, 200

    def post(self):
        body = request.form.to_dict(flat=True)
        #         body = request.get_json()
        name = body['name']
        existing_category = Category.objects(slug=slugify(name , to_lower=True)).first()
        if existing_category:
            return jsonify({
                "message": "Category already exists",
                "status": 422,
            })

        display_names = []
        for iso in ['en', 'nl', 'fr', 'de']:
            display_names.append({
                "display_name": name,
                "iso": iso
            })
        # return {"dddd" : "dddd"}, 400
        category = Category(
            name=name,
            system_key=slugify(name, to_lower=True),
            display_name=display_names
        ).save()
        return jsonify({
            "message": "Category created successfully",
            "status": 201,
            "data": category
        })

    def put(self):
        pass


########################################
############ update delete class #######
########################################
class CategoryApi(Resource):
    def get(self, slug):
        category = Category.objects.get_or_404(slug=slug)
        return jsonify({
            "data": category,
            "status": 200,
            "message": None
        })

    def put(self, slug):

        body = request.form.to_dict(flat=True)

        # Convert boolean fields properly
        body["published"] = body.get("published") == "1"
        body["checked"] = body.get("checked") == "1"

        # Rebuild the display_name list
        display_name = []
        index = 0

        while f"display_name[{index}][iso]" in body:
            display_name.append({
                "iso": body[f"display_name[{index}][iso]"],
                "display_name": body[f"display_name[{index}][display_name]"]
            })
            index += 1

        # Remove all keys that start with "display_name[" dynamically
        update_query = {
            f"set__{k}": v
            for k, v in body.items()
            if not k.startswith("display_name[") and k != "slug"
        }

        # Add display_name update separately as a whole list
        update_query["set__display_name"] = display_name

        # Perform the update
        Category.objects(slug=slug).update_one(**update_query)

        return {
            "data": None,
            "status": 200,
            "message": "Category has been updated successfully",
        }, 200
        return {}
        body = request.form.to_dict(flat=True)  # Convert form data to dictionary
        iso_code = body.pop("iso", None)  # Extract ISO code
        new_display_name = body.pop("display_name", None)  # Extract new display_name value

        display_name = []
        for d_name in new_display_name:
            print(d_name)
            # display_name.append({"iso": iso_code, "display_name": d_name})
        update_query = {}

        # Convert 'published' and 'checked' to booleans correctly
        if "published" in body:
            body["published"] = str(body["published"]).strip().lower() in ["true", "1", "yes"]

        if "checked" in body:
            body["checked"] = str(body["checked"]).strip().lower() in ["true", "1", "yes"]

        # Find existing category
        existing_category = Category.objects(slug=slug).first()
        if not existing_category:
            return {"status": 404, "message": "Category not found"}, 404

        # Ensure display_name is a list before updating
        if not isinstance(existing_category.display_name, list):
            existing_category.display_name = []

        # Try to find the index of the entry that matches the ISO code
        update_display_name = False
        for index, entry in enumerate(existing_category.display_name):
            if entry.get("iso") == iso_code:
                update_display_name = True
                update_query[f"set__display_name__{index}__display_name"] = new_display_name
                break

        # If no existing ISO entry, append a new one
        if not update_display_name and iso_code and new_display_name:
            update_query["push__display_name"] = {"iso": iso_code, "display_name": new_display_name}

        # Dynamically update other fields
        for key, value in body.items():
            update_query[f"set__{key}"] = value

        print(f"Final update query: {update_query}")  # Debugging output

        # Apply update only if there is something to update
        if update_query:
            Category.objects(slug=slug).update_one(**update_query)

        return {
            "data": None,
            "status": 200,
            "message": "Category has been updated successfully",
        }, 200

    def delete(self, slug):
        # check if category has relation
        force = not (request.args.get('force') is None or request.args.get('force') in ["0", "false", "False"])
        category = Category.objects.get_or_404(slug=slug)

        if force:
            linked = {"linked": None}
            SupplierCategory.objects(linked=category.id).modify(**linked)
            SupplierBoops.objects(linked=category.id).modify(**linked)
            boxes = Box.objects(categories=category)
            for box in boxes:
                box.update(pull__categories=category)

        if Box.objects(categories=category).count() == 0 and SupplierCategory.objects(
                linked=category['id']).count() == 0:
            CategoryBoxOption.objects(category=category).update(category=None)
            Manifest.objects(category=category).delete()
            MatchedCategory.objects(category=category).delete()

            category.delete()
            return {
                "data": None,
                "message": "Category has been deleted successfully",
                "status": 200
            }, 200
        else:
            return {
                "data": None,
                "message": "We can't remove this category because it is linked to boxes and suppliers.",
                "status": 200
            }, 200


########################################
############ attach class ##############
########################################
class AttachSupplierCategoryApi(Resource):

    def __init__(self):
        self.category = None

    def _process_category_request(self, nature_obj, obj_key, slug, tenant_id):
        nature_obj = nature_obj.objects(slug=slug, tenant_id=tenant_id).first()
        changes = {obj_key: self.category}
        SupplierCategory.objects(slug=slug, tenant_id=tenant_id).modify(**changes)
        nature_obj.delete()
        return {
            "status": 200,
            "data": {
                "tenant": tenant_id,
                "slug": slug,
            },
            "message": "data has been successfully updated",
        }, 200

    def post(self, slug):
        data = request.form.to_dict(flat=True)
        tenant_id, category_slug = data["tenant_id"], data["slug"]
        self.category = Category.objects(slug=slug).first()

        if data["type"] == "matches":
            return self._process_category_request(MatchedCategory, "linked", category_slug, tenant_id)
        elif data['type'] == "unmatched":
            try:
                return self._process_category_request(UnmatchedCategory, "linked", category_slug, tenant_id)
            except Exception as e:
                return {
                    "status": 422,
                    "message": "The given data was invalid.",
                    "errors": [{
                        "category": e
                    }]
                }, 422

        elif data['type'] == "suppliers":
            SupplierCategory.objects(slug=category_slug, tenant_id=tenant_id).update(linked=self.category)
            return {
                "status": 200,
                "data": {
                    "tenant": tenant_id,
                    "slug": category_slug,
                },
                "message": "Supplier category has been attached successfully to category.",
            }, 200
        else:
            return {
                "status": 422,
                "message": "The given data was invalid.",
                "errors": [{
                    "type": ['The type key field is required or not found.']
                }]
            }, 422


########################################
############ detach class ##############
########################################
class DetachSupplierCategoryApi(Resource):
    def post(self, slug):

        data = request.form.to_dict(flat=True)
        #         data = request.get_json()
        # get slug of category
        category = Category.objects(slug=slug).first()

        try:
            suppliers = SupplierCategory.objects(slug=data["slug"], tenant_id=data["tenant_id"],linked=category.id).first()
            sp = {
                "name": suppliers['name'],
                "tenant_id": suppliers['tenant_id'],
                "tenant_name": suppliers['tenant_name'],
                "sku": suppliers['sku'],
                "description": suppliers['description'],
                "media": suppliers['media'],
                "published": suppliers['published']
            }
            linked = {"linked": None}

            UnmatchedCategory(**sp).save()
            suppliers.modify(**linked)

            return {
                "status": 200,
                "data": {
                    "tenant": data["tenant_id"],
                    "slug": data["slug"],
                },
                "message": "Supplier category has been detached successfully",
            }, 200

        except:
            return {
                "status": 404,
                "message": "Page not found!",
                "errors": [{
                    "category": ['Entry for category was not found.']
                }]
            }, 404


class ProductCalculationApi(Resource):
    def post(self, slug):
        data = request.get_json()
        base = data['base']
        qty = data['qty']
        size = data['format']
        grams = data['wight']
        colors = data['printing']
        file = []
        sizes = {}
        materials = {}
        printing = {}
        wight = {}
        ref = {}
        with open('./data.json') as json_file:
            file = json.load(json_file)
            # Print the type of data variable
        for key in file:
            if key.get('Format'):
                sizes = key.get('Format')
            elif key.get('Materials'):
                materials = key.get('Materials')
            elif key.get('wight'):
                wight = key.get('wight')
            elif key.get('printing'):
                printing = key.get('printing')
            elif key.get('ref'):
                ref = key.get('ref')

        found = {}

        for f in sizes:
            if slugify(size, to_lower=True) == f.get('slug'):
                found = f
                break
            else:
                continue

        symbol = str("€ ")
        if found:
            # calculate the dia
            w = found.get('format').get('mm').get('width')
            h = found.get('format').get('mm').get('height')
            height_with_bleed = (h + (base.get('bleed') * 2))
            width_with_bleed = (w + (base.get('bleed') * 2))

            yyv = base.get('h') % height_with_bleed
            yxv = base.get('h') % width_with_bleed

            xyv = base.get('w') % height_with_bleed
            xxv = base.get('w') % width_with_bleed

            ###### new in positioning ######

            ## landscape

            # Example usage
            width = base.get('w')  # Width of the larger sheet in mm
            height = base.get('h')  # Height of the larger sheet in mm
            # A4 dimensions in mm
            a4_width = width_with_bleed
            a4_height = height_with_bleed

            # Fit in pure portrait orientation
            fit_portrait_width = width // a4_width
            fit_portrait_height = height // a4_height
            total_fit_portrait = fit_portrait_width * fit_portrait_height

            # Fit in pure landscape orientation
            fit_landscape_width = width // a4_height
            fit_landscape_height = height // a4_width
            total_fit_landscape = fit_landscape_width * fit_landscape_height

            # Mixed orientations: portrait along width, landscape along remaining height
            fit_mixed1_width = width // a4_width
            remaining_height1 = height - fit_mixed1_width * a4_height
            fit_mixed1_height_landscape = remaining_height1 // a4_width
            total_fit_mixed1_portrait = fit_portrait_height * fit_mixed1_width
            total_fit_mixed1_landscape = fit_mixed1_height_landscape * fit_mixed1_width
            total_fit_mixed1 = total_fit_mixed1_portrait + total_fit_mixed1_landscape

            # Mixed orientations: landscape along width, portrait along remaining height
            fit_mixed2_width = width // a4_height
            remaining_height2 = height - fit_mixed2_width * a4_width
            fit_mixed2_height_portrait = remaining_height2 // a4_height
            total_fit_mixed2_landscape = fit_landscape_height * fit_mixed2_width
            total_fit_mixed2_portrait = fit_mixed2_height_portrait * fit_mixed2_width
            total_fit_mixed2 = total_fit_mixed2_landscape + total_fit_mixed2_portrait

            results = [
                {
                    'orientation': 'pure portrait',
                    'portrait_count': total_fit_portrait,
                    'landscape_count': 0,
                    'total_fit': total_fit_portrait
                },
                {
                    'orientation': 'pure landscape',
                    'portrait_count': 0,
                    'landscape_count': total_fit_landscape,
                    'total_fit': total_fit_landscape
                },
                {
                    'orientation': 'mixed: portrait width, landscape height',
                    'portrait_count': total_fit_mixed1_portrait,
                    'landscape_count': total_fit_mixed1_landscape,
                    'total_fit': total_fit_mixed1
                },
                {
                    'orientation': 'mixed: landscape width, portrait height',
                    'portrait_count': total_fit_mixed2_portrait,
                    'landscape_count': total_fit_mixed2_landscape,
                    'total_fit': total_fit_mixed2
                }
            ]

            results = sorted(results, key=lambda x: x['total_fit'])

            for result in results:
                orientation = result['orientation']
                portrait_count = result['portrait_count']
                landscape_count = result['landscape_count']
                total_fit = result['total_fit']
                print(f"Orientation: {orientation}")
                print(f"Number of A4 sheets (portrait): {portrait_count}")
                print(f"Number of A4 sheets (landscape): {landscape_count}")
                print(f"Total number of A4 sheets: {total_fit}")
                print("---")
            return sorted(results, key=lambda x: x['total_fit'])
            # return best_orientation, max_fit

            landscape_w_or = round(base.get('w') // width_with_bleed)
            landscape_h_or = round(base.get('h') // height_with_bleed)
            landscape_amount = landscape_w_or * landscape_h_or
            print(landscape_w_or, landscape_h_or, landscape_amount)
            ################################
            # calculate floor numbers left cross
            # height calculation
            calc_hh = math.floor(base.get('h') / height_with_bleed)
            calc_hw = math.floor(base.get('h') / width_with_bleed)
            # width calculation
            calc_wh = math.floor(base.get('w') / height_with_bleed)
            calc_ww = math.floor(base.get('w') / width_with_bleed)

            cross_1 = calc_hh * calc_ww
            cross_2 = calc_hw * calc_wh
            # maxi is the amount of prints on one page
            maxi = max(cross_1, cross_2)
            c_landscape = 0
            c_portrait = 0

            if yyv >= width_with_bleed:
                c_landscape = cross_1 + calc_wh

            if xxv >= width_with_bleed:
                c_portrait = cross_2 + calc_wh

            if yxv >= width_with_bleed:
                c_portrait = cross_2 + calc_wh
            if xyv >= width_with_bleed:
                c_portrait = cross_1 + calc_wh

            mini = min(cross_1, cross_2)
            position = {}

            if c_landscape > maxi:
                position = {
                    "Maximum prints on one sheet": c_landscape,
                    "Landscape": c_landscape - mini,
                    "Portrait": mini,
                    "Amount of sheets needed": qty / c_landscape
                }
            elif c_portrait > maxi:
                position = {
                    "Maximum prints on one sheet": c_portrait,
                    "Portrait": c_portrait - mini,
                    "Landscape": mini,
                    "Amount of sheets needed": qty / c_portrait
                }

            if maxi == cross_1:
                ps = "Portrait"
            elif maxi == cross_2:
                ps = "Landscape"
            # total papers to get 1kg
            amount_p_k = 1000 / grams  # in 1sqm
            # cost for one sheet
            price_per_vel = base.get('price_per_kilo') / amount_p_k
            # sheen in sqm  used
            square_mt = ((base.get('w') * base.get('h')) / 1000) / 1000
            # sheet cost
            paper_mt_price = price_per_vel * square_mt
            # amount of sheets needed
            amount_of_paper_needed = qty / maxi

            reference = {}
            tik_price = 0
            for c in printing:
                if c.get('slug') == slugify(colors, to_lower=True):
                    reference = c

            for r in ref:
                fr = 0
                to = 0
                if r.get('key') == reference.get('ref'):
                    for t in r.get('run'):
                        if qty in range(t.get('from'), t.get('to')):
                            tik_price = t.get('price') / 100
                        elif qty not in range(t.get('from'), t.get('to')) and t.get('from') <= qty and t.get('to') == 0:
                            tik_price = t.get('price') / 100

            if tik_price == 0:
                return {"message": "The amount you need is not available", "status": 404}, 404

            add_on = base.get('add_on')
            price = (amount_of_paper_needed * paper_mt_price) + (
                    amount_of_paper_needed * tik_price) + base.get('s_price')
            if add_on:
                for p in add_on:
                    price += add_on[p]

            price_per_cut = paper_mt_price / maxi

            # return total
            return {
                "price_per_vel": '%s%s' % (symbol, price_per_vel),
                "paper_mt_price": '%s%s' % (symbol, paper_mt_price),
                "square_mt": square_mt,
                "amount_of_paper_needed": amount_of_paper_needed,
                "Print position": {
                    "Maximum prints on one sheet": maxi,
                    "Amount of sheets needed": round(amount_of_paper_needed),
                    "Print position": ps,
                    "option": position
                },
                "Sheet": {
                    "Print position": ps,
                    "Maximum prints on one sheet": maxi,
                    "Amount of sheets needed": round(amount_of_paper_needed),
                    "Used in Sqm": round(square_mt, 2),
                    "sheets cost": round(round(paper_mt_price, 2) * round(amount_of_paper_needed), 2),
                    "Cost price per sqm m2": '%s%s' % (symbol, round(price_per_vel, 2)),
                    "Sheet cost price": '%s%s' % (symbol, round(paper_mt_price, 2)),
                    "per tik price": '%s%s' % (symbol, tik_price),
                    "total tik cost": '%s%s' % (symbol, amount_of_paper_needed * tik_price),
                    "start price": '%s%s' % (symbol, base.get('s_price')),
                },
                "display_price": '%s%s' % (symbol, round(price, 2)),
                "width": w,
                "height": h,
                "Wight": grams,
                "Colors": colors,
                "qty": qty
            }, 200

        return {"message": "Item not found", "status": 404}, 404
