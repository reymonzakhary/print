from database.db import db
from flask_mongoengine import BaseQuerySet
import datetime
from slugify import slugify, Slugify, UniqueSlugify
from models.category import Category
from models.box import Box
from models.option import Option


class CategoryBoxOption(db.Document):
    category = db.ReferenceField(Category)
    box = db.ReferenceField(Box)
    option = db.ReferenceField(Option)
    meta = {
        'indexes': [ 'category', 'box','option'],
        'collection': 'category_box_options',
        'queryset_class': BaseQuerySet
    }
