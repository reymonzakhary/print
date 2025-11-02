import os
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

# Database Name
DB_NAME = os.getenv("DB_NAME")

# API Details
API_URL = os.getenv("API_URL")
PROBO_API_URL = os.getenv("PROBO_API_URL")
API_TOKEN = os.getenv("API_TOKEN")
PROBO_API_TOKEN = os.getenv("PROBO_API_TOKEN")

# MongoDB Collection Names
CATEGORY_COLL = os.getenv("CATEGORY_COLL")
BOX_COLL = os.getenv("BOX_COLL")
OPTION_COLL = os.getenv("OPTION_COLL")
BOOBS_COLL = os.getenv("BOOBS_COLL")
POLICY_COLL = os.getenv("POLICY_COLL")

LOGIN_URL = os.getenv("LOGIN_URL")
EMAIL = os.getenv("EMAIL")
PASSWORD = os.getenv("PASSWORD")
ENV_FILE = ".env"

PASS_THROUGH_PROXY = os.getenv("PASS_THROUGH_PROXY")
PROXY_HOSTNAME = os.getenv("PROXY_HOSTNAME")
