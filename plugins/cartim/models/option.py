from database.db import db
from flask_mongoengine import BaseQuerySet
import datetime
from slugify import slugify, Slugify, UniqueSlugify
from flask_mongoengine.wtf import model_form


class Option(db.Document):
    sort = db.IntField(required=False, default=0)
    name = db.StringField(required=True, unique=True)
    display_name = db.ListField(required=False, default=[])
    sku = db.StringField(required=False, unique=True)
    slug = db.StringField(required=False, unique=True)
    system_key = db.StringField(required=False, default="")
    description = db.StringField(required=False, default='')
    media = db.ListField(default=[])
    tenant_id = db.StringField(required=False, default="")
    tenant_name = db.StringField(required=False, default="")
    dimension = db.StringField(required=False, default='2d')
    dynamic = db.BooleanField(default=False)
    dynamic_keys = db.ListField(required=False, default=[])
    start_on = db.IntField(required=False, default=0)
    end_on = db.IntField(required=False, default=0)
    generate = db.BooleanField(required=False, default=False)
    dynamic_type = db.StringField(required=False, default="")
    width = db.IntField(min_value=None, max_value=None, default=0)
    maximum_width = db.IntField(min_value=None, max_value=None, default=0)
    minimum_width = db.IntField(min_value=None, max_value=None, default=0)

    height = db.IntField(min_value=None, max_value=None, default=0)
    maximum_height = db.IntField(min_value=None, max_value=None, default=0)
    minimum_height = db.IntField(min_value=None, max_value=None, default=0)

    length = db.IntField(min_value=None, max_value=None, default=0)
    maximum_length = db.IntField(min_value=None, max_value=None, default=0)
    minimum_length = db.IntField(min_value=None, max_value=None, default=0)

    unit = db.StringField(required=False, default='')
    incremental_by = db.IntField(min_value=None, max_value=None, default=0)
    published = db.BooleanField(default=True)
    has_children = db.BooleanField(default=False)
    input_type = db.StringField(default='')
    extended_fields = db.ListField(required=False, default=[])
    shareable = db.BooleanField(default=False)
    parent = db.BooleanField(default=True)
    start_cost = db.IntField(min_value=None, max_value=None, default=0)
    calculation_method = db.ListField(required=False)
    rpm = db.IntField(min_value=None, max_value=None, default=0)
    sheet_runs = db.ListField(required=False, default=[])
    runs = db.ListField(required=False, default=[])
    configure = db.ListField(required=False, default=[])
    information = db.StringField(default='')
    children = db.ListField(db.ReferenceField('self', default=None), default=None)
    additional = db.DictField(required=False, default={})
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)
    checked = db.BooleanField(default=False)

    meta = {
        'indexes': ['-created_at', 'slug', 'name'],
        'ordering': ['-created_at', 'name'],
        'collection': 'options',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(Option, self).__setattr__(key, value)
        if key == 'name':
            self.slug = slugify(self.name, to_lower=True)
