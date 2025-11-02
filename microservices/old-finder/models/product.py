from database.db import db
from flask_mongoengine import BaseQuerySet
import datetime
from models.category import Category


class Product(db.Document):
    iso = db.StringField(required=True)
    category = db.ReferenceField(Category)
    category_name = db.StringField(required=True)
    category_slug = db.StringField(required=True, default=None)
    object = db.ListField()
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)
    meta = {
        'indexes': ['-created_at', 'category', 'category_name','category_slug'],
        'ordering': ['-created_at', 'category_name','category_slug'],
        'collection': 'products',
        'queryset_class': BaseQuerySet
    }