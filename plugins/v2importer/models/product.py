from database.db import db
from flask_mongoengine import BaseQuerySet
from models.category import Category
from models.supplierCategory import SupplierCategory
import datetime
# from slugify import slugify, Slugify, UniqueSlugify


class Product(db.Document):
    iso = db.StringField(required=True)
    category = db.ReferenceField(Category)
    supplier_category = db.ReferenceField(SupplierCategory)
    category_name = db.StringField(required=True)
    category_slug = db.StringField(required=True)
    object = db.ListField()
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)
    meta = {
        'indexes': ['-created_at'],
        'ordering': ['-created_at'],
        'collection': 'products',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(Product, self).__setattr__(key, value)
        # if key == 'name':
        #     self.slug = slugify(self.name, to_lower=True)
