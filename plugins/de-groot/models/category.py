from database.db import db
from flask_mongoengine import BaseQuerySet
import datetime
from slugify import slugify, Slugify, UniqueSlugify


class Category(db.Document):
    name = db.StringField(required=True)
    sort = db.IntField(required=False, default=0)
    countries = db.ListField(required=False, default=[])
    sku = db.StringField(required=False, default='')
    system_key = db.StringField(required=True, unique=True)
    display_name = db.ListField(required=True, default=[])
    slug = db.StringField(required=True, unique=True)
    description = db.StringField(required=False, default=None)
    shareable = db.BooleanField(required=False, default=False)
    published = db.BooleanField(required=False, default=True)
    media = db.ListField(required=False)
    price_build = db.DictField(required=False, default={})
    has_products = db.BooleanField(required=False, default=False)
    has_manifest = db.BooleanField(required=False, default=False)
    calculation_method = db.ListField(required=False, )
    dlv_days = db.ListField(required=False, default=[])
    printing_method = db.ListField(required=False, default=None)
    production_days = db.ListField(required=False, default=[])
    production_dlv = db.ListField(required=False, default=[])
    free_entry = db.ListField(required=False, default=[])
    limits = db.ListField(required=False, default=[])
    ranges = db.ListField(required=False, default=[])
    range_list = db.ListField(required=False, default=[])
    ref_id = db.StringField(required=False, default='')
    ref_category_name = db.StringField(required=False, default='')
    start_cost = db.IntField(required=False, default=0)
    vat = db.IntField(required=False, default=0)
    bleed = db.IntField(required=False, default=0)
    range_around = db.IntField(required=False, default=0)
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)
    checked = db.BooleanField(default=False)
    additional = db.DictField(required=False, default={})

    meta = {
        'indexes': ['-created_at', 'slug'],
        'ordering': ['-created_at', 'name'],
        'collection': 'categories',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(Category, self).__setattr__(key, value)
        if key == 'name':
            if value is not None:
                self.slug = slugify(value, to_lower=True)
