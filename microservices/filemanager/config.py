"""Flask configuration."""
from os import environ, path
from dotenv import load_dotenv

basedir = path.abspath(path.dirname(__file__))
load_dotenv(path.join(basedir, '.env'))


class Config:
    """Base config."""
    SECRET_KEY = environ.get('SECRET_KEY')
    SESSION_COOKIE_NAME = environ.get('SESSION_COOKIE_NAME')
    STATIC_FOLDER = 'static'
    TEMPLATES_FOLDER = 'templates'


class ProductionConfig(Config):
    FLASK_ENV = 'production'
    DEBUG = False
    TESTING = False
  


class DevelopmentConfig(Config):
    FLASK_ENV = 'development'
    DEBUG = True
    TESTING = True
    S3_ACCESS_ID = environ.get('S3_ACCESS_ID','7ZGKHBRJ3HICZQXAUMR4')
    S3_SECRET_KEY = environ.get('S3_SECRET_KEY','TgoCc5fWwh+vxyzLkGWAPHZ20mLtknboyW8I5X6nJxU')
    S3_BUCKET = environ.get('S3_BUCKET',"cec-ams3-prod")
    S3_ENDPOINT_URL = environ.get('S3_ENDPOINT_URL',"https://ams3.digitaloceanspaces.com")
    S3_REGION_NAME = environ.get('S3_REGION_NAME',"ams3")

