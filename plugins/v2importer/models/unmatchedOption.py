from database.db import db
from flask_mongoengine import BaseQuerySet
from slugify import slugify, Slugify, UniqueSlugify
from models.box import Box

import uuid
import datetime


def id(): return str(uuid.uuid4())


class UnmatchedOption(db.Document):
    iso = db.StringField(required=True)
    sort = db.IntField(required=False, default=0)
    name = db.StringField(required=True, unique_with=('tenant_id'))
    slug = db.StringField(required=False, unique_with=('tenant_id'))
    tenant_id = db.StringField(required=True)
    tenant_name = db.StringField(required=True)
    description = db.StringField(required=False, default='')
    media = db.StringField(default='')
    sku = db.StringField(default='')
    published = db.BooleanField(default=True)
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)

    meta = {
        'indexes': ['-created_at', 'slug', 'name'],
        'ordering': ['-created_at', 'name'],
        'collection': 'unmatched_options',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(UnmatchedOption, self).__setattr__(key, value)
        if key == 'name':
            self.slug = slugify(self.name, to_lower=True)
