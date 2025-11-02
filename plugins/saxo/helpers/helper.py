import uuid


def getCategorySorting(category_id):
    for s in sortedCategory:
        if s[category_id]:
            return s[sorting]

# skip limit for pagination


def skiplimit(page_size, page_num, table_name):
    """returns a set of documents belonging to page number `page_num`
    where size of each page is `page_size`.
    """
    # Calculate number of documents to skip
    skips = page_size * (page_num - 1)

    # Skip and limit
    cursor = db[table_name].find().skip(skips).limit(page_size)

    # Return documents
    return [x for x in cursor]


# check if obj exist
def ifExists(id):
    if db.assortments.categories.find({"$exists": True}, {"sku": id}):
        return True
    else:
        return False

# generate uuid


def generateUUID():
    generatedUuid = uuid.uuid4()
    return generatedUuid


def checkSimilarity(errorName, standardName):
    # remove spaces
    errorName = str(slugify(errorName.strip(), to_lower=True).replace('-', ''))
    standardName = str(slugify(standardName.strip(),
                               to_lower=True).replace('-', ''))
    # if any word in our db
    # for x in errorName.split():
    #     if(x.strip() == standardName):
    #         return {"percentage": 100, "char": x}
    # convert a new word to chars
    errorNameList = list(errorName)
    standardNameList = list(standardName)

    res = {}

    nameLen = len(errorNameList)
    standardNameLen = len(standardNameList)
    # res["standardNameList"] = standardNameList
    # res["errorNameList"] = errorNameList
    # res["nameLen"] = nameLen
    # res["standardNameLen"] = int("{0:.0f}".format(standardNameLen/1.2))
    count = 0
    char = ""
    errLen = 0
    # if(nameLen > int("{0:.0f}".format(standardNameLen/1.2))):
    for y in errorNameList:
        for c in standardNameList:
            if (c == y):
                Cindex = standardNameList.index(c)
                Yindex = errorNameList.index(y)

                if(Cindex == Yindex):
                    count = count + 2
                else:
                    count = count + 1
                char = char + y + c + ","
                # standardNameList.remove(c)
                standardNameList[Cindex] = ''
                break

    count = int(count/2)
    percentage = int((count/nameLen)*100)
    if (percentage):
        res["errorName"] = errorName
        res["standardName"] = standardName
        res["percentage"] = percentage
        res["char"] = nameLen
        res["count"] = count
        res["err"] = errorName
    # if (res):
        return res
