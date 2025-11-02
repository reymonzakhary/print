from database.db import db
from flask_mongoengine import BaseQuerySet
import datetime


class SupplierMachine(db.Document):
    tenant_id = db.StringField(required=True)
    tenant_name = db.StringField(required=True)
    pg_id = db.IntField(required=False, default=0)
    name = db.StringField(required=False, default='')
    description = db.StringField(required=False, default=None)
    type = db.StringField(required=False, default='sheet')
    unit = db.StringField(required=False, default='mm')
    width = db.IntField(required=False, default=0)
    height = db.IntField(required=False, default=0)
    sqcm = db.IntField(required=False, default=0)
    ean = db.StringField(required=False, default='')
    rpm = db.IntField(required=False, default=0)
    pm = db.StringField(required=False, default='')
    setup_time = db.IntField(required=False, default=0)
    cooling_time = db.IntField(required=False, default=0)
    cooling_time_per = db.IntField(required=False, default=0)
    mpm = db.BooleanField(required=False, default=False)
    default = db.BooleanField(required=False, default=False)
    price = db.IntField(required=False, default=0)
    created_at = db.DateTimeField(default=datetime.datetime.now, required=True)
    updated_at = db.DateTimeField(default=datetime.datetime.now, required=True)

    meta = {
        'indexes': ['-created_at', 'ean'],
        'ordering': ['-created_at', 'name'],
        'collection': 'supplier_machines',
        'queryset_class': BaseQuerySet
    }

    def __setattr__(self, key, value):
        super(SupplierMachine, self).__setattr__(key, value)
