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
    system_key = db.StringField(required=True)
    display_name = db.ListField(required=True, default=[])
    slug = db.StringField(required=True, default='', unique_with=('tenant_id'))
    source_slug = db.StringField(required=False)
    description = db.StringField(required=False, default=None)
    status = db.StringField(required=False, default=None)
    shareable = db.BooleanField(required=False, default=False)
    published = db.BooleanField(required=False, default=True)
    checked = db.BooleanField(required=False, default=False)
    source = db.StringField(required=False, default=None)
    media = db.ListField(required=False)
    price_build = db.DictField(required=False, default={})
    has_products = db.BooleanField(required=False, default=False)
    has_manifest = db.BooleanField(required=False, default=False)
    calculation_method = db.ListField(required=False,)
    dlv_days = db.ListField(required=False, default=[])
    printing_method = db.ListField(required=False, default=None)
    ranges = db.ListField(required=False, default=[])
    rangeSets = db.ListField(required=False, default=[])
    range_list = db.ListField(required=False, default=[])
    bleed = db.IntField(required=False, default=0)
    limits = db.ListField(required=False, default=[])
    free_entry = db.ListField(required=False, default=[])
    range_around = db.IntField(required=False, default=2)
    production_days = db.ListField(required=False, default=[])
    start_cost = db.IntField(required=False, default=0)
    linked = db.ReferenceField(Category)
    ref_id = db.StringField(required=False, default='')
    ref_category_id = db.ReferenceField('self', required=False)
    ref_category_name = db.StringField(required=False, default='')
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)
    updated_at = db.DateTimeField(default=datetime.datetime.now, required=True)
    additional = db.DictField(required=False, default={})
    vat = db.DecimalField(required=False, nullable=True)
    production_dlv = db.ListField(required=False, default=[])

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

