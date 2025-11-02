from flask_mongoengine import BaseQuerySet, MongoEngine
# init db connection 
db = MongoEngine()

def initialize_db(app):
    db.init_app(app)