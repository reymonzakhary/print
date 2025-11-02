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

# if os.environ.get('FLASK_ENV') == 'production':
#     app.config.from_object(ProductionConfig)
# else:
#     app.config.from_object(DevelopmentConfig)

# mongoengine.connect(**app.config['MONGODB_SETTINGS'])
app.config["SESSION_TYPE"] = "filesystem"
app.config['SESSION_COOKIE_NAME'] = 'assortments'
app.secret_key = "0ex275G86IT1OPf3@p"  # Required for session encryption
Session(app)  # Initialize Flask-Session

initialize_db(app)
initialize_routes(api)

if __name__ == "__main__":
    ENVIRONMENT_DEBUG = app.config.get('DEBUG', False)
    ENVIRONMENT_PORT = int(app.config.get("APP_PORT", 5000))
    app.run(host="0.0.0.0", port=ENVIRONMENT_PORT, debug=ENVIRONMENT_DEBUG)

