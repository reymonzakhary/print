import os
from dotenv import load_dotenv

# Load environment variables from .env file
load_dotenv()

class Settings:
    """Application Configuration Settings"""
    REDIS_HOST: str = os.getenv("REDIS_HOST", "localhost")
    REDIS_PORT: int = int(os.getenv("REDIS_PORT", 6380))
    DATA_SET_FILE: str = os.getenv("DATA_SET_FILE", "data/data_set.json")
    BOXES_DATA_SET_FILE: str = os.getenv("BOXES_DATA_SET_FILE", "data/boxes_data_set.json")
    OPTIONS_DATA_SET_FILE: str = os.getenv("DATA_SET_FILE", "data/options_data_set.json")

# Create a single settings instance
settings = Settings()
