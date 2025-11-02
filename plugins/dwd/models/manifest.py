from database.db import db
from flask_mongoengine import BaseQuerySet
from models.category import Category


class BoopOperation(db.EmbeddedDocument):
    id = db.ObjectIdField(required=True)  # Using ObjectIdField to ensure proper MongoDB indexing
    sort = db.IntField(required=False, default=0)  # Using ObjectIdField to ensure proper MongoDB indexing
    name = db.StringField(required=True)
    slug = db.StringField()
    sku = db.StringField()
    unit = db.StringField()
    media = db.ListField()
    additional = db.ListField()
    tenant_id = db.StringField()
    tenant_name = db.StringField()
    dimension = db.StringField(default='2d')
    excludes = db.ListField(default=[])
    start_on = db.IntField(default=0)
    end_on = db.IntField(default=0)
    generate = db.BooleanField(default=False)
    description = db.StringField()
    information = db.StringField()
    input_type = db.StringField()
    system_key = db.StringField(required=True)
    linked = db.StringField(default="")
    dynamic = db.BooleanField(default=False)
    dynamic_keys = db.ListField()
    extended_fields = db.ListField()
    rpm = db.IntField(default=0)
    incremental_by = db.IntField(default=0)
    published = db.BooleanField(default=True)
    shareable = db.BooleanField(default=False)
    parent = db.BooleanField(default=False)
    has_children = db.BooleanField(default=False)
    start_cost = db.IntField(default=0)
    calculation_method = db.IntField(default=0)

    height = db.IntField(default=0)
    minimum_height = db.IntField(default=0)
    maximum_height = db.IntField(default=0)
    width = db.IntField(default=0)
    minimum_width = db.IntField(default=0)
    maximum_width = db.IntField(default=0)
    length = db.IntField(default=0)
    maximum_length = db.IntField(default=0)
    minimum_length = db.IntField(default=0)

    display_name = db.ListField(db.DictField(), default=list)  # Multi-language support


class Boop(db.EmbeddedDocument):
    id = db.ObjectIdField(required=True)  # Using ObjectId for unique identifier
    system_key = db.StringField(required=True)
    name = db.StringField(required=True)
    calculation_type = db.StringField(required=False, default="")
    tenant_id = db.StringField(required=False, default="")
    tenant_name = db.StringField(required=False, default="")
    description = db.StringField(required=False, default="")
    slug = db.StringField()
    option_limit = db.IntField(required=False, default=0)
    select_limit = db.IntField(required=False, default=0)
    sort = db.IntField(required=False, default=0)
    linked = db.StringField(default='')
    published = db.BooleanField(default=True)
    incremental = db.BooleanField(default=False)
    shareable = db.BooleanField(default=False)
    divider = db.StringField()
    sqm = db.IntField(default=0)
    sku = db.StringField()
    input_type = db.StringField()
    appendage = db.BooleanField(default=False)
    ops = db.ListField(db.EmbeddedDocumentField(BoopOperation), default=list)  # List of operations
    display_name = db.ListField(db.DictField(), default=list)  # Multi-language support


class Manifest(db.Document):
    name = db.StringField(default=None)  # Optional field
    sort = db.IntField(required=False, default=0)
    sku = db.StringField()
    description = db.StringField(required=False, default="")
    slug = db.StringField(required=True, unique=True)  # Enforce uniqueness
    tenant_id = db.StringField(default=None)  # Optional field
    ref_id = db.StringField(default='')  # Reference ID (if applicable)
    ref_boops_id = db.StringField(required=False, default="")  # Reference to another Manifest
    ref_boops_name = db.StringField(default='')  # Optional reference name
    category = db.ReferenceField(Category, required=True)  # Foreign key reference
    tenant_name = db.StringField(default=None)
    divided = db.BooleanField(default=False)  # Ensure correct type

    # Additional fields
    system_key = db.StringField(required=True, unique=True)
    shareable = db.BooleanField(default=False)
    published = db.BooleanField(default=True)
    generated = db.BooleanField(default=True)
    has_products = db.BooleanField(default=False)
    has_manifest = db.BooleanField(default=False)
    start_cost = db.IntField(default=0)
    vat = db.FloatField(default=0.0)

    # Nested fields
    shared = db.ListField(db.StringField(), default=list)
    display_name = db.ListField(db.DictField(), default=list)  # Multi-language names
    boops = db.ListField(db.EmbeddedDocumentField(Boop), default=list)  # Embedding Boops properly
    additional = db.DictField(default=dict)  # Ensure additional remains a dictionary

    meta = {
        'indexes': ['tenant_id', 'tenant_name', 'category', 'name', 'system_key'],
        'ordering': ['tenant_name'],
        'collection': 'system_manifests',
        'queryset_class': BaseQuerySet
    }
