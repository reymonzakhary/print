import json
import os
from dotenv import load_dotenv
from services.redisearch_service import RedisearchService

load_dotenv()
DATA_SET_FILE = os.getenv("DATA_SET_FILE", "data_set.json")

if not os.path.exists(DATA_SET_FILE):
    print(f"{DATA_SET_FILE} not found. Creating a new one...")

    default_data = {
        "sample_sku_1": {
            "names": [{"iso": "en", "display_name": "Sample Product 1"}],
            "origin_names": [{"iso": "en", "origin_name": "Original Sample Product 1"}]
        },
        "sample_sku_2": {
            "names": [{"iso": "en", "display_name": "Sample Product 2"}],
            "origin_names": [{"iso": "en", "origin_name": "Original Sample Product 2"}]
        }
    }

    with open(DATA_SET_FILE, "w", encoding="utf-8") as file:
        json.dump(default_data, file, indent=4, ensure_ascii=False)

redis_service = RedisearchService()

with open(DATA_SET_FILE, "r", encoding="utf-8") as file:
    products = json.load(file)


# for sku, data in products.items():
#     names = data.get("names", [])
#     origin_names = data.get("origin_names", [])
#     redis_service.insert_product(sku, names, origin_names)
#
# print("Products successfully inserted into Redis!")
