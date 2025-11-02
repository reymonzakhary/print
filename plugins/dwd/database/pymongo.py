from pymongo import MongoClient

# init db connection
client = MongoClient()


def initialize_pymongo(uri):
    client = MongoClient(uri)
