from typing import Optional

from pydantic import BaseModel


class LoginRequest(BaseModel):
    tenant_id: str

class RegisterRequest(BaseModel):
    tenant_id: str
    username: str
    password: str
    url: str

class DwdRegisterRequest(BaseModel):
    tenant_id: str
    url: str
    secret: str
    user_id: str

class SecretResponse(BaseModel):
    tenant_id: str
    username: str
    password: str
    url: str
    token: str
