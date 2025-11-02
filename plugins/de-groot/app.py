import os
from flask import Flask
from flask_restful import Api
from flask_session import Session
from database.db import initialize_db
from routes.routes import initialize_routes
from dotenv import load_dotenv

load_dotenv()

app = Flask(__name__)
api = Api(app)

# Select config via env: CONFIG=Development or Production
app.config.from_object('config.'+os.environ.get('CONFIG')+'Config')

# Minimal session setup; can be adjusted later
app.config["SESSION_TYPE"] = "filesystem"
app.config['SESSION_COOKIE_NAME'] = 'assortments'
app.secret_key = "0ex275G86IT1OPf3@p"
Session(app)

# Initialize DB and routes
try:
    initialize_db(app)
except Exception:
    pass
initialize_routes(api)

if __name__ == "__main__":
    ENVIRONMENT_DEBUG = app.config.get('DEBUG', False)
    ENVIRONMENT_PORT = int(app.config.get("APP_PORT", 5000))
    app.run(host="0.0.0.0", port=ENVIRONMENT_PORT, debug=ENVIRONMENT_DEBUG)



