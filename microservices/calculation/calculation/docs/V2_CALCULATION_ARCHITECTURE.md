# V2 Calculation Architecture

## Overview

The V2 calculation system refactors the monolithic `FetchProduct` God Object into organized, testable services following Single Responsibility Principle.

## Architecture Comparison

### V1 (God Object Pattern)
```
FetchProduct.js (970 lines)
├─ getRunning()
├─ getCategory()
├─ getProduct()
├─ getMargin()
├─ matcher()
├─ prepareObject()
├─ prepareDivided()
├─ preCalculation()
├─ getLess()
├─ getLewPrice()
└─ groupBy()
```

**Problems:**
- Single file with too many responsibilities
- Hard to test individual components
- Difficult to extend for new calculation types
- Mixed business logic and data access
- Cannot parallelize calculations

### V2 (Service-Oriented Pattern)
```
CalculationPipeline (orchestrator)
├─ 1. CategoryService → Load category, machines, boops
├─ 2. ProductService → Match products by IDs
├─ 3. MarginService → Fetch margins
├─ 4. FormatService → Calculate format details
├─ 5. CatalogueService → Fetch paper specifications
├─ 6. MachineCalculationService → Run machine combinations
│   ├─ DigitalPrintingCalculator
│   ├─ LaminationCalculator
│   └─ FinishingCalculator
├─ 7. OptionsCalculationService → Calculate extra options
├─ 8. PriceCalculationService → Calculate final prices
└─ 9. PriceFormatterService → Format response
```

**Benefits:**
- Single responsibility per service
- Easy to test individually
- Can parallelize steps
- Clear data flow
- Extensible for new calculators

## Data Flow

### Input (V1 Payload)
```javascript
{
  "product": [
    { "key": "format", "value": "a4", "key_id": "...", "value_id": "..." },
    { "key": "weight", "value": "300gsm", "key_id": "...", "value_id": "..." },
    // ... more items
  ],
  "quantity": 100,
  "vat": 21,
  "internal": false
}
```

### Pipeline Steps

#### 1. Category Loading
**Service:** `CategoryService.getCategory()`
```javascript
{
  category: { name, slug, machines, boops, ... },
  machines: [{ _id, name, type, width, height, spm, ... }],
  boops: { boops: [{ id, slug, ops: [...] }] }
}
```

#### 2. Product Matching
**Service:** `ProductService.getMatchedProducts()`
```javascript
{
  boxes: [{ _id, name, calc_ref, ... }],
  options: [{ _id, name, rpm, runs, ... }],
  items: [{ box, option, key, value, ... }]
}
```

#### 3. Margin Fetching
**Service:** `MarginService.getMargins()`
```javascript
{
  margins: [{ type: 'percentage', value: 25, from: 1, to: 100 }]
}
```

#### 4. Format Calculation
**Service:** `FormatService.calculate()`
```javascript
{
  width: 210, height: 297,
  bleed: 3,
  quantity: 100,
  size: { m: 0.0624, lm: 0.297 },
  sheets: 1,
  pages: 4,
  status: 200
}
```

#### 5. Catalogue Lookup
**Service:** `CatalogueService.fetchMaterials()`
```javascript
{
  results: [{
    grs: 300,
    density: 0.65,
    thickness: 0.462,
    price: 5000,
    calc_type: 'kg'
  }]
}
```

#### 6. Machine Calculations
**Service:** `MachineCalculationService.runCombinations()`
```javascript
{
  combinations: [
    {
      printing: {
        machine: { ... },
        results: {
          calculation: {
            total_sheet_price: 25.50,
            amount_of_sheets_needed: 102,
            amount_of_sheets_printed: 102,
            price_list: { ... },
            color: { dlv: {...} }
          },
          duration: { setup_time: 5, print_time: 2, ... }
        }
      },
      lamination: { ... },
      finishing: { ... }
    }
  ]
}
```

#### 7. Options Calculation
**Service:** `OptionsCalculationService.calculateOptions()`
```javascript
{
  binding_cost: 2.50,
  folding_cost: 1.20,
  other_options_cost: 3.80,
  total_options_cost: 7.50
}
```

#### 8. Price Calculation
**Service:** `PriceCalculationService.calculateFinalPrices()`
```javascript
{
  cheapest_option: {
    machine: { ... },
    row_price: 45.30,
    price: 4530, // In cents
    dlv: { days: 3, title: "Standard" },
    duration: { total_time: 180 },
    calculation: { ... }
  }
}
```

#### 9. Price Formatting
**Service:** `PriceFormatterService.formatPriceObject()`
```javascript
{
  id: "abc123...",
  qty: 100,
  gross_price: 45.30,
  gross_ppp: 0.45,
  selling_price_ex: 56.63,
  selling_price_inc: 68.52,
  vat: 21,
  vat_p: 11.89,
  dlv: { days: 3, title: "Standard" },
  profit: null, // Only if internal=true
  margins: [] // Only if internal=true
}
```

### Output (V2 Response)
```javascript
{
  "type": "print",
  "calculation_type": "full_calculation",
  "items": [...],
  "product": [...],
  "category": {...},
  "quantity": 100,
  "divided": false,
  "calculation": [{
    "name": null,
    "machine": {...},
    "dlv": {...},
    "duration": {...},
    "row_price": 45.30,
    "price_list": {...},
    "details": {...},
    "price": {...}
  }],
  "prices": [{...}],
  "margins": [] // Only if internal=true
}
```

## Service Responsibilities

### 1. CategoryService (Existing ✓)
- Load category by slug and supplier ID
- Fetch boops from separate collection
- Extract machines from additional array
- Validate category has machines

### 2. ProductService (Existing ✓)
- Match boxes and options by IDs
- Build item objects with calc_refs
- Handle dynamic options
- Filter by category configuration

### 3. MarginService (Existing ✓)
- Fetch margins from margin microservice
- Apply margins to gross price
- Calculate profit
- Handle internal vs shop calculations

### 4. FormatService (NEW)
**Wraps:** `Calculations/Config/Format.js`
```javascript
class FormatService {
  async calculate(category, formatOption, quantity, bleed, pages, cover,
                  bindingMethod, bindingDirection, folding, endpapers, sides) {
    const format = new Format(...).calculate();
    return {
      width, height, bleed, quantity,
      size: { m, lm },
      sheets, pages,
      status, message
    };
  }
}
```

### 5. CatalogueService (NEW)
**Wraps:** `Calculations/Catalogues/FetchCatalogue.js`
```javascript
class CatalogueService {
  async fetchMaterials(material, weight, supplierId) {
    const catalogue = await new FetchCatalogue(material, weight, supplierId).get();
    return {
      results: [{ grs, density, thickness, price, calc_type }],
      error: { status, message }
    };
  }
}
```

### 6. MachineCalculationService (NEW)
**Orchestrates:** Machine combinations and calculator selection
```javascript
class MachineCalculationService {
  async runCombinations(machines, format, materials, items, category, content) {
    const results = [];

    for (const machine of machines) {
      // Select appropriate calculator
      const calculator = this._selectCalculator(machine.type);

      // Run calculation
      const result = await calculator.calculate({
        machine, format, materials, items, category, content
      });

      results.push(result);
    }

    // Create combinations (printing + lamination + finishing)
    return this._createCombinations(results);
  }

  _selectCalculator(machineType) {
    switch(machineType) {
      case 'digital':
      case 'printing':
        return new DigitalPrintingCalculator();
      case 'lamination':
        return new LaminationCalculator();
      case 'finishing':
        return new FinishingCalculator();
      default:
        throw new Error(`Unknown machine type: ${machineType}`);
    }
  }
}
```

### 7. OptionsCalculationService (NEW)
**Handles:** Extra options (binding, folding, etc.)
```javascript
class OptionsCalculationService {
  calculateOptions(items, format, quantity, category) {
    let totalCost = 0;

    // Calculate category start cost
    const startCost = category.start_cost ? parseFloat(category.start_cost) / 100000 : 0;
    totalCost += startCost;

    // Calculate extra options (binding, folding, etc.)
    for (const item of items) {
      if (item.option.runs) {
        const cost = this._calculateOptionCost(item, format, quantity, category);
        totalCost += cost;
      }
    }

    return { total: totalCost, breakdown: {...} };
  }
}
```

### 8. PriceCalculationService (NEW)
**Handles:** Final price calculation from all components
```javascript
class PriceCalculationService {
  calculateFinalPrice(combination, optionsCost, format) {
    let rowPrice = 0;

    // Add printing cost
    if (combination.printing) {
      rowPrice += parseFloat(combination.printing.results.calculation.total_sheet_price);
    }

    // Add lamination cost
    if (combination.lamination) {
      rowPrice += this._calculateLaminationCost(combination.lamination, format);
    }

    // Add finishing cost
    if (combination.finishing) {
      rowPrice += this._calculateFinishingCost(combination.finishing, format);
    }

    // Add options cost
    rowPrice += optionsCost.total;

    return {
      row_price: rowPrice,
      price: Math.round(rowPrice * 100), // Convert to cents
      breakdown: {...}
    };
  }
}
```

### 9. PriceFormatterService (Existing ✓)
- Format price objects for API response
- Apply VAT calculations
- Include margins if internal
- Generate unique price IDs

## CalculationPipeline Orchestrator

```javascript
class CalculationPipeline {
  constructor(context) {
    this.context = context; // Contains all input data

    // Initialize services
    this.categoryService = new CategoryService();
    this.productService = new ProductService();
    this.marginService = new MarginService();
    this.formatService = new FormatService();
    this.catalogueService = new CatalogueService();
    this.machineCalculationService = new MachineCalculationService();
    this.optionsCalculationService = new OptionsCalculationService();
    this.priceCalculationService = new PriceCalculationService();
    this.priceFormatterService = new PriceFormatterService();
  }

  async execute() {
    // Step 1: Load category and machines
    const { category, machines, boops } = await this.categoryService.getCategory(
      this.context.slug,
      this.context.supplierId
    );

    // Step 2: Match products
    const { items } = await this.productService.getMatchedProducts(
      this.context.items,
      this.context.supplierId,
      boops,
      category._id
    );

    // Step 3: Fetch margins
    const margins = await this.marginService.getMargins(
      this.context.supplierId,
      category.slug,
      this.context.quantity,
      this.context.internal
    );

    // Step 4: Calculate format
    const formatResult = await this.formatService.calculate(
      category,
      this._extractFormatOption(items),
      this.context.quantity,
      this.context.bleed || category.bleed
    );

    // Step 5: Fetch catalogue (materials)
    const materials = await this.catalogueService.fetchMaterials(
      this._extractMaterial(items),
      this._extractWeight(items),
      this.context.supplierId
    );

    // Step 6: Run machine calculations
    const combinations = await this.machineCalculationService.runCombinations(
      machines,
      formatResult,
      materials,
      items,
      category
    );

    // Step 7: Calculate options
    const optionsCost = await this.optionsCalculationService.calculateOptions(
      items,
      formatResult,
      this.context.quantity,
      category
    );

    // Step 8: Calculate final prices for each combination
    const pricesWithCombinations = combinations.map(combination => {
      return this.priceCalculationService.calculateFinalPrice(
        combination,
        optionsCost,
        formatResult
      );
    });

    // Find cheapest option
    const cheapestOption = this._findCheapest(pricesWithCombinations);

    // Step 9: Format response
    const formattedPrice = this.priceFormatterService.formatPriceObject({
      category,
      quantity: this.context.quantity,
      price: cheapestOption.row_price,
      dlv: cheapestOption.dlv,
      machine: cheapestOption.machine,
      margins: margins,
      discount: [],
      requestDlv: this.context.dlv,
      internal: this.context.internal,
      vat: this.context.vat,
      vatOverride: this.context.vatOverride
    });

    // Build final response
    return {
      type: 'print',
      calculation_type: 'full_calculation',
      items: items,
      product: this.context.items,
      category: category,
      quantity: this.context.quantity,
      divided: false,
      margins: this.context.internal && margins.length ? margins[0] : [],
      calculation: [{
        name: null,
        items: items,
        machine: cheapestOption.machine,
        dlv: cheapestOption.dlv,
        duration: cheapestOption.duration,
        row_price: cheapestOption.row_price,
        price_list: cheapestOption.calculation.price_list,
        details: cheapestOption.calculation,
        price: formattedPrice,
        error: { message: '', status: 200 }
      }],
      prices: [formattedPrice]
    };
  }
}
```

## Usage in HybridCalculationController

```javascript
static async calculate(req, res) {
  const { supplier_id, slug } = req.params;
  const v1Payload = req.body;

  // Enrich V1 payload with IDs (existing logic)
  const categoryService = new CategoryService();
  const { boops } = await categoryService.getCategory(slug, supplier_id);
  const enrichedItems = HybridCalculationController._enrichItemsWithIds(
    v1Payload.product,
    boops
  );

  // Create V2 calculation context
  const context = {
    slug,
    supplierId: supplier_id,
    items: enrichedItems,
    quantity: v1Payload.quantity || 100,
    vat: v1Payload.vat || 21,
    vatOverride: v1Payload.vat_override || false,
    internal: v1Payload.internal || false,
    dlv: v1Payload.dlv || null,
    bleed: v1Payload.bleed || null
  };

  // Execute V2 pipeline
  const pipeline = new CalculationPipeline(context);
  const result = await pipeline.execute();

  return res.status(200).json(result);
}
```

## Migration Strategy

### Phase 1: Build V2 Services (Current)
- Create all new services
- Keep V1 FetchProduct working
- Test V2 in parallel

### Phase 2: A/B Testing
- Add feature flag to switch between V1 and V2
- Test V2 with real data
- Compare results

### Phase 3: Full Migration
- Switch default to V2
- Deprecate V1
- Remove FetchProduct.js

## Testing Strategy

### Unit Tests
Each service can be tested individually:
```javascript
describe('FormatService', () => {
  it('should calculate format for A4', () => {
    const result = formatService.calculate(...);
    expect(result.width).toBe(210);
    expect(result.height).toBe(297);
  });
});
```

### Integration Tests
Test pipeline end-to-end:
```javascript
describe('CalculationPipeline', () => {
  it('should calculate price for brochure', async () => {
    const result = await pipeline.execute();
    expect(result.prices).toHaveLength(1);
    expect(result.prices[0].qty).toBe(100);
  });
});
```

## Performance Improvements

### Parallelization
Services can run in parallel where possible:
```javascript
// Run independent operations in parallel
const [margins, formatResult, materials] = await Promise.all([
  marginService.getMargins(...),
  formatService.calculate(...),
  catalogueService.fetchMaterials(...)
]);
```

### Caching
Cache expensive operations:
```javascript
// Cache category lookups
const cachedCategory = await cache.getOrSet(
  `category:${slug}:${supplierId}`,
  () => categoryService.getCategory(slug, supplierId),
  { ttl: 300 } // 5 minutes
);
```

## Next Steps

1. ✓ Create architecture document
2. Create FormatService
3. Create CatalogueService
4. Create MachineCalculationService
5. Create OptionsCalculationService
6. Create PriceCalculationService
7. Create CalculationPipeline orchestrator
8. Integrate into HybridCalculationController
9. Add comprehensive tests
10. Performance benchmarking
