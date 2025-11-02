try:
    from flask_mongoengine import MongoEngine, BaseQuerySet
    db = MongoEngine()
    def initialize_db(app):
        db.init_app(app)
except Exception:
    db = None
    def initialize_db(app):
        return None



