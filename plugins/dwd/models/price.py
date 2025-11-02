from database.db import db
from flask_mongoengine import BaseQuerySet
from models.supplierProduct import SupplierProduct
import datetime
# from slugify import slugify, Slugify, UniqueSlugify


class Price(db.Document):
    supplier_product = db.ReferenceField(SupplierProduct)
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
