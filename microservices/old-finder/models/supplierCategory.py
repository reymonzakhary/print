from database.db import db
from flask_mongoengine import BaseQuerySet
from slugify import slugify, Slugify, UniqueSlugify
from models.category import Category
import uuid
import datetime


def id(): return str(uuid.uuid4())


class SupplierCategory(db.Document):
    sort = db.IntField(required=False, default=0)
    tenant_id = db.StringField(required=True)
    tenant_name = db.StringField(required=True)
    countries = db.ListField(required=False, default=[])
    sku = db.StringField(required=False, default='')
    name = db.StringField(required=True)
    display_name = db.ListField(required=True, default=[])
    system_key = db.StringField(required=True)
    slug = db.StringField(required=True, default='', unique_with=('tenant_id'))
    description = db.StringField(required=False, default=None)
    shareable = db.BooleanField(required=False, default=False)
    published = db.BooleanField(required=False, default=True)
    media = db.ListField(required=False)
    price_build = db.DictField(required=False, default={})
    has_products = db.BooleanField(required=False, default=False)
    has_manifest = db.BooleanField(required=False, default=False)
    calculation_method = db.ListField(required=False,)
    production_days = db.ListField(required=False, default=[])
    dlv_days = db.ListField(required=False, default=[])
    printing_method = db.ListField(required=False, default=None)
    start_cost = db.IntField(required=False, default=0)
    additional = db.DictField(required=False, default={})
    ref_id = db.StringField(required=False, default='')
    ref_category_id = db.ReferenceField('self', required=False)
    ref_category_name = db.StringField(required=False, default='')
    linked = db.ReferenceField(Category)
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)

    meta = {
        'indexes': ['-created_at', 'slug', 'name', 'linked'],
        'ordering': ['-created_at'],
        'collection': 'supplier_categories',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(SupplierCategory, self).__setattr__(key, value)
        if key == 'name':
            self.slug = slugify(self.name, to_lower=True)

