from database.db import db
from flask_mongoengine import BaseQuerySet
import datetime
from slugify import slugify, Slugify, UniqueSlugify
from models.option import Option


class Catalogue(db.Document):
    material = db.ReferenceField(Option)
    grs = db.ReferenceField(Option)
    sort = db.IntField(required=False, default=0)
    calc_type = db.StringField(required=False, default='')
    additional = db.DictField(required=False, default={})

    meta = {
        'ordering': ['-created_at', 'sort'],
        'collection': 'catalogues',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(Catalogue, self).__setattr__(key, value)
        if key == 'name':
            self.slug = slugify(self.name, to_lower=True)
