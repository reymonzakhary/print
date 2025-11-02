from database.db import db
from flask_mongoengine import BaseQuerySet
import datetime
from slugify import slugify, Slugify, UniqueSlugify
from models.category import Category
from flask_mongoengine.wtf import model_form

# id = lambda: str(uuid.uuid4())


class Box(db.Document):
    sort = db.IntField(required=False, default=0)
    tenant_id = db.StringField(required=False, default=None)
    tenant_name = db.StringField(required=False)
    sku = db.StringField(required=False, default='')
    name = db.StringField(required=True, unique=True)
    display_name = db.ListField(required=False, default=[])
    system_key = db.StringField(required=False, default="")
    slug = db.StringField(required=False, unique=True)
    description = db.StringField(required=False, default='')
    media = db.ListField()
    sqm = db.BooleanField(required=True, unique=False, default=False)
    appendage = db.BooleanField(required=True, unique=False, default=False)
    calculation_type = db.StringField(default='')
    published = db.BooleanField(default=True)
    input_type = db.StringField(default='')
    incremental = db.BooleanField(required=True, default=False)
    select_limit = db.IntField(required=False, default=0)
    option_limit = db.IntField(required=False, default=0)
    shareable = db.BooleanField(default=True)
    start_cost = db.IntField(required=False)
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)
    categories = db.ListField(db.ReferenceField(Category))
    additional = db.DictField(required=False, default={})
    checked = db.BooleanField(default=False)

    configure = db.DictField(required=False,default={})

    meta = {
        'indexes': ['-created_at', 'slug', 'name', 'categories'],
        'ordering': ['-created_at', 'name'],
        'collection': 'boxes',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(Box, self).__setattr__(key, value)
        if key == 'name':
            self.slug = slugify(self.name, to_lower=True)
