import requests
from fastapi import HTTPException
from pydantic import BaseModel

from config import DB_NAME
from .requests_models import LoginRequest, RegisterRequest, SecretResponse


class SecretService:
    def __init__(self, client):
        """Initialize PolicyService with a MongoDB client."""
        self.client = client
        self.db = client[DB_NAME]
        self.plugin_secrets = self.db['secrets']

    def check_auth(self, username, password, auth_url):
        """Check if the provided username and password are valid by calling the external API."""
        payload = {
            "credentials": {
                "username": username,
                "password": password
            }
        }

        response = requests.post(f"{auth_url}/login", json=payload)

        if response.status_code == 200:
            return response.json()  # This should return the token or some other data
        else:
            raise HTTPException(status_code=401, detail="Authentication failed. Invalid username or password.")

    def login(self, tenant_id, expired=False):
        """Fetch tenant secrets and refresh token if needed."""
        secret_data = self.plugin_secrets.find_one({"tenant_id": tenant_id})

        if not secret_data:
            raise HTTPException(status_code=401, detail="Invalid tenant_id or token.")

        username = secret_data.get("username")
        password = secret_data.get("password")
        auth_url = secret_data.get("url")
        token = secret_data.get("token")

        if not token or expired == True:
            # If no token exists, generate a new one
            return self.refresh_token(tenant_id, username, password, auth_url)

        return SecretResponse(
            tenant_id=tenant_id,
            username=username,
            password=password,
            url=auth_url,
            token=token
        )

    def refresh_token(self, tenant_id, username, password, auth_url):
        """Refresh the authentication token if it is expired or missing."""
        if not username or not password or not auth_url:
            raise HTTPException(status_code=401, detail="Missing credentials for token refresh.")

        new_token = self.check_auth(username, password, auth_url)

        # Update the token in MongoDB
        self.plugin_secrets.update_one(
            {"tenant_id": tenant_id},
            {"$set": {"token": new_token}}
        )

        return SecretResponse(
            tenant_id=tenant_id,
            username=username,
            password=password,
            url=auth_url,
            token=new_token
        )

    def register(self, tenant_id, username, password, url):
        """Register or update the secret information for a tenant."""

        # Step 1: Check if the username and password are valid by calling the external API
        auth_response = self.check_auth(username, password, url)

        # Step 2: Proceed to register or update the secret information in MongoDB
        existing_secret = self.plugin_secrets.find_one({"tenant_id": tenant_id})

        if existing_secret:
            # Update the existing record
            self.plugin_secrets.update_one(
                {"tenant_id": tenant_id},
                {"$set": {"username": username, "password": password, "url": url, "token": auth_response}}
            )
            return {"message": "Record updated successfully"}
        else:
            # Insert a new record
            new_secret = {
                "tenant_id": tenant_id,
                "username": username,
                "password": password,
                "url": url,
                "token": auth_response  # Assuming 'token' is returned
            }
            self.plugin_secrets.insert_one(new_secret)
            return {"message": "Record created successfully"}

    def dwd_register(self, tenant_id, url, secret, user_id):
        """Register or update the secret information for a tenant."""

        existing_secret = self.plugin_secrets.find_one({"tenant_id": tenant_id})

        if existing_secret:
            # Update the existing record
            self.plugin_secrets.update_one(
                {"tenant_id": tenant_id},
                {"$set": {"dwd_secret": secret, "dwd_user_id": user_id, "dwd_url": url}}
            )
            return {"message": "DWD Account updated successfully"}
        else:
            # Insert a new record
            new_secret = {
                "tenant_id": tenant_id,
                "dwd_secret": secret,
                "dwd_user_id": user_id,
                "dwd_url": url,
            }
            self.plugin_secrets.insert_one(new_secret)
            return {"message": "DWD Account created successfully"}
