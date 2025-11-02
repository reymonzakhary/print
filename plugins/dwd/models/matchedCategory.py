from database.db import db
from flask_mongoengine import BaseQuerySet
from slugify import slugify, Slugify, UniqueSlugify
from models.category import Category
import uuid
import datetime


def id(): return str(uuid.uuid4())


class MatchedCategory(db.Document):
    sort = db.IntField(required=False, default=0)
    tenant_id = db.StringField(required=True)
    tenant_name = db.StringField(required=False)
    name = db.StringField(required=True, default=None,unique_with=('tenant_id', 'category'))
    slug = db.StringField(required=True, default=None,unique_with=('tenant_id', 'category'))
    sku = db.StringField(required=False, default='')
    description = db.StringField(required=False, default='')
    media = db.ListField()
    percentage = db.IntField(default=0)
    published = db.BooleanField(default=True)
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)

    category = db.ReferenceField(Category,unique_with=('tenant_id','name'))

    meta = {
        'indexes': ['-created_at', 'slug', 'name', 'category'],
        'ordering': ['-created_at'],
        'collection': 'matched_categories',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(MatchedCategory, self).__setattr__(key, value)
        if key == 'name':
            self.slug = slugify(self.name, to_lower=True)
