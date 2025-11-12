# V2 Calculation Response Structure

## Current Problems

1. **Duration is null** - Timing calculations not working
2. **Lamination missing** - Not showing in final calculation
3. **Sides missing** - "4-sides" option not calculated
4. **Divided calculation** - Cover + Content not properly separated
5. **Response messy** - Hard to understand what happened

## New V2 Response Structure

### Top Level
```json
{
  "type": "print",
  "calculation_type": "divided_calculation", // or "full_calculation"
  "quantity": 100,
  "vat": 21,

  // Clear summary at top
  "summary": {
    "total_price_ex_vat": 4624.84,
    "total_price_inc_vat": 5595.65,
    "price_per_piece": 46.25,
    "production_time": {
      "setup_minutes": 10,
      "print_minutes": 120,
      "finishing_minutes": 30,
      "total_minutes": 160,
      "estimated_days": 1
    },
    "delivery_options": [
      { "days": 0, "title": "Same Day", "price_extra": 0 },
      { "days": 1, "title": "Next Day", "price_extra": 0 },
      { "days": 2, "title": "Standard", "price_extra": 0 }
    ]
  },

  // Breakdown for divided calculations
  "divisions": [
    {
      "name": "Cover",
      "divider": "cover",
      "items": [
        { "key": "format", "value": "a4", "display": "A4" },
        { "key": "printing-colors", "value": "44-full-color", "display": "4/4 Full Color" },
        { "key": "weight", "value": "135-grs", "display": "135 Grs" },
        { "key": "material", "value": "woodfree-coated-glossy-mc", "display": "Woodfree Coated Glossy" },
        { "key": "number-of-sides", "value": "4-sides", "display": "4 sides" },
        { "key": "lamination", "value": "double-sided-matt", "display": "Double Sided Matt Lamination" }
      ],
      "calculation": {
        "machine": {
          "id": "668e38b0d1911334d1635392",
          "name": "HP 7200",
          "type": "printing"
        },
        "sheets": {
          "needed": 50,
          "with_spoilage": 100,
          "products_per_sheet": 2
        },
        "costs": {
          "printing": 174.35,
          "lamination": 2500.00,
          "setup": 50.00,
          "subtotal": 2724.35
        },
        "timing": {
          "setup_minutes": 10,
          "print_minutes": 60,
          "lamination_minutes": 20,
          "total_minutes": 90
        }
      }
    },
    {
      "name": "Content",
      "divider": "content",
      "items": [
        { "key": "format", "value": "a4", "display": "A4" },
        { "key": "printing-colors", "value": "44-full-color", "display": "4/4 Full Color" },
        { "key": "weight", "value": "135-grs", "display": "135 Grs" },
        { "key": "material", "value": "woodfree-coated-glossy-mc", "display": "Woodfree Coated Glossy" },
        { "key": "number-of-pages", "value": "96-pages", "display": "96 Pages" }
      ],
      "calculation": {
        "machine": {
          "id": "668e38b0d1911334d1635392",
          "name": "HP 7200",
          "type": "printing"
        },
        "sheets": {
          "needed": 1200,
          "with_spoilage": 1300,
          "products_per_sheet": 2
        },
        "costs": {
          "printing": 1800.05,
          "setup": 50.00,
          "subtotal": 1850.05
        },
        "timing": {
          "setup_minutes": 10,
          "print_minutes": 100,
          "total_minutes": 110
        }
      }
    }
  ],

  // Price options with all delivery times
  "prices": [
    {
      "id": "...",
      "delivery_days": 0,
      "delivery_title": "Same Day",
      "quantity": 100,
      "gross_price": 4624.84,
      "selling_price_ex_vat": 4624.84,
      "selling_price_inc_vat": 5595.65,
      "price_per_piece": 46.25,
      "vat_amount": 970.82,
      "vat_percentage": 21,
      "margins": [], // Only if internal=true
      "breakdown": {
        "cover_cost": 2724.35,
        "content_cost": 1850.05,
        "options_cost": 50.44,
        "delivery_cost": 0
      }
    }
  ],

  // Full details for debugging (optional, can be hidden in production)
  "debug": {
    "pipeline_steps": [
      { "step": 1, "name": "Category Loading", "duration_ms": 45, "status": "success" },
      { "step": 2, "name": "Product Matching", "duration_ms": 12, "status": "success" },
      { "step": 3, "name": "Format Calculation", "duration_ms": 8, "status": "success" },
      { "step": 4, "name": "Machine Calculations", "duration_ms": 234, "status": "success" },
      { "step": 5, "name": "Price Formatting", "duration_ms": 5, "status": "success" }
    ],
    "total_pipeline_time_ms": 304,
    "machines_evaluated": 4,
    "combinations_generated": 8,
    "cheapest_combination": "HP 7200 + Lamination Machine"
  }
}
```

## Benefits of New Structure

### 1. Clear Summary at Top
User immediately sees:
- Total price
- Per-piece price
- Production time
- Delivery options

### 2. Division Breakdown
For divided calculations (cover + content):
- Each division clearly separated
- Items for each division listed
- Costs broken down per division
- Timing per division

### 3. Clean Price Options
Each price option shows:
- Delivery time
- Full breakdown
- Easy to compare

### 4. Debug Info (Optional)
Pipeline performance:
- Each step duration
- Total time
- What was evaluated

### 5. Better for Frontend
Frontend can easily:
- Show summary card
- Expand divisions
- Compare delivery options
- Debug issues

## Implementation Plan

1. Create `DividedCalculationHandler` service
2. Create `ResponseFormatter` service
3. Create `DurationCalculator` service
4. Update `CalculationPipeline` to use new structure
5. Add debug mode flag

Next: Implement these services?
