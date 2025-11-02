from database.db import db
from flask_mongoengine import BaseQuerySet
from slugify import slugify, Slugify, UniqueSlugify
from models.category import Category
import uuid
import datetime


def id(): return str(uuid.uuid4())


class SupplierCategory(db.Document):
    iso = db.StringField(required=True)
    sort = db.IntField(required=False, default=0)
    tenant_id = db.StringField(required=True)
    tenant_name = db.StringField(required=True)
    sku = db.StringField(required=False, default='')
    name = db.StringField(required=True, default='', unique_with=('tenant_id'))
    slug = db.StringField(required=True, default='', unique_with=('tenant_id'))
    category = db.ReferenceField(Category, unique_with=('tenant_id', 'name'))
    description = db.StringField(required=False, default='')
    media = db.ListField()
    published = db.BooleanField(default=True)
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)

    meta = {
        'indexes': ['-created_at', 'slug', 'name'],
        'ordering': ['-created_at'],
        'collection': 'supplier_categories',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(SupplierCategory, self).__setattr__(key, value)
        if key == 'name':
            self.slug = slugify(self.name, to_lower=True)
