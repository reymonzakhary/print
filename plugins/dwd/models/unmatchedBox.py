from database.db import db
from flask_mongoengine import BaseQuerySet
from slugify import slugify, Slugify, UniqueSlugify

import uuid
import datetime


def id(): return str(uuid.uuid4())


class UnmatchedBox(db.Document):
    sort = db.IntField(required=False, default=0)
    tenant_id = db.StringField(required=True)
    tenant_name = db.StringField(required=True)
    name = db.StringField(required=True, default=None,  unique_with=('tenant_id'))
    slug = db.StringField(required=True, default=None, unique_with=('tenant_id'))
    sku = db.StringField(required=False, default='')
    description = db.StringField(required=False, default='')
    media = db.ListField()
    published = db.BooleanField(default=True)
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)

    meta = {
        'indexes': ['-created_at', 'slug', 'name'],
        'ordering': ['-created_at'],
        'collection': 'unmatched_boxes',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(UnmatchedBox, self).__setattr__(key, value)
        if key == 'name':
            self.slug = slugify(self.name, to_lower=True)
