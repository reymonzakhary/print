from flask_mongoengine import BaseQuerySet, MongoEngine
from mongoengine import Document, StringField, BooleanField, ListField, ReferenceField, EmbeddedDocument, EmbeddedDocumentField, DictField, FloatField, IntField
# init db connection 
db = MongoEngine()

def initialize_db(app):
    db.init_app(app)