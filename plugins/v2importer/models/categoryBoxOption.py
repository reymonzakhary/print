from database.db import db
from flask_mongoengine import BaseQuerySet
import uuid
import datetime
from slugify import slugify, Slugify, UniqueSlugify
from models.category import Category
from flask_mongoengine.wtf import model_form
from models.category import Category
from models.box import Box
from models.option import Option


id = lambda: str(uuid.uuid4())

class CategoryBoxOption(db.Document):
    category = db.ReferenceField(Category)
    box = db.ReferenceField(Box)
    option = db.ReferenceField(Option)
    meta = {
        'collection':'category_box_options',
        'queryset_class': BaseQuerySet
    }