from services.redisearch_service import RedisearchService
from services.boxes_redisearch_service import RedisearchService as BoxRedisService
from services.options_redisearch_service import RedisearchService as OptionRedisService  # if you have options too

# Initialize Redisearch Service
redis_service = RedisearchService()
redis_service.create_index()

# Create box index
box_service = BoxRedisService()
box_service.create_index()


# Create option index (optional)
option_service = OptionRedisService()
option_service.create_index()