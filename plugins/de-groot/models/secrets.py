from unittest.mock import DEFAULT

from database.db import db
from flask_mongoengine import BaseQuerySet
import datetime


class Secret(db.Document):
    tenant_id = db.StringField(required=False, DEFAULT=None)
    username = db.StringField(required=False, DEFAULT=None)
    password = db.StringField(required=False, DEFAULT=None)
    url = db.StringField(required=False, DEFAULT=None)
    token = db.StringField(required=False, DEFAULT=None)
    dwd_url = db.StringField(required=False, DEFAULT=None)
    dwd_secret = db.StringField(required=False, DEFAULT=None)
    dwd_user_id = db.StringField(required=False, DEFAULT=None)

    meta = {
        'collection': 'secrets',
    }
