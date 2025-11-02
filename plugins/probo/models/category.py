from database.db import db
from flask_mongoengine import BaseQuerySet
import datetime
from slugify import slugify, Slugify, UniqueSlugify


class Category(db.Document):
    box = db.StringField(required=True)
    option = db.StringField(required=True)
    parent = db.StringField(required=True)
    meta = {
        'collection': 'probo_categories',
        'queryset_class': BaseQuerySet
    }

