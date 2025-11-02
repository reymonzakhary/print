from database.db import db
from flask_mongoengine import BaseQuerySet
from models.supplierProduct import SupplierProduct
import datetime
# from slugify import slugify, Slugify, UniqueSlugify


class SupplierProductPrice(db.Document):
    supplier_product = db.ReferenceField(SupplierProduct)
    supplier_id = db.StringField(required=True)
    tables = db.DictField()
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)
    additional = db.DictField(required=False, default={})
    meta = {
        'indexes': ['-created_at'],
        'ordering': ['-created_at'],
        'collection': 'supplier_product_prices',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(SupplierProductPrice, self).__setattr__(key, value)
        # if key == 'name':
        #     self.slug = slugify(self.name, to_lower=True)
