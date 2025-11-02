# De-Groot Plugin

This plugin provides Flask-based API integration with the de-groot (GrootsGedrukt) API, following the same structure as the DWD module.

## Configuration

Copy `.env_example` to `.env` and configure your settings:

```bash
cp .env_example .env
```

### Required Environment Variables

- `DEGROOT_URL`: Base URL for the de-groot API (default: https://api.grootsgedrukt.nl)
- `DEGROOT_TOKEN`: API authentication token
- `CONFIG`: Flask configuration (Development/Production)

## API Endpoints

### Categories
- `GET /categories` - Get all articles (categories) from de-groot API
  ```bash
  curl -X GET "http://localhost:5000/categories" \
    -H "Content-Type: application/json" \
    -d '{"tenant_id": "your_tenant_id"}'
  ```

- `GET /category/{articlenumber}` - Get single article/category details
  ```bash
  curl -X GET "http://localhost:5000/category/005" \
    -H "Content-Type: application/json" \
    -d '{"tenant_id": "your_tenant_id"}'
  ```

- `GET /category` - Get single article/category details (articlenumber in body)
  ```bash
  curl -X GET "http://localhost:5000/category" \
    -H "Content-Type: application/json" \
    -d '{
      "tenant_id": "your_tenant_id",
      "articlenumber": "005"
    }'
  ```

- `GET /article-metadata/{articlenumber}` - Get flat list of available option values
  ```bash
  curl -X GET "http://localhost:5000/article-metadata/005" \
    -H "Content-Type: application/json" \
    -d '{"tenant_id": "your_tenant_id"}'
  ```

- `GET /article-metadata` - Get flat list of available option values (articlenumber in body)
  ```bash
  curl -X GET "http://localhost:5000/article-metadata" \
    -H "Content-Type: application/json" \
    -d '{
      "tenant_id": "your_tenant_id",
      "articlenumber": "005"
    }'
  ```

- `GET /segment-mapping/{articlenumber}` - Get mapping between options and SKU segments
  ```bash
  curl -X GET "http://localhost:5000/segment-mapping/005" \
    -H "Content-Type: application/json" \
    -d '{"tenant_id": "your_tenant_id"}'
  ```

- `GET /segment-mapping` - Get mapping between options and SKU segments (articlenumber in body)
  ```bash
  curl -X GET "http://localhost:5000/segment-mapping" \
    -H "Content-Type: application/json" \
    -d '{
      "tenant_id": "your_tenant_id",
      "articlenumber": "005"
    }'
  ```

### Secrets Management
- `GET /secrets` - Get API credentials for a tenant
  ```bash
  curl -X GET "http://localhost:5000/secrets" \
    -H "Content-Type: application/json" \
    -d '{"tenant_id": "your_tenant_id"}'
  ```

### Data Import
- `POST /import` - Import supplier data for a specific article
  ```bash
  curl -X POST "http://localhost:5000/import" \
    -H "Content-Type: application/json" \
    -d '{
      "tenant_id": "your_tenant_id",
      "articlenumber": "article_number_here"
    }'
  ```

### Validation
- `POST /validate-pair` - Validate if two option values can be used together
  ```bash
  curl -X POST "http://localhost:5000/validate-pair" \
    -H "Content-Type: application/json" \
    -d '{
      "tenant_id": "your_tenant_id",
      "articlenumber": "article_number",
      "option1_id": 123,
      "option2_id": 456
    }'
  ```

### Pricing
- `POST /get-price` - Get price for specific product configuration
  ```bash
  # Get price by articlecode (direct)
  curl -X POST "http://localhost:5000/get-price" \
    -H "Content-Type: application/json" \
    -d '{
      "tenant_id": "your_tenant_id",
      "articlecode": "005-090-PWS-A3ST-FD40-PZLA-ASCH-GBOR-NBUN",
      "quantity": 100,
      "delivery_type": "Standaard"
    }'
  
  # Get price by options (find articlecode first)
  curl -X POST "http://localhost:5000/get-price" \
    -H "Content-Type: application/json" \
    -d '{
      "tenant_id": "your_tenant_id",
      "articlenumber": "005",
      "options": {"format": 102, "bedrukking": 41},
      "quantity": 50,
      "delivery_type": "NextDay"
    }'
  ```

### Data Sync
- `POST /sync` - Sync complete article data (categories, options, products)
  ```bash
  curl -X POST "http://localhost:5000/sync" \
    -H "Content-Type: application/json" \
    -d '{
      "tenant_id": "your_tenant_id",
      "articlenumber": "article_number"
    }'
  ```

### Orders
- `GET /orders` - Get all orders
- `GET /orders/{uuid}` - Get specific order
- `POST /order` - Create new order

### Webhooks
- `POST /webhooks` - Handle webhook events from de-groot

## Testing

Run the API test script to verify integration:

```bash
python test_api.py
```

## Architecture

The plugin follows the same structure as the DWD module:

- **Services Layer** (`services/`): Core business logic and API integration
- **External Layer** (`external/`): Import/export functionality
- **Routes Layer** (`routes/`): Flask-RESTful API endpoints
- **Models**: Uses existing MongoDB models for data persistence

## Key Features

- ✅ Complete Flask-RESTful API structure
- ✅ Tenant-based credential management
- ✅ Article/category fetching and processing
- ✅ Option validation and pricing
- ✅ Supplier data import with similarity matching
- ✅ Webhook handling for order events
- ✅ Error handling and logging
- ✅ Following DWD module patterns for consistency
