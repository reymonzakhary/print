from database.db import db
from flask_mongoengine import BaseQuerySet
from slugify import slugify, Slugify, UniqueSlugify
from models.box import Box
import uuid
import datetime

def id(): return str(uuid.uuid4())

class SupplierBox(db.Document):
    iso = db.StringField(required=True)
    row_id = db.StringField(required=False)
    sort = db.IntField(required=False, default=0)
    tenant_id = db.StringField(required=True)
    tenant_name = db.StringField(required=False)
    sku = db.StringField(required=False, default='')
    name = db.StringField(required=True, default='', unique_with=('tenant_id'))
    display_name = db.ListField(required=True, default=[])
    system_key = db.StringField(required=True)
    input_type = db.StringField(required=False, default='')
    incremental = db.BooleanField(required=True, default=False)
    select_limit = db.IntField(required=True, default=0)
    option_limit = db.IntField(required=True, default=0)
    sqm = db.BooleanField(required=True, default=False)
    slug = db.StringField(required=True, default='', unique_with=('tenant_id'))
    linked = db.ReferenceField(Box)
    description = db.StringField(required=False, default='')
    media = db.ListField()
    shareable = db.BooleanField(default=True)
    published = db.BooleanField(default=True)
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)
    additional = db.DictField(required=False, default={})

    meta = {
        'indexes': ['-created_at', 'slug', 'name'],
        'ordering': ['-created_at'],
        'collection': 'supplier_boxes',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(SupplierBox, self).__setattr__(key, value)
        if key == 'name':
            self.slug = slugify(self.name, to_lower=True)
