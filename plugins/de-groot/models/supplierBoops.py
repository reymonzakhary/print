from database.db import db
from flask_mongoengine import BaseQuerySet
import datetime
from slugify import slugify, Slugify, UniqueSlugify
from models.category import Category
from flask_mongoengine.wtf import model_form
from models.supplierCategory import SupplierCategory
from models.category import Category

# id = lambda: str(uuid.uuid4())


class SupplierBoops(db.Document):
    tenant_id = db.StringField(required=True, default=0)
    ref_id = db.StringField(required=False, default='')
    ref_boops_id = db.ReferenceField('self', required=False)
    ref_boops_name = db.StringField(required=False, default='')
    source_slug = db.StringField(required=False, default='')
    row_id = db.StringField(required=False)
    tenant_name = db.StringField(required=True, default=0)
    supplier_category = db.ReferenceField(SupplierCategory)
    linked = db.ReferenceField(Category)
    display_name = db.ListField(required=True, default=[])
    system_key = db.StringField(required=True, unique_with=('tenant_id'))
    shareable = db.BooleanField(required=False, default=False)
    published = db.BooleanField(default=True)
    generated = db.BooleanField(default=True)
    name = db.StringField(required=False, default=0)
    slug = db.StringField(required=True, default='')
    source_slug = db.StringField(required=False, default=None)
    divided = db.BooleanField(default=False)
    boops = db.ListField(default=None)
    additional = db.DictField(required=False, default={})

    meta = {
        'indexes': ['tenant_id', 'tenant_name', 'supplier_category','linked','name'],
        'ordering': ['tenant_name'],
        'collection': 'supplier_boops',
        'queryset_class': BaseQuerySet
    }
