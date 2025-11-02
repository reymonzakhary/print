from database.db import db
from flask_mongoengine import BaseQuerySet
from slugify import slugify, Slugify, UniqueSlugify
from models.box import Box
import uuid
import datetime


def id(): return str(uuid.uuid4())


class MatchedBox(db.Document):
    iso = db.StringField(required=True)
    sort = db.IntField(required=False, default=0)
    tenant_id = db.StringField(required=True)
    tenant_name = db.StringField(required=False)
    name = db.StringField(required=True, default=None, unique_with=('tenant_id', 'box'))
    slug = db.StringField(required=True, default=None, unique_with=('tenant_id', 'box'))
    sku = db.StringField(required=False, default='')
    description = db.StringField(required=False, default='')
    media = db.ListField()
    percentage = db.IntField(default=0)
    published = db.BooleanField(default=True)
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)

    box = db.ReferenceField(
        Box, unique_with=('slug', 'tenant_id', 'box'))

    meta = {
        'indexes': ['-created_at', 'slug', 'name', 'box'],
        'ordering': ['-created_at'],
        'collection': 'matched_boxes',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(MatchedBox, self).__setattr__(key, value)
        if key == 'name':
            self.slug = slugify(self.name, to_lower=True)
