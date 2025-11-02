from database.db import db
from flask_mongoengine import BaseQuerySet
from slugify import slugify, Slugify, UniqueSlugify
from models.supplierCategory import SupplierCategory

import datetime


class ResellerCategory(db.Document):
    sort = db.IntField(required=False, default=0)
    tenant_id = db.StringField(required=True)
    tenant_name = db.StringField(required=True)
    supplier_id = db.StringField(required=True)
    supplier_category = db.ReferenceField(SupplierCategory)
    description = db.StringField(required=False, default='')
    published = db.BooleanField(default=True)
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)
    additional = db.DictField(required=False, default={})

    meta = {
        'indexes': ['-created_at', 'tenant_id'],
        'ordering': ['-created_at'],
        'collection': 'reseller_categories',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(ResellerCategory, self).__setattr__(key, value)
