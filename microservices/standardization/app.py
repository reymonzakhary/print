import os

from flask import Flask, jsonify, request
from flask_restful import Api, Resource
from pymongo import MongoClient

import bcrypt
import spacy



app = Flask(__name__)
api = Api(app)


# init db
client = MongoClient("mongodb://admin:ad@mongodb:27017")
db = client.standard

objectTemp = db["objectTemp"]
standardNames = db["standardNames"]
standardObjects = db["standardObjects"]

# check if obj exist


def ObjExist(name):
    if objectTemp.find({"name": name}).count() == 0:
        return False
    else:
        return True


def CheckSimilarity(errorName, standardName):

    # remove spaces
    errorName = errorName.strip()

    # if any word in our db
    for x in errorName.split():
        if(x.strip() == standardName):
            return {"percentage": 100, "char": x}

    # convert a new word to chars
    errorNameList = list(errorName)

    res = {}
    nameLen = len(errorNameList)

    count = 0
    char = ''
    for y in errorNameList:
        if (y in standardName):
            count = count + 1
            char = char + y + ','

    percentage = (count/nameLen)*100

    if (percentage):
        res["percentage"] = percentage
        res["char"] = char

    if (res):
        return res


class CrudObj(Resource):
    def post(self):
        obj = request.get_json()

        # Get name from obj
        name = obj["name"]

        if ObjExist(name):
            retJson = {
                'status': 301,
                'msg': 'Object Exist'
            }
            return jsonify(retJson)

        # get all similarity
        # import spacy
        # nlp = spacy.load('en_core_web_sm')

        similarity = []

        # step 1 get all standard data
        for standard in standardNames.find({}, {"_id": 0}):
            # step 2 check similarity

            # spacy nlp
            # errorname = nlp(name)
            # standardname = nlp(standard['name'])
            # ratio = errorname.similarity(standardname)
            # spacy nlp

            # custom similarity
            similarityRes = CheckSimilarity(name, standard['name'])

            if(similarityRes and similarityRes["percentage"] > 70):
                similarity.append(
                    {"key": standard['key'], "name": standard['name'], "percentage": similarityRes["percentage"], "similarity_chars": similarityRes["char"]})

                # step 3 push similarity into the obj
                obj["similarity"] = similarity

        # step 4 Store obj into the database
        objectTemp.insert({
            "name": name,
            "obj": obj
        })
        retJson = {
            "status": 200,
            "msg": "You successfully add the obj"
        }
        return jsonify(retJson)

    def get(self):
        objs = []
        for obj in objectTemp.find({}, {"_id": 0}):
            objs.append(obj)

        retJson = {
            "status": 200,
            "message": objs
        }
        return jsonify(retJson)


class standard(Resource):
    def post(self):
        obj = request.get_json()

        # Get name from obj
        name = obj["name"]
        key = obj["key"]
        tenant = obj["tenant"]

        objectTempColl = objectTemp.find({"name": name})
        for x in objectTempColl:
            objectTempCollObj = x['obj']

        standardNameColl = standardNames.find({"key": key})

        if (objectTempColl.count() != 0 and standardNameColl.count() != 0 and objectTempCollObj['tenant'] == tenant):

            standardKey = standardObjects.find({"key": key})
            for x in standardKey:
                standardKeyObj = x['namesObject']

            if (standardKey.count() != 0):
                # update relation
                standardKeyObj.append(
                    {"tenant": tenant, "name": name})
                standardObjects.update_one(
                    {"key": key}, {"$set": {"namesObject": standardKeyObj}})
            else:
                # insert new relation
                standardObjects.insert({
                    "key": key,
                    "namesObject": [{"tenant": tenant, "name": name}]
                })

            # delete obj from temp
            objectTemp.delete_one({"name": name})

            retJson = {
                'status': 200,
                'msg': 'Done'
            }
            return jsonify(retJson)

        else:
            retJson = {
                'status': 301,
                'msg': 'Wrong parameters'
            }
            return jsonify(retJson)

    def get(self):
        objs = []
        for obj in standardObjects.find({}, {"_id": 0}):
            objs.append(obj)

        retJson = {
            "status": 200,
            "message": objs
        }
        return jsonify(retJson)


api.add_resource(CrudObj, '/object')
api.add_resource(standard, '/standard')

if __name__ == "__main__":
    ENVIRONMENT_DEBUG = os.environ.get("APP_DEBUG", True)
    ENVIRONMENT_PORT = os.environ.get("APP_PORT", 5000)
    app.run(host='0.0.0.0', port=ENVIRONMENT_PORT,
            debug=ENVIRONMENT_DEBUG)
