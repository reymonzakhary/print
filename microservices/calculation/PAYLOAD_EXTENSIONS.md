# API Payload Extensions

## Overview

This document describes optional fields that can be ADDED to calculation API request and response payloads to support enhanced features.

**IMPORTANT**: All existing fields in the payloads are maintained and MUST NOT be removed. This document only covers additions.

---

## Current Request Payload Structure

### Full Calculation Endpoints

**Existing Request Body (DO NOT REMOVE)**:

```javascript
{
    product: Array,      // Product items with options, materials, formats
    quantity: Number,    // Number of units to produce
    contract: String     // Optional contract pricing reference (optional)
}
```

### Semi-Calculation Endpoints

**Existing Request Body (DO NOT REMOVE)**:

```javascript
{
    product: Object,     // Product configuration (may be simplified)
    quantity: Number,    // Number of units
    vat: Number,         // VAT percentage
    dlv: Number,         // Delivery time filter (optional)
    vat_override: Boolean,  // Override category VAT (optional)
    contract: String     // Contract pricing reference (optional)
}
```

---

## Current Response Payload Structure

### Existing Response Format (DO NOT REMOVE)

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
    margins: Array,               // Margin calculations
    divided: Boolean,             // If calculation was split
    quantity: Number,             // Requested quantity
    calculation: Array,           // Detailed calculation breakdown
    prices: [{                    // Price variations
        id: String,               // Unique price identifier
        pm: String,               // Price model
        qty: Number,              // Quantity for this price
        dlv: Object,              // Delivery info {days, title}
        gross_price: Number,      // Price before margin
        gross_ppp: Number,        // Gross price per piece
        p: Number,                // Final price
        ppp: Number,              // Price per piece
        selling_price_ex: Number, // Selling price excluding VAT
        selling_price_inc: Number,// Selling price including VAT
        profit: Number,           // Profit amount (internal only)
        discount: Array,          // Applied discounts
        margins: Object,          // Margin details (internal only)
        vat: Number,              // VAT percentage
        vat_p: Number,            // VAT amount
        vat_ppp: Number           // VAT amount per piece
    }]
}
```

---

## Request Payload Extensions (CAN BE ADDED)

### 1. Printing Method Selection

Add to explicitly request specific printing method:

```javascript
{
    // NEW FIELDS
    printing_method: {
        type: String,
        enum: ['digital', 'offset', 'large_format', 'auto'],
        default: 'auto'  // Let system choose optimal method
    },

    printing_method_preferences: [{
        method: String,        // 'digital', 'offset', etc.
        priority: Number       // 1 = highest priority
    }]
}
```

**Usage**: Allows customer to request specific printing method or let system optimize.

---

### 2. Color Configuration

Add for explicit color specification (offset printing):

```javascript
{
    // NEW FIELDS
    color_config: {
        notation: String,      // "4/4", "4/0", "1/1", "4/1+1", etc.
        front_colors: Number,  // Number of colors on front
        back_colors: Number,   // Number of colors on back
        spot_colors: [{
            name: String,      // "Pantone 185 C"
            side: String,      // "front" or "back"
            coverage: Number   // % coverage (0-100)
        }],
        color_mode: {
            type: String,
            enum: ['CMYK', 'RGB', 'Pantone', 'mixed'],
            default: 'CMYK'
        }
    }
}
```

**Usage**: Enables accurate offset printing calculations based on exact color requirements.

---

### 3. Delivery & Urgency

Add for enhanced delivery calculation:

```javascript
{
    // NEW FIELDS
    delivery_requirements: {
        required_date: String,      // ISO date "2025-11-20"
        latest_date: String,        // Latest acceptable delivery
        rush_acceptable: Boolean,   // Can pay rush fees
        split_delivery: Boolean,    // Can split order
        preferred_days: [Number]    // Day of week (0-6) for delivery
    },

    urgency: {
        type: String,
        enum: ['standard', 'express', 'rush', 'same_day'],
        default: 'standard'
    }
}
```

**Usage**: Calculates realistic delivery dates and applies rush fees when needed.

---

### 4. Finishing & Post-Processing

Add for finishing requirements:

```javascript
{
    // NEW FIELDS
    finishing: {
        coating: {
            type: String,
            enum: ['none', 'uv', 'aqueous', 'varnish', 'lamination'],
            default: 'none'
        },
        coating_sides: {
            type: String,
            enum: ['none', 'front', 'back', 'both'],
            default: 'none'
        },
        cutting: {
            required: Boolean,
            custom_shape: Boolean,
            die_cutting: Boolean
        },
        folding: {
            required: Boolean,
            fold_type: String,  // "half_fold", "tri_fold", "z_fold", etc.
            fold_count: Number
        },
        binding: {
            type: String,
            enum: ['none', 'saddle_stitch', 'perfect_bound', 'spiral', 'wire_o'],
            default: 'none'
        },
        packaging: {
            type: String,
            enum: ['bulk', 'bundled', 'boxed', 'shrink_wrapped'],
            default: 'bulk'
        }
    }
}
```

**Usage**: Includes finishing costs and time in calculations.

---

### 5. Quality & Specifications

Add for quality requirements:

```javascript
{
    // NEW FIELDS
    quality_requirements: {
        quality_level: {
            type: String,
            enum: ['draft', 'standard', 'premium', 'showcase'],
            default: 'standard'
        },
        color_accuracy: {
            type: String,
            enum: ['standard', 'high', 'color_managed', 'proof_required'],
            default: 'standard'
        },
        proof_required: Boolean,
        hard_proof: Boolean,     // Physical proof
        pdf_proof: Boolean,       // Digital proof
        press_check: Boolean     // Customer present during print run
    }
}
```

**Usage**: Adjusts costs and timelines based on quality requirements.

---

### 6. Position/Imposition Preferences

Add for sheet layout preferences:

```javascript
{
    // NEW FIELDS
    imposition_preferences: {
        optimize_for: {
            type: String,
            enum: ['cost', 'quality', 'speed', 'material_savings'],
            default: 'cost'
        },
        allow_rotation: Boolean,     // Can rotate product on sheet
        allow_mixed_orientation: Boolean,  // Mix portrait/landscape
        min_bleed: Number,           // mm
        requested_bleed: Number,     // mm
        color_bar: Boolean,          // Include color bar
        registration_marks: Boolean  // Include registration marks
    }
}
```

**Usage**: Optimizes sheet layout according to customer preferences.

---

### 7. Material Preferences

Add for material selection criteria:

```javascript
{
    // NEW FIELDS
    material_preferences: {
        eco_friendly: Boolean,       // Prefer sustainable materials
        certifications_required: [String],  // ['FSC', 'PEFC']
        recycled_content_min: Number,  // Minimum % recycled content
        price_priority: {
            type: String,
            enum: ['lowest', 'best_value', 'premium'],
            default: 'best_value'
        },
        brand_preference: String     // Preferred paper brand
    }
}
```

**Usage**: Filters material selection and may adjust pricing.

---

### 8. Calculation Options

Add for calculation behavior control:

```javascript
{
    // NEW FIELDS
    calculation_options: {
        include_alternatives: Boolean,   // Include alternative materials/methods
        show_breakdown: Boolean,         // Detailed cost breakdown
        compare_methods: Boolean,        // Compare digital vs offset
        optimize_quantity: Boolean,      // Suggest optimal quantity
        show_positioning: Boolean,       // Include sheet layout info
        show_duration: Boolean,          // Include production timeline
        include_waste_calculation: Boolean,  // Show material waste
        currency: {
            type: String,
            default: 'EUR'
        },
        locale: {
            type: String,
            default: 'en-GB'
        }
    }
}
```

**Usage**: Controls what information is returned in response.

---

### 9. Customer & Order Context

Add for context-aware pricing:

```javascript
{
    // NEW FIELDS
    customer_context: {
        customer_id: String,
        customer_type: {
            type: String,
            enum: ['new', 'regular', 'premium', 'trade'],
            default: 'new'
        },
        order_reference: String,
        is_repeat_order: Boolean,
        original_order_id: String,   // If repeat
        volume_commitment: {         // Annual volume commitment
            quantity: Number,
            period: String           // 'annual', 'quarterly'
        }
    }
}
```

**Usage**: Applies customer-specific pricing and discounts.

---

## Response Payload Extensions (CAN BE ADDED)

### 1. Enhanced Price Breakdown

Add to price objects for detailed cost transparency:

```javascript
{
    prices: [{
        // ... existing fields ...

        // NEW FIELDS
        breakdown: {
            material_cost: Number,
            plate_cost: Number,           // Offset plates
            ink_cost: Number,
            machine_setup_cost: Number,
            machine_runtime_cost: Number,
            labor_cost: Number,
            finishing_cost: Number,
            packaging_cost: Number,
            overhead_cost: Number,
            delivery_cost: Number
        },

        cost_per_unit_breakdown: {
            material_cost_ppu: Number,
            production_cost_ppu: Number,
            overhead_ppu: Number,
            profit_ppu: Number
        }
    }]
}
```

**Usage**: Provides transparency into pricing components (especially useful for internal calculations).

---

### 2. Production Timeline

Add for delivery planning:

```javascript
{
    // NEW FIELD
    production_timeline: {
        order_date: String,          // ISO date
        production_start: String,    // When production can start
        production_duration: {
            setup_time: Number,      // Minutes
            print_time: Number,      // Minutes
            drying_time: Number,     // Minutes
            finishing_time: Number,  // Minutes
            quality_check_time: Number,  // Minutes
            total_time: Number       // Total minutes
        },
        production_complete: String, // When production finishes
        packaging_time: Number,      // Minutes
        ready_for_delivery: String,  // ISO date/time
        estimated_delivery: String,  // ISO date
        latest_delivery: String,     // Latest possible delivery
        working_days: Number,        // Business days
        calendar_days: Number        // Calendar days
    }
}
```

**Usage**: Helps customer understand when they'll receive order.

---

### 3. Sheet Positioning/Imposition Data

Add for visualization and production planning:

```javascript
{
    // NEW FIELD
    positioning: {
        sheet_size: {
            width: Number,   // mm
            height: Number   // mm
        },
        product_size: {
            width: Number,
            height: Number
        },
        products_per_sheet: Number,
        layout: [{
            position: Number,    // 1, 2, 3, ...
            x: Number,          // Position on sheet (mm)
            y: Number,
            width: Number,
            height: Number,
            rotation: Number,   // Degrees (0, 90, 180, 270)
            bleed: {
                top: Number,
                right: Number,
                bottom: Number,
                left: Number
            }
        }],
        sheets_required: Number,
        sheet_utilization: Number,   // % of sheet used
        waste_area: Number,          // Unused area (sqm)
        gripper_edge: {
            side: String,            // "top"
            size: Number             // mm
        },
        trim_marks: Boolean,
        color_bar: Boolean
    }
}
```

**Usage**:
- Visualizes product layout on sheet
- Shows production efficiency
- Useful for customer approval

---

### 4. Material Usage

Add for material consumption reporting:

```javascript
{
    // NEW FIELD
    material_usage: {
        material_name: String,
        material_sku: String,
        gsm: Number,
        sheet_size: {
            width: Number,
            height: Number
        },
        sheets_required: Number,
        sheets_with_waste: Number,
        setup_waste_sheets: Number,
        spoilage_sheets: Number,
        total_sheets: Number,
        total_weight_kg: Number,
        total_area_sqm: Number,
        material_cost_total: Number,
        material_cost_per_unit: Number,
        waste_percentage: Number,
        sustainability: {
            carbon_footprint_kg: Number,
            recyclable: Boolean,
            certifications: [String]
        }
    }
}
```

**Usage**:
- Transparency on material consumption
- Environmental reporting
- Helps justify pricing

---

### 5. Alternative Options

Add to suggest alternatives:

```javascript
{
    // NEW FIELD
    alternatives: [{
        type: String,  // "material", "method", "quantity", "finish"
        description: String,
        current_value: String,
        alternative_value: String,
        price_difference: Number,    // Amount saved/added
        price_difference_pct: Number,  // % difference
        delivery_difference_days: Number,
        quality_impact: String,      // "same", "better", "lower"
        recommendation_reason: String,
        recommended: Boolean
    }]
}
```

**Usage**: Helps customer make informed decisions and optimize costs.

---

### 6. Warnings & Recommendations

Add for quality and production advice:

```javascript
{
    // NEW FIELD
    warnings: [{
        severity: String,  // "info", "warning", "error"
        code: String,      // "QUANTITY_BELOW_OPTIMAL"
        message: String,
        recommendation: String,
        impact: String     // How this affects order
    }],

    recommendations: [{
        type: String,      // "cost_saving", "quality_improvement", "time_saving"
        message: String,
        potential_savings: Number,
        action: String
    }]
}
```

**Usage**: Guides customer toward better decisions.

**Example warnings**:
- "Quantity below economic run length for offset (min: 500)"
- "Material weight may cause folding issues"
- "Rush delivery requires 30% surcharge"

**Example recommendations**:
- "Increase quantity to 1000 for 15% per-unit savings"
- "Switch to offset printing at this quantity for 25% savings"
- "Use standard delivery (2 days later) to save €45"

---

### 7. Comparison Data

Add when comparing multiple methods:

```javascript
{
    // NEW FIELD
    method_comparison: [{
        method: String,        // "digital", "offset"
        available: Boolean,
        price_total: Number,
        price_per_unit: Number,
        delivery_days: Number,
        quality_rating: String,  // "good", "excellent"
        suitable_for_quantity: Boolean,
        pros: [String],
        cons: [String],
        recommended: Boolean,
        recommendation_reason: String
    }]
}
```

**Usage**: Helps customer choose between digital and offset printing.

---

### 8. Validation & Status

Add for request validation feedback:

```javascript
{
    // NEW FIELD
    validation: {
        valid: Boolean,
        errors: [{
            field: String,
            message: String,
            code: String
        }],
        warnings: [{
            field: String,
            message: String,
            code: String
        }]
    },

    calculation_meta: {
        calculation_time_ms: Number,
        api_version: String,
        calculation_engine_version: String,
        cached: Boolean,
        cache_key: String,
        calculated_at: String  // ISO timestamp
    }
}
```

**Usage**:
- Helps debug API issues
- Provides validation feedback
- Performance monitoring

---

### 9. Visual Assets

Add for customer preview:

```javascript
{
    // NEW FIELD
    visual_assets: {
        sheet_layout_svg: String,    // SVG of sheet layout
        sheet_layout_png: String,    // PNG URL
        product_preview: String,     // Preview of finished product
        thumbnail: String            // Small preview image
    }
}
```

**Usage**: Visual representation of production layout (future feature).

---

### 10. Pricing Summary

Add for easy price understanding:

```javascript
{
    // NEW FIELD
    pricing_summary: {
        lowest_price: Number,
        highest_price: Number,
        recommended_price: Number,
        recommended_quantity: Number,
        price_range: {
            min_qty: Number,
            max_qty: Number,
            variations: Number  // Number of price points
        },
        optimal_quantity: {
            qty: Number,
            price_per_unit: Number,
            total_price: Number,
            reason: String     // Why this quantity is optimal
        }
    }
}
```

**Usage**: Quick overview of pricing without examining all variations.

---

## Complete Example: Enhanced Offset Calculation Request

```javascript
{
    // EXISTING FIELDS
    product: [...],
    quantity: 2000,
    contract: "annual_2024",

    // NEW FIELDS
    printing_method: "offset",

    color_config: {
        notation: "4/4",
        front_colors: 4,
        back_colors: 4,
        color_mode: "CMYK"
    },

    delivery_requirements: {
        required_date: "2025-11-25",
        rush_acceptable: false
    },

    finishing: {
        coating: "uv",
        coating_sides: "both",
        cutting: {
            required: true,
            custom_shape: false
        }
    },

    quality_requirements: {
        quality_level: "premium",
        color_accuracy: "color_managed",
        pdf_proof: true
    },

    imposition_preferences: {
        optimize_for: "cost",
        allow_rotation: true
    },

    calculation_options: {
        show_breakdown: true,
        show_positioning: true,
        show_duration: true,
        include_alternatives: true
    }
}
```

---

## Complete Example: Enhanced Calculation Response

```javascript
{
    // EXISTING FIELDS
    type: "print",
    connection: "supplier_123",
    external: "",
    external_id: "supplier_123",
    external_name: "Acme Printing",
    calculation_type: "full_calculation",
    items: [...],
    product: {...},
    category: {...},
    margins: [...],
    divided: false,
    quantity: 2000,
    calculation: [...],

    prices: [{
        // Existing price fields
        id: "abc123",
        pm: "per_sheet",
        qty: 2000,
        dlv: {days: 5, title: "Standard"},
        gross_price: 850.00,
        gross_ppp: 0.425,
        p: 1020.00,
        ppp: 0.51,
        selling_price_ex: 1020.00,
        selling_price_inc: 1234.20,
        profit: 170.00,
        discount: [],
        margins: {value: 20, type: "percentage"},
        vat: 21,
        vat_p: 214.20,
        vat_ppp: 0.1071,

        // NEW: Detailed breakdown
        breakdown: {
            material_cost: 320.00,
            plate_cost: 120.00,     // 8 plates × €15
            ink_cost: 80.00,
            machine_setup_cost: 150.00,
            machine_runtime_cost: 120.00,
            labor_cost: 40.00,
            finishing_cost: 120.00,  // UV coating
            overhead_cost: 70.00
        }
    }],

    // NEW: Production timeline
    production_timeline: {
        order_date: "2025-11-12T10:00:00Z",
        production_start: "2025-11-13T08:00:00Z",
        production_duration: {
            setup_time: 80,       // Plate making + press setup
            print_time: 120,      // Actual printing
            drying_time: 0,       // UV is instant
            finishing_time: 60,   // Cutting
            quality_check_time: 15,
            total_time: 275       // ~4.6 hours
        },
        production_complete: "2025-11-13T13:00:00Z",
        ready_for_delivery: "2025-11-13T14:00:00Z",
        estimated_delivery: "2025-11-14T10:00:00Z",
        working_days: 2,
        calendar_days: 2
    },

    // NEW: Sheet positioning
    positioning: {
        sheet_size: {width: 520, height: 370},
        product_size: {width: 210, height: 297},
        products_per_sheet: 2,
        layout: [
            {position: 1, x: 5, y: 5, width: 210, height: 297, rotation: 0, bleed: {...}},
            {position: 2, x: 220, y: 5, width: 210, height: 297, rotation: 0, bleed: {...}}
        ],
        sheets_required: 1000,
        sheet_utilization: 78.5,
        waste_area: 0.041
    },

    // NEW: Material usage
    material_usage: {
        material_name: "Silk Coated 300gsm SRA3",
        gsm: 300,
        sheet_size: {width: 320, height: 450},
        sheets_required: 1000,
        setup_waste_sheets: 50,
        spoilage_sheets: 20,
        total_sheets: 1070,
        total_weight_kg: 154.5,
        total_area_sqm: 153.7,
        material_cost_total: 320.00,
        waste_percentage: 7.0,
        sustainability: {
            carbon_footprint_kg: 123.6,
            recyclable: true,
            certifications: ["FSC", "EU_Ecolabel"]
        }
    },

    // NEW: Warnings
    warnings: [
        {
            severity: "info",
            code: "OFFSET_OPTIMAL",
            message: "Offset printing is optimal for this quantity",
            impact: "Cost-effective choice"
        }
    ],

    // NEW: Recommendations
    recommendations: [
        {
            type: "cost_saving",
            message: "Increase quantity to 2500 for 12% per-unit savings",
            potential_savings: 127.50,
            action: "adjust_quantity"
        }
    ],

    // NEW: Method comparison
    method_comparison: [
        {
            method: "offset",
            available: true,
            price_total: 1020.00,
            price_per_unit: 0.51,
            delivery_days: 2,
            quality_rating: "excellent",
            recommended: true,
            pros: ["Lower cost at this quantity", "Excellent color accuracy"],
            cons: ["Longer setup time"]
        },
        {
            method: "digital",
            available: true,
            price_total: 1380.00,
            price_per_unit: 0.69,
            delivery_days: 1,
            quality_rating: "good",
            recommended: false,
            pros: ["Faster turnaround"],
            cons: ["35% more expensive at this quantity"]
        }
    ],

    // NEW: Pricing summary
    pricing_summary: {
        lowest_price: 0.48,
        highest_price: 0.53,
        recommended_price: 0.51,
        recommended_quantity: 2000,
        optimal_quantity: {
            qty: 2500,
            price_per_unit: 0.45,
            total_price: 1125.00,
            reason: "Best cost per unit with minimal additional investment"
        }
    }
}
```

---

## Implementation Guidelines

### Adding New Request Fields:

1. **Always Optional**: New request fields must be optional
2. **Provide Defaults**: System chooses sensible defaults when fields absent
3. **Validate Input**: Validate new fields properly
4. **Document**: Update API documentation
5. **Version Control**: Consider V2 endpoint if changes are significant

### Adding New Response Fields:

1. **Always Optional**: Old clients may not expect new fields
2. **Conditional**: Only include when relevant (e.g., don't include positioning if not requested)
3. **Consistent Structure**: Follow established patterns
4. **Test Backward Compatibility**: Ensure old clients aren't broken
5. **Document**: Clearly document new fields

### Backward Compatibility Checklist:

- ✅ Existing request fields work as before
- ✅ Missing new request fields use sensible defaults
- ✅ Existing response fields always present
- ✅ New response fields don't break JSON parsing
- ✅ Old API clients continue to work
- ✅ Response field order doesn't matter (use object keys)

---

## Versioning Strategy

### V1 API (Current):
- Maintains all existing fields
- Can add **optional** request fields
- Can add **optional** response fields at root or within objects
- MUST NOT remove or rename existing fields

### V2 API (Future):
- Can restructure payloads more significantly
- Can deprecate old fields (but keep for compatibility)
- Can change default behaviors
- Can introduce breaking changes with proper migration path

### Example V2 Endpoint:
```
POST /v2/suppliers/:supplier_id/categories/:slug/products/calculate/price
```

---

## Testing New Payload Fields

### Request Testing:

1. **Test with field present**: Verify new functionality works
2. **Test with field absent**: Ensure defaults work
3. **Test with invalid values**: Verify validation
4. **Test with null/undefined**: Handle gracefully
5. **Test combinations**: Multiple new fields together

### Response Testing:

1. **Old client test**: Parse response without expecting new fields
2. **New client test**: Parse and use new fields
3. **Schema validation**: Ensure response matches schema
4. **Size test**: Ensure response isn't too large
5. **Performance test**: New fields don't slow down response

---

## Related Documentation

- See `MACHINE_MODEL_EXTENSIONS.md` for machine/press enhancements
- See `CATALOGUE_MODEL_EXTENSIONS.md` for material/catalogue enhancements
- See `OFFSET_PRINTING_IMPLEMENTATION.md` for complete implementation guide
- See `REFACTORING_PLAN.md` for overall refactoring strategy
- See `routes/v1/calculations.js` for current API routes
