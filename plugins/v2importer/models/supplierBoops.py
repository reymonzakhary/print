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
    iso = db.StringField(required=True)
    tenant_id = db.StringField(required=True, default=0)
    tenant_name = db.StringField(required=True, default=0)
    supplier_category = db.ReferenceField(SupplierCategory)
    category = db.ReferenceField(Category)
    category_name = db.StringField(required=False, default=0)
    boops = db.ListField(default=None)
    meta = {
        'indexes': ['tenant_id', 'tenant_name', 'supplier_category','category','category_name'],
        'ordering': ['tenant_name','category_name'],
        'collection': 'supplier_boops',
        'queryset_class': BaseQuerySet
    }
