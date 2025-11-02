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
    slug = db.StringField(required=False, unique_with=('tenant_id'))
    tenant_id = db.StringField(required=True)
    tenant_name = db.StringField(required=False)
    option = db.ReferenceField(Option)
    description = db.StringField(required=False, default='')
    media = db.StringField(default='')
    sku = db.StringField(default='')
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)

    meta = {
        'indexes': ['-created_at', 'slug', 'name', 'option'],
        'ordering': ['-created_at', 'name'],
        'collection': 'supplier_options',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(SupplierOption, self).__setattr__(key, value)
        if key == 'name':
            self.slug = slugify(self.name, to_lower=True)
