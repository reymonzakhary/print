from database.db import db
from flask_mongoengine import BaseQuerySet
from slugify import slugify, Slugify, UniqueSlugify
from models.option import Option

import uuid
import datetime


def id(): return str(uuid.uuid4())


class MatchedOption(db.Document):
    sort = db.IntField(required=False, default=0)
    tenant_id = db.StringField(required=True)
    tenant_name = db.StringField(required=False)
    name = db.StringField(required=True, default=None, unique_with=('tenant_id', 'option'))
    slug = db.StringField(required=True, default=None, unique_with=('tenant_id', 'option'))
    sku = db.StringField(required=False, default='')
    description = db.StringField(required=False, default='')
    media = db.ListField()
    percentage = db.IntField(default=0)
    published = db.BooleanField(default=True)
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)

    option = db.ReferenceField(Option, unique_with=('tenant_id', 'name'))

    meta = {
        'indexes': ['-created_at', 'slug', 'name', 'option'],
        'ordering': ['-created_at'],
        'collection': 'matched_options',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(MatchedOption, self).__setattr__(key, value)
        if key == 'name':
            self.slug = slugify(self.name, to_lower=True)
