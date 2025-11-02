import pytest
from services.redisearch_service import RedisearchService

# Initialize RedisearchService
redis_service = RedisearchService()

def test_redis_connection():
    """Test if Redis connection is successful."""
    assert redis_service.client.ping() == True

def test_index_creation():
    """Test if the Redisearch index is created."""
    redis_service.create_index()
    existing_indexes = redis_service.client.execute_command("FT._LIST")
    assert redis_service.index_name in existing_indexes

# def test_insert_and_search_product_with_iso():
#     """Test inserting a product with language (ISO) and searching for it."""
#     # Insert a product with names in different languages (English and Spanish)
#     product_data = [
#         {"iso": "en", "display_name": "Test Product"},
#         {"iso": "es", "display_name": "Producto de prueba"}
#     ]
#     redis_service.insert_product("99999", product_data)
#
#     # Search for the product in English (ISO 'en')
#     results_en = redis_service.search_products("Test Product", "en")
#     assert len(results_en) > 0
#     assert results_en[0]["sku"] == "99999"
#     assert "Test Product" in [name["display_name"] for name in results_en[0]["name"] if name["iso"] == "en"]
#
#     # Search for the product in Spanish (ISO 'es')
#     results_es = redis_service.search_products("Producto de prueba", "es")
#     assert len(results_es) > 0
#     assert results_es[0]["sku"] == "99999"
#     assert "Producto de prueba" in [name["display_name"] for name in results_es[0]["names"] if name["iso"] == "es"]
