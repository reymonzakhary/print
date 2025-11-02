from database.db import db
from flask_mongoengine import BaseQuerySet
import datetime
from slugify import slugify, Slugify, UniqueSlugify


class Category(db.Document):
    iso = db.StringField(required=True)
    sort = db.IntField(required=False, default=0)
    name = db.StringField(required=True, unique=True)
    sku = db.StringField(required=True)
    slug = db.StringField(required=True, unique=True, default=None)
    description = db.StringField(required=False, default='')
    media = db.ListField()
    attributes = db.ListField()
    published = db.BooleanField(default=True)
    checked = db.BooleanField(default=False)
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)
    meta = {
        'indexes': ['-created_at', 'slug', 'name'],
        'ordering': ['-created_at', 'name'],
        'collection': 'categories',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(Category, self).__setattr__(key, value)
        if key == 'name':
            self.slug = slugify(self.name, to_lower=True)
