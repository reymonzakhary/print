# Calculation API Documentation

## Overview

This document provides comprehensive documentation for the Calculation microservice API after reorganization.

**Version**: 1.0
**Base URL**: `/api` (configured in main app)
**Date**: 2025-11-12

---

## Table of Contents

1. [Route Organization](#route-organization)
2. [API Endpoints](#api-endpoints)
3. [Authentication](#authentication)
4. [Request/Response Formats](#requestresponse-formats)
5. [Error Handling](#error-handling)
6. [Extensibility](#extensibility)
7. [Examples](#examples)

---

## Route Organization

### File Structure

```
microservices/calculation/calculation/routes/
├── index.js                    # Main router (delegates to v1)
└── v1/
    ├── index.js               # V1 main router
    ├── calculations.js        # Full calculation routes
    ├── semi-calculations.js   # Semi-calculation routes
    └── products.js            # Product data routes
```

### Design Principles

1. **Backward Compatibility**: All existing routes maintained without changes
2. **Modular Organization**: Routes grouped by functionality
3. **Versioning Ready**: V1 structure allows future V2 additions
4. **Clear Documentation**: Each route file contains comprehensive documentation
5. **Extensibility**: Can add new optional fields without breaking existing clients

---

## API Endpoints

### Full Calculation Endpoints

#### 1. Internal Full Calculation

```
POST /suppliers/:supplier_id/categories/:slug/products/calculate/price
```

**Purpose**: Calculate complete product pricing with internal profit margins

**Parameters**:
- `supplier_id` (path): Tenant/supplier identifier
- `slug` (path): Category slug

**Request Body**:
```javascript
{
    product: Array,      // Product items configuration
    quantity: Number,    // Units to produce
    contract: String     // Optional contract reference
}
```

**Response**: Full calculation response with profit data

**Use Case**: Internal systems, admin panels, pricing analysis

---

#### 2. Shop Full Calculation

```
POST /shop/suppliers/:supplier_id/categories/:slug/products/calculate/price
```

**Purpose**: Calculate product pricing for customer-facing systems

**Parameters**: Same as internal endpoint

**Request Body**: Same as internal endpoint

**Response**: Full calculation response without internal profit data

**Use Case**: Webshop, customer portals, external API integrations

---

#### 3. Shop Price List

```
POST /shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/list
```

**Purpose**: Calculate prices across multiple quantity ranges

**Parameters**: Same as internal endpoint

**Request Body**: Same as internal endpoint (quantity field will be overridden by range list)

**Response**: Single response with consolidated prices array containing all quantity variations

**Use Case**: Displaying tiered pricing tables, volume discount visualization

**Special Behavior**:
- Automatically uses category-defined quantity ranges
- Performs calculation for each quantity in range
- Consolidates results into single response
- Prices array contains all quantity variations

---

### Semi-Calculation Endpoints

#### 4. Internal Semi-Calculation

```
POST /suppliers/:supplier_id/categories/:slug/products/calculate/price/semi
```

**Purpose**: Simplified/preliminary pricing calculation (internal)

**Parameters**:
- `supplier_id` (path): Tenant/supplier identifier
- `slug` (path): Category slug

**Request Body**:
```javascript
{
    product: Object,        // Simplified product configuration
    quantity: Number,       // Units to produce
    vat: Number,           // VAT percentage
    dlv: Number,           // Optional delivery time filter
    vat_override: Boolean, // Optional VAT override flag
    contract: String       // Optional contract reference
}
```

**Response**: Semi-calculation response with profit data

**Use Case**: Quick internal quotes, simplified configurations

---

#### 5. Shop Semi-Calculation

```
POST /shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/semi
```

**Purpose**: Simplified pricing for customers

**Parameters**: Same as internal semi-calculation

**Request Body**: Same as internal semi-calculation

**Response**: Semi-calculation response without internal profit data

**Use Case**: Customer quick quotes, simplified product builders

---

#### 6. Shop Semi-Calculation Price List

```
POST /shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/semi/list
```

**Purpose**: Semi-calculation across multiple quantities

**Parameters**: Same as internal endpoint

**Request Body**: Same as internal semi-calculation

**Response**: Consolidated semi-calculation with all quantity variations

**Use Case**: Quick tiered pricing for simplified products

---

### Product Data Endpoints

#### 7. Product Items

```
POST /suppliers/:supplier_id/products/items
```

**Purpose**: Retrieve product configuration data without calculation

**Parameters**:
- `supplier_id` (path): Tenant/supplier identifier

**Request Body**:
```javascript
{
    products: Array,          // Product identifiers
    calculation_type: String  // "full_calculation" or "semi_calculation"
}
```

**Response**: Array of product items with options, materials, formats, and constraints

**Use Case**: Product configurators, form builders, pre-calculation data fetching

---

## Authentication

**Current Status**: Not explicitly shown in routes (likely handled by middleware in main app)

**Recommended**:
- Use JWT tokens or API keys
- Apply authentication middleware at app level
- Consider rate limiting for shop endpoints

---

## Request/Response Formats

### Standard Request Headers

```
Content-Type: application/json
Accept: application/json
```

### Standard Response Structure

All calculation endpoints return a structured response:

```javascript
{
    type: "print",
    connection: String,           // supplier_id
    external: String,             // External system reference
    external_id: String,          // External identifier
    external_name: String,        // Tenant/supplier name
    calculation_type: String,     // "full_calculation" or "semi_calculation"
    items: Array,                 // Product items
    product: Object,              // Original product request
    category: Object,             // Category details
    margins: Array | Object,      // Margin calculations
    divided: Boolean,             // If calculation was split
    quantity: Number,             // Requested quantity
    calculation: Array,           // Detailed calculation breakdown
    prices: [{                    // Price variations
        id: String,               // Unique price identifier (hash)
        pm: String,               // Price model
        qty: Number,              // Quantity for this price
        dlv: {                    // Delivery info
            days: Number,
            title: String
        },
        gross_price: Number,      // Price before margin
        gross_ppp: Number,        // Gross price per piece
        p: Number,                // Final price
        ppp: Number,              // Price per piece
        selling_price_ex: Number, // Selling price excluding VAT
        selling_price_inc: Number,// Selling price including VAT
        profit: Number | null,    // Profit amount (internal only)
        discount: Array,          // Applied discounts
        margins: Object | Array,  // Margin details (internal only)
        vat: Number,              // VAT percentage
        vat_p: Number,            // VAT amount
        vat_ppp: Number           // VAT amount per piece
    }]
}
```

### Product Items Response

```javascript
[
    {
        // Product item structure
        // Contains: options, materials, formats, constraints, etc.
    }
]
```

---

## Error Handling

### Current Error Format

**Status Code**: 200 (⚠️ Note: This should be fixed to use proper HTTP codes)

**Error Response**:
```javascript
{
    message: String,  // Error description
    status: 422       // Error code (422 = validation error)
}
```

### Recommended Error Format (Future)

**Status Code**: Actual HTTP status code (422, 404, 500, etc.)

**Error Response**:
```javascript
{
    error: {
        code: String,      // Error code (e.g., "INVALID_QUANTITY")
        message: String,   // Human-readable message
        field: String,     // Field that caused error (if applicable)
        details: Object    // Additional error context
    }
}
```

### Common Error Codes

- `422`: Validation error (invalid input)
- `404`: Resource not found (supplier, category, product)
- `500`: Internal server error
- `503`: Service unavailable

---

## Extensibility

### Adding New Fields

**Important**: See comprehensive documentation in:
- `MACHINE_MODEL_EXTENSIONS.md` - What can be added to machine models
- `CATALOGUE_MODEL_EXTENSIONS.md` - What can be added to catalogue models
- `PAYLOAD_EXTENSIONS.md` - What can be added to request/response payloads

### Extensibility Principles

1. **Never Remove Existing Fields**: All existing fields must remain
2. **Optional New Fields**: All new fields must be optional with defaults
3. **Backward Compatible**: Old API clients must continue working
4. **Version When Needed**: Use V2 endpoints for breaking changes
5. **Document Extensions**: Update documentation when adding fields

### Example Extensions

#### Request Extensions:
```javascript
{
    // Existing fields
    product: [...],
    quantity: 2000,

    // NEW: Optional extensions
    printing_method: "offset",
    color_config: {
        notation: "4/4"
    },
    delivery_requirements: {
        required_date: "2025-11-25"
    }
}
```

#### Response Extensions:
```javascript
{
    // Existing fields
    type: "print",
    prices: [...],

    // NEW: Optional extensions
    production_timeline: {
        estimated_delivery: "2025-11-14"
    },
    positioning: {
        products_per_sheet: 2,
        sheet_utilization: 78.5
    },
    material_usage: {
        total_sheets: 1070,
        waste_percentage: 7.0
    }
}
```

---

## Examples

### Example 1: Simple Digital Print Calculation

**Request**:
```bash
POST /shop/suppliers/tenant_123/categories/business-cards/products/calculate/price
Content-Type: application/json

{
    "product": [
        {
            "box": {"calc_ref": "format"},
            "option": {"width": 85, "height": 55}
        },
        {
            "box": {"calc_ref": "material"},
            "option": {"gsm": 300, "type": "coated"}
        },
        {
            "box": {"calc_ref": "printing_colors"},
            "option": {"front": 4, "back": 4}
        }
    ],
    "quantity": 500
}
```

**Response**:
```javascript
{
    "type": "print",
    "connection": "tenant_123",
    "external_name": "Acme Printing",
    "calculation_type": "full_calculation",
    "quantity": 500,
    "prices": [
        {
            "id": "abc123def456",
            "qty": 500,
            "dlv": {"days": 3, "title": "Standard"},
            "p": 125.00,
            "ppp": 0.25,
            "selling_price_ex": 125.00,
            "selling_price_inc": 151.25,
            "vat": 21,
            "vat_p": 26.25,
            "margins": []  // Empty for shop endpoint
        }
    ],
    // ... other fields
}
```

---

### Example 2: Price List Request

**Request**:
```bash
POST /shop/suppliers/tenant_123/categories/flyers/products/calculate/price/list
Content-Type: application/json

{
    "product": [...],  // Same as above
    "quantity": 100    // Will be overridden by range list
}
```

**Response**:
```javascript
{
    "type": "print",
    "calculation_type": "full_calculation",
    "prices": [
        {"qty": 100, "ppp": 0.50, ...},
        {"qty": 250, "ppp": 0.35, ...},
        {"qty": 500, "ppp": 0.28, ...},
        {"qty": 1000, "ppp": 0.22, ...},
        {"qty": 2500, "ppp": 0.18, ...}
    ]
}
```

---

### Example 3: Product Items Fetch

**Request**:
```bash
POST /suppliers/tenant_123/products/items
Content-Type: application/json

{
    "products": ["business_cards", "flyers"],
    "calculation_type": "full_calculation"
}
```

**Response**:
```javascript
[
    {
        "product_id": "business_cards",
        "name": "Business Cards",
        "options": [
            {
                "box": {"calc_ref": "format", "name": "Format"},
                "available_options": [
                    {"width": 85, "height": 55, "name": "Standard"},
                    {"width": 90, "height": 50, "name": "US"}
                ]
            },
            {
                "box": {"calc_ref": "material", "name": "Paper"},
                "available_options": [...]
            }
        ]
    }
]
```

---

### Example 4: Semi-Calculation

**Request**:
```bash
POST /shop/suppliers/tenant_123/categories/posters/products/calculate/price/semi
Content-Type: application/json

{
    "product": {
        "format": {"width": 420, "height": 594},
        "material": {"gsm": 150},
        "colors": {"front": 4, "back": 0}
    },
    "quantity": 100,
    "vat": 21
}
```

**Response**:
```javascript
{
    "type": "print",
    "calculation_type": "semi_calculation",
    "quantity": 100,
    "prices": [
        {
            "qty": 100,
            "p": 85.00,
            "ppp": 0.85,
            "selling_price_inc": 102.85,
            "vat": 21
        }
    ]
}
```

---

## Migration Guide

### From Old Route Structure to New

**No Changes Required!**

The new route structure is 100% backward compatible. All existing endpoints work exactly as before:

| Old Route | Status | New Location |
|-----------|--------|--------------|
| `/suppliers/:supplier_id/categories/:slug/products/calculate/price` | ✅ Works | `routes/v1/calculations.js` |
| `/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price` | ✅ Works | `routes/v1/calculations.js` |
| `/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/list` | ✅ Works | `routes/v1/calculations.js` |
| `/suppliers/:supplier_id/categories/:slug/products/calculate/price/semi` | ✅ Works | `routes/v1/semi-calculations.js` |
| `/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/semi` | ✅ Works | `routes/v1/semi-calculations.js` |
| `/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/semi/list` | ✅ Works | `routes/v1/semi-calculations.js` |
| `/suppliers/:supplier_id/products/items` | ✅ Works | `routes/v1/products.js` |

### Benefits of New Structure

1. **Better Organization**: Routes grouped by functionality
2. **Improved Documentation**: Each route file has comprehensive comments
3. **Easier Maintenance**: Clear separation of concerns
4. **Future-Ready**: Can add V2 routes without touching V1
5. **Clearer Purpose**: Route names indicate internal vs shop usage

---

## Future Enhancements (V2 Considerations)

### Potential V2 Features

1. **Proper HTTP Status Codes**: Use 422, 404, 500 instead of 200 with error in body
2. **GraphQL Support**: Alternative to REST for flexible queries
3. **Webhooks**: Notify when calculation completes (for async processing)
4. **Batch Calculations**: Calculate multiple products in one request
5. **Caching**: Cache-Control headers for repeated calculations
6. **Compression**: Gzip response compression
7. **Pagination**: For large price lists
8. **Filtering**: Filter results by delivery time, price range, etc.

### V2 Route Examples

```
POST /v2/calculations/full
POST /v2/calculations/semi
POST /v2/calculations/batch
GET  /v2/products/:product_id/options
POST /v2/calculations/:id/optimize
```

---

## Performance Considerations

### Current Performance

- Response time depends on calculation complexity
- Larger quantities may take longer
- Price list endpoints perform multiple calculations

### Optimization Recommendations

1. **Caching**: Cache calculations for repeated requests
2. **Async Processing**: Use job queues for complex calculations
3. **Database Indexing**: Ensure proper indexes on supplier_id, slug
4. **Connection Pooling**: Optimize MongoDB connections
5. **Monitoring**: Add performance metrics and logging

---

## Testing

### Manual Testing

Use tools like:
- Postman
- cURL
- HTTPie

**Example cURL**:
```bash
curl -X POST \
  http://localhost:3000/api/shop/suppliers/tenant_123/categories/business-cards/products/calculate/price \
  -H 'Content-Type: application/json' \
  -d '{
    "product": [...],
    "quantity": 500
  }'
```

### Automated Testing

Recommended test coverage:
- Unit tests for controllers
- Integration tests for complete flows
- API contract tests
- Load testing for performance

---

## Support & Contact

### Documentation Resources

- `MACHINE_MODEL_EXTENSIONS.md` - Machine model extensibility
- `CATALOGUE_MODEL_EXTENSIONS.md` - Catalogue model extensibility
- `PAYLOAD_EXTENSIONS.md` - Request/response payload extensibility
- `OFFSET_PRINTING_IMPLEMENTATION.md` - Offset printing implementation guide
- `REFACTORING_PLAN.md` - Overall refactoring strategy

### Related Services

- **Machines Service**: `/microservices/machines`
- **Catalogues Service**: `/microservices/catalogues`
- **Boxes Service**: `/microservices/boxes`
- **Options Service**: `/microservices/options`

---

## Changelog

### Version 1.0 (2025-11-12)

- **Route Reorganization**: Restructured routes into v1 directory
- **Documentation**: Added comprehensive inline documentation
- **Backward Compatibility**: All existing routes maintained
- **Extensibility Documentation**: Created detailed extension guides
- **API Documentation**: Created this comprehensive API guide

---

## Glossary

- **Semi-Calculation**: Simplified pricing calculation with reduced complexity
- **Full Calculation**: Complete pricing calculation including all cost factors
- **Price List**: Calculations across multiple quantity tiers
- **GSM**: Grams per Square Meter (paper weight)
- **PPP**: Price Per Piece
- **VAT**: Value Added Tax
- **DLV**: Delivery time
- **Calc Ref**: Calculation reference (used to identify option types)
- **Supplier**: Tenant or print shop
- **Slug**: URL-friendly category identifier

---

**Document Version**: 1.0
**Last Updated**: 2025-11-12
**Maintained By**: Development Team
