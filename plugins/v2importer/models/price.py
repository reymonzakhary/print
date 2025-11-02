from database.db import db
from flask_mongoengine import BaseQuerySet
from models.product import Product
import datetime
# from slugify import slugify, Slugify, UniqueSlugify


class Price(db.Document):
    iso = db.StringField(required=True)
    product = db.ReferenceField(Product)
    supplier_id = db.StringField(required=True)
    tables = db.DictField()
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)
    meta = {
        'indexes': ['-created_at'],
        'ordering': ['-created_at'],
        'collection': 'prices',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(Price, self).__setattr__(key, value)
        # if key == 'name':
        #     self.slug = slugify(self.name, to_lower=True)
