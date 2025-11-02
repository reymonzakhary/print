import os
from flask import Flask, session
from flask_session import Session  # Flask-Session for server-side sessions
from flask_restful import Api
from database.db import initialize_db
from routes.routes import initialize_routes
from dotenv import load_dotenv

load_dotenv()

app = Flask(__name__)
api = Api(app)

app.config.from_object('config.'+os.environ.get('CONFIG')+'Config')

# app.config['MONGODB_SETTINGS'] = {
#     'host': 'mongodb+srv://doadmin:0ex275G86IT1OPf3@private-db-mongodb-ams3-cec-prod-cdf6b254.mongo.ondigitalocean.com/admin?replicaSet=db-mongodb-ams3-cec-prod&authSource=admin',
#     'ssl': True,
#     'ssl_match_hostname': False,
#     'ssl_ca_certs': '/var/crt/ca-certificate.crt',
# }
# Configure Flask to use filesystem-based sessions (better for larger apps)
app.config["SESSION_TYPE"] = "filesystem"
app.config['SESSION_COOKIE_NAME'] = 'assortments'
app.secret_key = "0ex275G86IT1OPf3@p"  # Required for session encryption
Session(app)  # Initialize Flask-Session

initialize_db(app)
initialize_routes(api)

if __name__ == "__main__":
    ENVIRONMENT_DEBUG = bool(os.environ.get("APP_DEBUG", True))
    ENVIRONMENT_PORT = os.environ.get("APP_PORT", 5000)
    app.run(host="0.0.0.0", port=ENVIRONMENT_PORT, debug=ENVIRONMENT_DEBUG)
