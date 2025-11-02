"""Flask configuration."""
from os import environ, path
import os
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
    MONGODB_SETTINGS = {
        'host': os.environ.get("HOST", '127.0.0.1'),
        'ssl': os.environ.get('SSL', False),
        'ssl_match_hostname': os.environ.get('SSL_MATCH_HOSTNAME', False),
        'ssl_ca_certs': os.environ.get('SSL_CA_CERTS')
    }


class DevelopmentConfig(Config):
    FLASK_ENV = 'development'
    DEBUG = True
    TESTING = True
    MONGODB_SETTINGS = {
        'db': os.environ.get("DB", 'admin'),
        'host': os.environ.get("HOST", '127.0.0.1'),
        'port': int(os.environ.get("PORT", 27017)),
        'username': os.environ.get("USERNAME", 'admin'),
        'password': os.environ.get("PASSWORD", 'ad'),
        'authentication_source': os.environ.get("AUTHENTICATION_SOURCE", 'admin'),
    }
