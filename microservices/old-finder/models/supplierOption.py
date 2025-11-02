from database.db import db
from flask_mongoengine import BaseQuerySet
from slugify import slugify, Slugify, UniqueSlugify
from models.option import Option
from models.box import Box
import datetime


def id(): return str(uuid.uuid4())


class SupplierOption(db.Document):
    iso = db.StringField(required=True)
    sort = db.IntField(required=False, default=0)
    name = db.StringField(required=True, unique_with=('tenant_id'))
    display_name = db.StringField(required=True)
    slug = db.StringField(required=False, unique_with=('tenant_id'))
    tenant_id = db.StringField(required=True)
    tenant_name = db.StringField(required=False)
    unit = db.StringField(required=False)
    maximum = db.IntField(required=False)
    minimum = db.IntField(required=False)
    incremental_by = db.IntField(required=False)
    information = db.StringField(required=False)
    input_type = db.StringField(required=False)
    linked = db.ReferenceField(Option)
    shareable = db.BooleanField(required=False, default=False)
    published = db.BooleanField(default=True)
    description = db.StringField(required=False, default='')
    media = db.StringField(default='')
    sku = db.StringField(default='')
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)

    meta = {
        'indexes': ['-created_at', 'slug', 'name', 'linked'],
        'ordering': ['-created_at', 'name'],
        'collection': 'supplier_options',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(SupplierOption, self).__setattr__(key, value)
        if key == 'name':
            self.slug = slugify(self.name, to_lower=True)
