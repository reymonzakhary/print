from database.db import db
from flask_mongoengine import BaseQuerySet
from slugify import slugify, Slugify, UniqueSlugify
from models.option import Option
from models.box import Box
from models.supplierBox import SupplierBox
from mongoengine import ValidationError
import datetime

# def id(): return str(uuid.uuid4())

class SupplierOption(db.Document):
    sort = db.IntField(required=False, default=0)
    tenant_name = db.StringField(required=True)
    tenant_id = db.StringField(required=True)
    name = db.StringField(required=True)
    display_name = db.ListField(required=True)
    slug = db.StringField(required=False, unique_with=('tenant_id'))
    source_slug = db.ListField(required=False)
    system_key = db.StringField(required=False)
    description = db.StringField(required=False, default='')
    information = db.StringField(required=False, default='')
    media = db.ListField(required=False, default=[])
    incremental_by = db.IntField(required=False, default=0)
    published = db.BooleanField(default=True)
    has_children = db.BooleanField(required=False, default=False)
    input_type = db.StringField(required=False, default="radio")
    extended_fields = db.ListField(required=False, default=[])
    linked = db.ReferenceField(Option, required=False,default=None)
    shareable = db.BooleanField(required=False, default=False)
    sku = db.StringField(required=False, default='')
    children = db.ListField(db.ReferenceField('self', default=[]), required=False, default=[])
    parent = db.BooleanField(default=True)
    rpm = db.IntField(required=False, min_value=None, max_value=None, default=0)
    sheet_runs = db.ListField(required=False, default=[])
    runs = db.ListField(required=False, default=[])
    boxes = db.ListField(db.ReferenceField(SupplierBox), required=False, default=[])
    additional = db.DictField(required=False, default={})
    configure = db.ListField(required=False, default=[])
    created_at = db.DateTimeField(required=False, default=datetime.datetime.now)

    dimension = db.StringField(required=False, default='2d')
    dynamic = db.BooleanField(required=False, default=False)
    dynamic_type = db.StringField(required=False, default="")
    dynamic_keys = db.ListField(required=False, default=[])
    start_on = db.IntField(required=False, default=0)
    end_on = db.IntField(required=False, default=0)
    generate = db.BooleanField(required=False, default=False)
    unit = db.StringField(required=False, default="mm")
    width = db.IntField(required=False, min_value=None, max_value=None, default=0)
    maximum_width = db.IntField(required=False, min_value=None, max_value=None, default=0)
    minimum_width = db.IntField(required=False, min_value=None, max_value=None, default=0)
    height = db.IntField(required=False, min_value=None, max_value=None, default=0)
    maximum_height = db.IntField(required=False, min_value=None, max_value=None, default=0)
    minimum_height = db.IntField(required=False, min_value=None, max_value=None, default=0)
    length = db.IntField(required=False, min_value=None, max_value=None, default=0)
    maximum_length = db.IntField(required=False, min_value=None, max_value=None, default=0)
    minimum_length = db.IntField(required=False, min_value=None, max_value=None, default=0)
    start_cost = db.IntField(required=False, default=0)
    calculation_method = db.StringField(required=False, default='qty')

    meta = {
        'indexes': ['-created_at', 'slug', 'name', 'linked'],
        'ordering': ['-created_at', 'name'],
        'collection': 'supplier_options',
        'strict': False,
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(SupplierOption, self).__setattr__(key, value)
        if key == 'name':
            self.slug = slugify(self.name, to_lower=True)
