from database.db import db
from flask_mongoengine import BaseQuerySet
import datetime
from slugify import slugify, Slugify, UniqueSlugify
from models.box import Box
from flask_mongoengine.wtf import model_form


class Option(db.Document):
    iso = db.StringField(required=True)
    sort = db.IntField(required=False, default=0)
    name = db.StringField(required=True, unique=True)
    slug = db.StringField(required=False, unique=True)
    description = db.StringField(required=False, default='')
    media = db.StringField(default='')
    incremental_by = db.IntField(min_value=None, max_value=None, default=0)
    information = db.StringField(default='')
    box = db.StringField(default='')
    published = db.BooleanField(default=True)
    input_type = db.StringField(default='')
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)
    valid_options = db.ListField(default=None)

    meta = {
        'indexes': ['-created_at', 'slug', 'name'],
        'ordering': ['-created_at', 'name'],
        'collection': 'dwd_options',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(Option, self).__setattr__(key, value)
        if key == 'name':
            self.slug = slugify(self.name, to_lower=True)
