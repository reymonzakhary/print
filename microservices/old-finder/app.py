import os
from flask import Flask
from flask_restful import Api
from database.db import initialize_db
from routes.routes import initialize_routes
from dotenv import load_dotenv

load_dotenv()

app = Flask(__name__)
api = Api(app)

app.config.from_object('config.'+os.environ.get('CONFIG')+'Config')

initialize_db(app)
initialize_routes(api)

if __name__ == "__main__":
    ENVIRONMENT_DEBUG = bool(os.environ.get("APP_DEBUG", False))
    ENVIRONMENT_PORT = os.environ.get("APP_PORT", 5000)
    app.run(host="0.0.0.0", port=ENVIRONMENT_PORT, debug=ENVIRONMENT_DEBUG)
