from typing import Collection
from database.db import db
from flask_mongoengine import BaseQuerySet
from slugify import slugify, Slugify, UniqueSlugify

import uuid
import datetime


def id(): return str(uuid.uuid4())


class UnhandledProduct(db.Document):
    tenant_id = db.StringField(required=True)
    tenant_name = db.StringField(required=True)
    box_name = db.StringField(required=True)
    option_name = db.StringField(required=True)
    type = db.StringField(required=True, default=None)
    sku = db.StringField(required=False, default='')
    object = db.ListField()
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)

    meta = {
        'indexes': ['-created_at'],
        'ordering': ['-created_at'],
        'collection': 'unhandled_products',
        'queryset_class': BaseQuerySet
    }
