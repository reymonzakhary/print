from database.db import db
from flask_mongoengine import BaseQuerySet
import datetime
# from slugify import slugify, Slugify, UniqueSlugify


class Product(db.Document):
    id = db.IntField()
    iso = db.StringField(required=True)
    category_id = db.StringField(required=True)
    category_name = db.StringField(required=True)
    object = db.DictField()
    prices = db.ListField()
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)
    meta = {
        'indexes': ['-created_at'],
        'ordering': ['-created_at'],
        'collection': 'dwd_products',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(Product, self).__setattr__(key, value)
        # if key == 'name':
        #     self.slug = slugify(self.name, to_lower=True)
