from database.db import db
from flask_mongoengine import BaseQuerySet
import datetime
from slugify import slugify, Slugify, UniqueSlugify
from models.category import Category
from flask_mongoengine.wtf import model_form

# id = lambda: str(uuid.uuid4())


class Box(db.Document):
    iso = db.StringField(required=True)
    sort = db.IntField(required=False, default=0)
    name = db.StringField(required=True, unique=True)
    slug = db.StringField(required=False, unique=True)
    description = db.StringField(required=False, default='')
    media = db.ListField()
    sqm = db.BooleanField(required=True, unique=False, default=False)
    published = db.BooleanField(default=True)
    input_type = db.StringField(default='')
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)
    categories = db.ListField(db.ReferenceField(Category))

    meta = {
        'indexes': ['-created_at', 'slug', 'name', 'categories'],
        'ordering': ['-created_at', 'name'],
        'collection': 'dwd_boxes',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(Box, self).__setattr__(key, value)
        if key == 'name':
            self.slug = slugify(self.name, to_lower=True)

