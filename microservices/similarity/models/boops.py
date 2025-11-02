class Boops(Resource):
    def get(self, category_slug):
        # get data from request
        data = request.get_json()
        # options documents
        optionList = options.find(
            {"relationships": {"$elemMatch": {"category": category_slug}}})
​
        oldBox = ''
        newBox = ''
        boops = []
​
        # loop in options documents
        for option in optionList:
            # for every box get options
            newBox = option["relationships"][0]["box"]
            if (oldBox != newBox):
                opts = []
                boops.append({"name": newBox, "options": opts})
                oldBox = newBox
​
            opts.append(
                {"name": option["name"], "slug": option["slug"], "excludes": []})
​
        optionsList = dumps({"supplier_id": 'tenantID',
                             "category_slug": category_slug, "boops": boops})
​
        return json.loads(optionsList)

