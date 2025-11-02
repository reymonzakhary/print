import requests
from fastapi import FastAPI
from pydantic import BaseModel
from typing import Dict
from fastapi import HTTPException

from api import APIService
from config import API_URL, API_TOKEN
from database import get_mongo_connection
from pipeline.sync_service import SyncPipelineService
from pipeline.generate_boobs import GenerateBoobsService
from pipeline.option_service import OptionService
from pipeline.sync_service import SyncPipelineService
from pipeline.utils import UtilsService
from pipeline.requests_models import LoginRequest, RegisterRequest, SecretResponse, DwdRegisterRequest
from pipeline.secrets_service import SecretService

app = FastAPI()

class SyncRequest(BaseModel):
    tenant_name: str
    tenant_id: str
    skus: list[str] = []  # List of SKUs (default empty)
    vendor: str  # List of SKUs (default empty)

@app.post("/login")
async def login(request: LoginRequest):
    tenant_id = request.tenant_id
    client = get_mongo_connection()
    secret_service = SecretService(client)
    response = secret_service.login(tenant_id)
    return response


@app.post("/register")
async def register(request: RegisterRequest):
    client = get_mongo_connection()
    tenant_id = request.tenant_id
    username = request.username
    password = request.password
    url = request.url

    secret_service = SecretService(client)
    response = secret_service.register(
        tenant_id=tenant_id, username=username, password=password, url=url
    )
    return response


@app.post("/dwd-register")
async def register(request: DwdRegisterRequest):
    client = get_mongo_connection()
    tenant_id = request.tenant_id
    url = request.url
    secret = request.secret
    user_id = request.user_id
    secret_service = SecretService(client)
    response = secret_service.dwd_register(tenant_id=tenant_id, secret=secret, user_id=user_id, url=url)
    return response



@app.post("/sync")
async def sync(request: SyncRequest):
    """FastAPI endpoint to trigger the sync pipeline."""
    response_data = []
    client = get_mongo_connection()
    api_service = APIService(client, str(request.tenant_id))
    for sku in request.skus:
        product = api_service.fetch_origin_products(sku).get('products_details')
        response = api_service.sync_print_com(data=product)
        response_data.append(response)

    return {
        "data": response_data,
        "status": 200,
        "message": "Sync has completed successfully"
    }

@app.get('/products-updates')
def products_updates():
    client = get_mongo_connection()
    option_service = OptionService(client)
    response = option_service.update_excludes_in_ops()
    return response

@app.post("/probo")
async def sync_probo(request: SyncRequest):
    """FastAPI endpoint to trigger the sync pipeline."""
    client = get_mongo_connection()  # ✅ Initialize MongoDB connection
    sync_service = SyncPipelineService(client)  # ✅ Instantiate the service
    response = sync_service.sync_propo_pipeline(request.tenant_name, request.tenant_id, request.skus,
                                                request.vendor)
    return response


class BoobsRequest(BaseModel):
    skus: list

class CategoryRequest(BaseModel):
    tenant_id: str

@app.get("/categories")
async def fetch_categories(request: CategoryRequest):
    """FastAPI endpoint to fetch products."""
    client = get_mongo_connection()
    api_service = APIService(client, request.tenant_id)  # ✅ Instantiate the service
    response = api_service.fetch_products()
    return response


@app.post("/get-boops")
async def get_boobs(request: BoobsRequest):
    client = get_mongo_connection()
    boobs = GenerateBoobsService(client)
    response = boobs.get_oops_by_skus(request.skus)

    return {
        "data": UtilsService.sanitize_slugs(response),
        "status": 200,
        "message": "BOOPs has retrieved successfully"
    }


# Define the request model (optional, depending on what you want in the request)
class PriceRequest(BaseModel):
    sku: str
    options: dict
    address: dict
    tenant_id: str

# Define the POST endpoint for getting a random price
@app.post("/get-price")
async def get_price(request: PriceRequest):
    client = get_mongo_connection()
    api_service = APIService(client, request.tenant_id)

    try:
        return {
            "data": {
                "product_price": api_service.fetch_price(request.sku, request.options),
                "product_shipping": api_service.fetch_shipping_possibilities(request.sku, request.options, request.address)
            },

            "status": 200,
            "message": "Price data has retrieved successfully"
        }
    except Exception as e:
        return {
            "status": 422,
            "message": "PRINT.COM >> {}".format(repr(e))
        }

class PlaceOrderRequest(BaseModel):
    tenant_id: str
    order: Dict

@app.post("/place-order")
def place_order(request: PlaceOrderRequest):
    client = get_mongo_connection()
    api_service = APIService(client, request.tenant_id)

    try:
        response = api_service.place_order_printcom(request.order)
        return {
            "status": 200,
            "message": "Order placed successfully",
            "data": response
        }

    except HTTPException as e:
        raise e
    except Exception as e:
        return {
            "status": 500,
            "message": f"Unexpected error: {repr(e)}"
        }
