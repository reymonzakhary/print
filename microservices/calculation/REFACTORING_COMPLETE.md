# Calculation Microservice - Refactoring Complete (Phase 1)

## 🎉 What Was Refactored

This document describes the completed refactoring of the calculation microservice from a God Object pattern to a clean, service-based architecture.

---

## Before & After

### Before (God Object Pattern)

```
FetchProduct.js (971 lines)
├── 30+ instance properties
├── 13 methods doing everything
├── Direct database access mixed with business logic
├── No separation of concerns
└── Impossible to test individual components
```

**Problems:**
- ❌ Single class responsible for everything
- ❌ Direct database queries in business logic
- ❌ No testability
- ❌ Code duplication
- ❌ Hard to maintain and extend

### After (Service-Based Architecture)

```
services/
├── CalculationEngine.js         # Main orchestrator
├── CategoryService.js            # Category operations
├── ProductService.js             # Product/Box/Option matching
├── MarginService.js              # Margin calculations
├── DiscountService.js            # Discount logic
└── PriceFormatterService.js      # Price formatting

repositories/
├── CategoryRepository.js         # Category data access
├── ProductRepository.js          # Product data access
└── CatalogueRepository.js        # Catalogue data access

controllers/
├── CalculationControllerV2.js    # Refactored internal endpoint
├── ShopCalculationControllerV2.js # Refactored shop endpoint
└── ShopCalculationPriceListControllerV2.js # Refactored price list

errors/
└── index.js                      # Custom error classes
```

**Benefits:**
- ✅ Single Responsibility Principle
- ✅ Separation of concerns (data access vs business logic)
- ✅ Testable components
- ✅ Reusable services
- ✅ Proper error handling with HTTP status codes
- ✅ Easy to maintain and extend

---

## Architecture Overview

### Layer Structure

```
┌─────────────────────────────────────────┐
│          Controllers (HTTP)              │
│  CalculationControllerV2                 │
│  ShopCalculationControllerV2             │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│      Service Layer (Business Logic)      │
│  ┌─────────────────────────────────┐   │
│  │    CalculationEngine            │   │  ◄── Orchestrator
│  │  (Coordinates everything)       │   │
│  └───────┬─────────────────────────┘   │
│          │                               │
│  ┌───────▼───────────┬────────────┐    │
│  │ CategoryService   │            │    │
│  │ ProductService    │ Services   │    │
│  │ MarginService     │            │    │
│  │ DiscountService   │            │    │
│  │ PriceFormatter    │            │    │
│  └───────┬───────────┴────────────┘    │
└──────────┼──────────────────────────────┘
           │
           ▼
┌─────────────────────────────────────────┐
│    Repository Layer (Data Access)        │
│  CategoryRepository                      │
│  ProductRepository                       │
│  CatalogueRepository                     │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│          Database (MongoDB)              │
│  supplier_categories                     │
│  supplier_boxes                          │
│  supplier_options                        │
│  supplier_catalogues                     │
└─────────────────────────────────────────┘
```

---

## What Each Component Does

### Services

#### CalculationEngine
**Purpose**: Main orchestrator that coordinates the entire calculation flow

**Responsibilities**:
- Validates inputs
- Coordinates all services
- Manages calculation workflow
- Builds final response

**Key Methods**:
- `calculate(params)` - Main entry point
- `_validateInputs()` - Input validation
- `_fetchCatalogues()` - Fetch materials
- `_runMachineCalculations()` - Run machine calculations
- `_formatPrices()` - Format prices
- `_buildResponse()` - Build API response

#### CategoryService
**Purpose**: Handles category operations and validation

**Responsibilities**:
- Fetch categories from database (via repository)
- Validate category has machines
- Validate category has product configuration
- Extract delivery options

**Key Methods**:
- `getCategory(slug, supplierId)` - Get validated category
- `exists(slug, supplierId)` - Check if category exists
- `getDeliveryOptions(category)` - Extract delivery options
- `validateQuantity(category, quantity)` - Validate quantity

#### ProductService
**Purpose**: Handles product matching (boxes + options)

**Responsibilities**:
- Fetch boxes and options from database (via repository)
- Match request items with database products
- Apply category-specific option configuration
- Handle dynamic options
- Extract specific product types (format, material, etc.)

**Key Methods**:
- `getMatchedProducts(items, supplierId, boops, categoryId)` - Match products
- `filterByCalcRef(products, calcRef)` - Filter by type
- `getFormat(products)` - Extract format
- `getMaterial(products)` - Extract material
- `getWeight(products)` - Extract weight
- `getPrintingColors(products)` - Extract colors

#### MarginService
**Purpose**: Handles margin calculations

**Responsibilities**:
- Fetch margins from margin microservice
- Apply margins to prices
- Calculate profit
- Hide margins for shop calculations

**Key Methods**:
- `getMargins(supplierId, categoryId, quantity, internal)` - Fetch margins
- `applyMargins(grossPrice, margins, internal)` - Apply margins
- `calculateProfit(grossPrice, sellingPrice)` - Calculate profit
- `calculateMarginPercentage(grossPrice, sellingPrice)` - Calculate margin %

#### DiscountService
**Purpose**: Handles discount calculations

**Responsibilities**:
- Find applicable discounts from contracts
- Apply discounts to prices
- Validate discount applicability
- Calculate total discounts

**Key Methods**:
- `getDiscount(contract, categoryId, quantity)` - Find discount
- `applyDiscount(price, discount)` - Apply discount
- `isApplicable(discount, quantity)` - Check if applicable
- `calculateTotalDiscount(items, discount)` - Calculate total

#### PriceFormatterService
**Purpose**: Formats prices for API responses

**Responsibilities**:
- Format price objects with all calculations
- Calculate VAT
- Calculate per-piece prices
- Generate unique price IDs
- Format delivery information
- Merge price lists

**Key Methods**:
- `formatPriceObject(params)` - Format single price
- `formatPrices(prices)` - Format multiple prices
- `mergePriceObjects(priceObjects)` - Merge price lists

---

### Repositories

#### CategoryRepository
**Purpose**: Database access for categories

**Methods**:
- `findBySlugAndSupplier(slug, supplierId)` - Find category
- `findWithRelations(slug, supplierId)` - Find with relations
- `exists(slug, supplierId)` - Check existence

#### ProductRepository
**Purpose**: Database access for boxes and options

**Methods**:
- `findBoxesByIds(boxIds, supplierId)` - Find multiple boxes
- `findOptionsByIds(optionIds, supplierId)` - Find multiple options
- `findBoxesAndOptions(boxIds, optionIds, supplierId)` - Find both in parallel
- `findBoxById(boxId, supplierId)` - Find single box
- `findOptionById(optionId, supplierId)` - Find single option

#### CatalogueRepository
**Purpose**: Database access for catalogues (materials)

**Methods**:
- `findByCriteria(supplierId, criteria)` - Find by criteria
- `findByMaterialAndGsm(supplierId, materialLink, grsLink)` - Find specific
- `findAllBySupplier(supplierId)` - Find all for supplier

---

### Controllers

#### CalculationControllerV2
**Purpose**: HTTP endpoint for internal calculations (with margins)

**Features**:
- Uses CalculationEngine
- Includes margins and profit in response
- Proper HTTP status codes (400, 404, 422, 500)
- Detailed error responses

#### ShopCalculationControllerV2
**Purpose**: HTTP endpoint for shop calculations (without margins)

**Features**:
- Uses CalculationEngine
- Excludes margins and profit from response
- Proper HTTP status codes
- Customer-friendly error messages

#### ShopCalculationPriceListControllerV2
**Purpose**: HTTP endpoint for price lists across multiple quantities

**Features**:
- Fetches quantity ranges from category
- Runs calculation for each quantity
- Merges results into single response
- Continues on individual quantity failures

---

### Error Classes

Custom error classes with proper HTTP status codes:

```javascript
ApplicationError       // Base class
ValidationError        // 400 - Invalid input
NotFoundError          // 404 - Resource not found
CalculationError       // 422 - Calculation failed
ExternalServiceError   // 503 - External service unavailable
DatabaseError          // 500 - Database error
```

---

## How to Use the Refactored Code

### Option 1: Use New V2 Controllers (Recommended)

Update your routes to use the new V2 controllers:

```javascript
// routes/v1/calculations.js
const CalculationControllerV2 = require('../../controllers/CalculationControllerV2');

router.post(
    '/suppliers/:supplier_id/categories/:slug/products/calculate/price',
    CalculationControllerV2.calculate
);
```

### Option 2: Use CalculationEngine Directly

For custom integrations:

```javascript
const CalculationEngine = require('../services/CalculationEngine');

const engine = new CalculationEngine();

const result = await engine.calculate({
    slug: 'business-cards',
    supplierId: 'tenant_123',
    productItems: [...],
    quantity: 500,
    contract: null,
    internal: true,
    vat: 21,
    vatOverride: false,
    requestDlv: null
});
```

### Option 3: Use Individual Services

For specific functionality:

```javascript
const CategoryService = require('../services/CategoryService');
const ProductService = require('../services/ProductService');

const categoryService = new CategoryService();
const { category, machines, boops } = await categoryService.getCategory(slug, supplierId);

const productService = new ProductService();
const products = await productService.getMatchedProducts(items, supplierId, boops, categoryId);
```

---

## Testing

### Unit Testing Services

Services are now easily testable:

```javascript
const CategoryService = require('../services/CategoryService');
const CategoryRepository = require('../repositories/CategoryRepository');

describe('CategoryService', () => {
    it('should fetch category', async () => {
        // Mock repository
        const mockRepo = {
            findBySlugAndSupplier: jest.fn().mockResolvedValue({
                _id: '123',
                slug: 'test',
                machine: [{ _id: 'm1' }],
                boops: [{ _id: 'b1' }]
            })
        };

        const service = new CategoryService(mockRepo);
        const result = await service.getCategory('test', 'supplier1');

        expect(result.category.slug).toBe('test');
        expect(mockRepo.findBySlugAndSupplier).toHaveBeenCalledWith('test', 'supplier1');
    });
});
```

### Integration Testing

Test the full calculation flow:

```javascript
const CalculationEngine = require('../services/CalculationEngine');

describe('CalculationEngine Integration', () => {
    it('should calculate price for business cards', async () => {
        const engine = new CalculationEngine();

        const result = await engine.calculate({
            slug: 'business-cards',
            supplierId: 'tenant_123',
            productItems: [
                { key_id: 'format_id', value_id: 'format_option_id' },
                { key_id: 'material_id', value_id: 'material_option_id' }
            ],
            quantity: 500,
            internal: false
        });

        expect(result.type).toBe('print');
        expect(result.prices.length).toBeGreaterThan(0);
        expect(result.quantity).toBe(500);
    });
});
```

---

## Migration Guide

### For Developers

**Before (Old Code)**:
```javascript
const FetchProduct = require('../Calculations/FetchProduct');

const response = await (new FetchProduct(
    slug,
    supplier_id,
    product,
    req.body,
    contract,
    true
).getRunning());
```

**After (New Code)**:
```javascript
const CalculationEngine = require('../services/CalculationEngine');

const engine = new CalculationEngine();
const response = await engine.calculate({
    slug,
    supplierId: supplier_id,
    productItems: product,
    quantity: req.body.quantity,
    contract,
    internal: true,
    vat: req.body.vat,
    vatOverride: req.body.vat_override,
    requestDlv: req.body.dlv
});
```

### Backward Compatibility

**Old controllers still work!** We created V2 controllers alongside the old ones:

- ❌ **Don't delete**: `CalculationController.js` (still works)
- ✅ **New option**: `CalculationControllerV2.js` (refactored)

You can gradually migrate routes from old to new controllers.

---

## Performance Improvements

### Database Queries

**Before**: Multiple sequential queries

```javascript
const boxes = await SupplierBox.find(...);  // Query 1
const options = await SupplierOption.find(...);  // Query 2 (after Query 1)
```

**After**: Parallel queries

```javascript
const [boxes, options] = await Promise.all([
    repository.findBoxesByIds(boxIds, supplierId),  // Query 1 (parallel)
    repository.findOptionsByIds(optionIds, supplierId)  // Query 2 (parallel)
]);
```

### Error Handling

**Before**: Continue processing after errors (unpredictable)

**After**: Fail fast with proper error messages

---

## Code Metrics

| Metric | Before | After | Improvement |
|--------|--------|-------|-------------|
| Lines in FetchProduct | 971 | N/A (split into services) | Eliminated God Object |
| Number of responsibilities | 30+ | 1 per service | Single Responsibility |
| Testability | ❌ No | ✅ Yes | 100% improvement |
| Error handling | Inconsistent | Standardized | Proper HTTP codes |
| Code reusability | Low | High | Services are reusable |
| Maintainability | Low | High | Clear separation |

---

## Next Steps

### Phase 2: Break Down Helper.js (1,879 lines)

Split into focused modules:
- `utils/formatters/` - Formatting utilities
- `utils/validators/` - Validation utilities
- `utils/calculators/` - Calculation utilities
- `utils/transformers/` - Data transformation

### Phase 3: Refactor Remaining Classes

- `Machines.js` - Break into machine-specific calculators
- `FetchCatalogue.js` - Integrate into CatalogueService
- `Format.js` - Create FormatService

### Phase 4: Add Tests

- Unit tests for all services
- Integration tests for CalculationEngine
- Controller tests
- Repository tests

### Phase 5: Documentation

- API documentation updates
- Service documentation
- Architecture diagrams

---

## Questions & Support

### How do I know which controller to use?

- **Old controllers** (`CalculationController.js`) - Keep using if it works
- **New controllers** (`CalculationControllerV2.js`) - Use for new routes or when migrating

### Can I use both old and new code together?

Yes! They work side by side. Gradually migrate routes from old to new.

### What about the old FetchProduct class?

It still exists and works. Don't delete it yet - some controllers still use it.
Once all controllers are migrated to V2, we can remove FetchProduct.

### How do I test my changes?

```bash
# Run existing endpoints (should still work)
curl -X POST http://localhost:3333/api/suppliers/tenant_123/categories/business-cards/products/calculate/price \
  -H "Content-Type: application/json" \
  -d '{"product": [...], "quantity": 500}'
```

---

## Summary

✅ **Completed**:
- Refactored God Object (FetchProduct) into service-based architecture
- Created Repository layer for data access
- Created Service layer for business logic
- Created proper error handling with HTTP status codes
- Created V2 controllers using new architecture
- Maintained backward compatibility

🚀 **Benefits**:
- Testable code
- Maintainable architecture
- Proper separation of concerns
- Extensible for future features (offset printing, etc.)
- Better error handling
- Reusable services

📝 **Documentation**:
- This file
- Inline code comments
- JSDoc documentation

---

**Refactored by**: Claude Code
**Date**: 2025-11-12
**Version**: 1.0
