from database.db import db
from flask_mongoengine import BaseQuerySet
from models.category import Category
from models.supplierCategory import SupplierCategory
import datetime
# from slugify import slugify, Slugify, UniqueSlugify


class SupplierProduct(db.Document):
    iso = db.StringField(required=True)
    tenant_name = db.StringField(required=True)
    tenant_id = db.StringField(required=True)
    category_name = db.StringField(required=True)
    category_display_name = db.StringField(required=True)
    category_slug = db.StringField(required=True)
    linked = db.ReferenceField(Category)
    supplier_category = db.ReferenceField(SupplierCategory)
    shareable = db.BooleanField(default=True)
    published = db.BooleanField(default=True)
    object = db.ListField()
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)
    meta = {
        'indexes': ['-created_at','linked','supplier_category','tenant_id'],
        'ordering': ['-created_at'],
        'collection': 'supplier_products',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(SupplierProduct, self).__setattr__(key, value)
        # if key == 'name':
        #     self.slug = slugify(self.name, to_lower=True)
    def __repr__(self):
        return '<SupplierProduct %r' % self.id