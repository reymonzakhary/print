from database.db import db
from flask_mongoengine import BaseQuerySet
import datetime
from slugify import slugify, Slugify, UniqueSlugify
from models.category import Category
from flask_mongoengine.wtf import model_form

# id = lambda: str(uuid.uuid4())


class Boops(db.Document):
    iso = db.StringField(required=True)
    category = db.ReferenceField(Category)
    category_name = db.StringField(required=False, default=0)
    category_slug = db.StringField(required=False, default=0)
    boops = db.ListField(default=None)
    meta = {
        'indexes': ['category','category_name'],
        'ordering': ['category_name'],
        'collection': 'boops',
        'queryset_class': BaseQuerySet
    }
