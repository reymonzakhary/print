# import os
from os import environ, path
import pymongo
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

# MongoDB Connection Details
MONGO_URI = environ.get("MONGO_URI")
DB_NAME = environ.get("DB_NAME")

def get_mongo_connection():
    """Establish a MongoDB connection with connection pooling."""
    try:
        client = pymongo.MongoClient(MONGO_URI, serverSelectionTimeoutMS=5000)
        client.admin.command("ping")  # Check connection
        print("✅ Connected to MongoDB")
        return client
    except pymongo.errors.ServerSelectionTimeoutError:
        print("❌ Failed to connect to MongoDB. Check URI and MongoDB server.")
        exit(1)
