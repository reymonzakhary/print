# Catalogue Model Extensions

## Overview

This document describes optional fields that can be ADDED to the `SupplierCatalogue` model to support enhanced calculation features for various materials and printing methods.

**IMPORTANT**: All existing fields in the model are maintained and MUST NOT be removed. This document only covers additions.

---

## Current Catalogue Model Structure

### Existing Fields (DO NOT REMOVE)

```javascript
{
    // Identity & Tenant
    tenant_id: String (required),
    tenant_name: String (required),
    supplier: String (required),

    // Material Identification
    art_nr: String (required),  // Article number
    material: String (required),  // Material name
    material_link: ObjectId (optional),  // Link to material master data
    material_id: ObjectId (required),

    // Weight/GSM
    grs: Number (required),  // Grams per square meter (GSM)
    grs_link: ObjectId (optional),
    grs_id: ObjectId (required),

    // Pricing
    price: Number (default: 0),

    // Physical Properties
    ean: String (default: ''),
    density: Number (default: 0),
    height: Number (default: 0),
    length: Number (default: 0),
    width: Number (default: 0),
    sheet: Boolean (default: true),

    // Calculation
    calc_type: String (default: 'kg'),  // 'kg', 'sheet', 'sqm', etc.

    // Timestamps
    created_at: Date (default: Date.now),
    updated_at: Date (default: Date.now)
}
```

---

## Extensible Fields (CAN BE ADDED)

### 1. Material Type & Category

Add for better material classification and selection:

```javascript
{
    // NEW FIELDS - Material Classification
    material_type: {
        type: String,
        enum: [
            'paper_uncoated',
            'paper_coated_gloss',
            'paper_coated_matte',
            'paper_coated_silk',
            'cardboard',
            'cardboard_coated',
            'self_adhesive',
            'synthetic',
            'fabric',
            'specialty'
        ],
        required: false,
        default: 'paper_uncoated'
    },

    material_category: {
        type: String,
        enum: ['sheet', 'roll', 'board', 'film', 'fabric', 'other'],
        required: false,
        default: 'sheet'
    },

    material_grade: {
        type: String,
        enum: ['premium', 'standard', 'economy'],
        required: false,
        default: 'standard'
    }
}
```

**Usage**:
- Enables calculation engine to apply material-specific pricing
- Allows filtering materials by type/category
- Supports quality-based pricing tiers

---

### 2. Printing Method Compatibility

Add to specify which printing methods work with this material:

```javascript
{
    // NEW FIELDS - Printing Method Compatibility
    printing_methods: {
        type: Array,
        required: false,
        default: [],

        items: [{
            method: {
                type: String,
                enum: ['digital', 'offset', 'large_format', 'screen', 'flexo'],
                required: true
            },
            compatible: {
                type: Boolean,
                default: true
            },
            price_adjustment: {
                type: Number,
                default: 0  // % adjustment (e.g., +10 for 10% more expensive)
            },
            speed_adjustment: {
                type: Number,
                default: 100  // % of normal speed
            },
            quality_notes: {
                type: String,
                default: ""
            }
        }]
    }
}
```

**Usage**: Prevents incompatible material/machine combinations and adjusts costs accordingly.

---

### 3. Offset Printing Specific Properties

Add for offset printing materials:

```javascript
{
    // NEW FIELDS - Offset Printing Properties
    offset_properties: {
        type: Object,
        required: false,
        default: {},

        // Ink absorption
        ink_absorption: {
            type: String,
            enum: ['low', 'medium', 'high'],
            default: 'medium'
        },

        ink_absorption_rate: {
            type: Number,
            default: 0  // Percentage of ink absorbed
        },

        // Drying time
        drying_time: {
            type: Number,
            default: 0  // Minutes required for drying
        },

        // Coating/Finishing compatibility
        coating_compatible: {
            type: Boolean,
            default: true
        },

        coating_types: [{
            type: String,
            enum: ['uv', 'aqueous', 'varnish', 'lamination'],
            drying_time: { type: Number },  // Additional drying time
            cost_per_sqm: { type: Number }  // Cost in cents
        }],

        // Anti-offset spray required
        anti_offset_powder: {
            required: { type: Boolean, default: false },
            cost_per_sheet: { type: Number, default: 0 }
        },

        // Grain direction (important for folding)
        grain_direction: {
            type: String,
            enum: ['long_grain', 'short_grain', 'none'],
            default: 'none'
        },

        // Optimal printing conditions
        optimal_humidity: {
            min: { type: Number, default: 40 },  // %
            max: { type: Number, default: 60 }
        },

        optimal_temperature: {
            min: { type: Number, default: 20 },  // °C
            max: { type: Number, default: 24 }
        }
    }
}
```

**Usage**:
- Adjusts ink costs based on absorption
- Adds drying time to production schedule
- Includes anti-offset powder costs when needed
- Ensures quality by checking environmental conditions

---

### 4. Large Format Printing Properties

Add for wide format/banner materials:

```javascript
{
    // NEW FIELDS - Large Format Properties
    large_format_properties: {
        type: Object,
        required: false,
        default: {},

        media_category: {
            type: String,
            enum: ['banner', 'vinyl', 'canvas', 'paper', 'fabric', 'film', 'backlit'],
            default: 'paper'
        },

        roll_width: {
            type: Number,
            default: 0  // mm - actual roll width
        },

        roll_length: {
            type: Number,
            default: 0  // meters per roll
        },

        // Finishing options for this material
        finishing_compatible: [{
            type: String,
            enum: ['hemming', 'eyelets', 'pole_pocket', 'welding', 'lamination']
        }],

        // Ink compatibility
        ink_types_compatible: [{
            type: String,
            enum: ['solvent', 'eco_solvent', 'latex', 'UV', 'water_based']
        }],

        // Outdoor durability
        outdoor_durability: {
            type: String,
            enum: ['indoor_only', 'short_term', 'medium_term', 'long_term'],
            default: 'indoor_only'
        },

        outdoor_lifespan_months: {
            type: Number,
            default: 0
        },

        // Flexibility
        flexibility: {
            type: String,
            enum: ['rigid', 'semi_rigid', 'flexible'],
            default: 'flexible'
        }
    }
}
```

**Usage**: Supports wide format calculations with appropriate material selections and finishing options.

---

### 5. Stock & Availability

Add for inventory management and lead time calculations:

```javascript
{
    // NEW FIELDS - Stock Management
    stock_management: {
        type: Object,
        required: false,
        default: {},

        in_stock: {
            type: Boolean,
            default: true
        },

        stock_quantity: {
            type: Number,
            default: 0  // Number of sheets or kg available
        },

        stock_unit: {
            type: String,
            enum: ['sheets', 'kg', 'sqm', 'rolls'],
            default: 'sheets'
        },

        reorder_point: {
            type: Number,
            default: 0
        },

        // Lead times
        lead_time_days: {
            type: Number,
            default: 0  // Days to delivery if out of stock
        },

        supplier_lead_time: {
            type: Number,
            default: 0  // Days from supplier
        },

        // Minimum order quantity from supplier
        moq: {
            type: Number,
            default: 0
        },

        moq_unit: {
            type: String,
            enum: ['sheets', 'kg', 'sqm', 'rolls'],
            default: 'sheets'
        }
    }
}
```

**Usage**:
- Enables realistic delivery date calculation
- Supports inventory-based pricing
- Alerts when stock is insufficient

---

### 6. Sustainability & Certifications

Add for environmental reporting and customer requirements:

```javascript
{
    // NEW FIELDS - Sustainability
    sustainability: {
        type: Object,
        required: false,
        default: {},

        certifications: [{
            type: String,
            enum: ['FSC', 'PEFC', 'EU_Ecolabel', 'Blue_Angel', 'Nordic_Swan', 'Cradle_to_Cradle']
        }],

        recycled_content: {
            type: Number,
            default: 0  // % of recycled content
        },

        recyclable: {
            type: Boolean,
            default: true
        },

        biodegradable: {
            type: Boolean,
            default: false
        },

        carbon_footprint: {
            type: Number,
            default: 0  // kg CO2 per kg of material
        },

        sustainable_source: {
            type: Boolean,
            default: false
        },

        eco_premium: {
            type: Number,
            default: 0  // % price premium for eco certification
        }
    }
}
```

**Usage**:
- Supports filtering materials by certification
- Enables carbon footprint reporting
- Applies eco-premium pricing

---

### 7. Pricing Tiers & Volume Discounts

Add for quantity-based pricing:

```javascript
{
    // NEW FIELDS - Advanced Pricing
    pricing_tiers: {
        type: Array,
        required: false,
        default: [],

        items: [{
            min_quantity: { type: Number, required: true },  // Minimum qty for this tier
            max_quantity: { type: Number, required: false }, // Optional maximum
            unit: {
                type: String,
                enum: ['sheets', 'kg', 'sqm', 'rolls'],
                default: 'sheets'
            },
            price: { type: Number, required: true },  // Price for this tier (cents)
            discount_percentage: { type: Number, default: 0 }  // Discount vs base price
        }]
    },

    // Bulk pricing
    bulk_pricing: {
        type: Object,
        required: false,
        default: {},

        enabled: { type: Boolean, default: false },
        breaks: [{
            quantity: { type: Number },
            discount_percentage: { type: Number }
        }]
    }
}
```

**Usage**: Enables tiered pricing calculations based on order volume.

---

### 8. Technical Specifications

Add detailed technical properties:

```javascript
{
    // NEW FIELDS - Technical Details
    technical_specs: {
        type: Object,
        required: false,
        default: {},

        // Opacity
        opacity: {
            type: Number,
            default: 0,  // % (100 = completely opaque)
            min: 0,
            max: 100
        },

        // Brightness
        brightness: {
            type: Number,
            default: 0,  // ISO brightness value
            min: 0,
            max: 100
        },

        // Whiteness
        whiteness: {
            type: Number,
            default: 0,  // CIE whiteness
            min: 0,
            max: 100
        },

        // Smoothness
        smoothness: {
            type: String,
            enum: ['very_smooth', 'smooth', 'medium', 'rough'],
            default: 'medium'
        },

        // Bulk/Thickness
        bulk: {
            type: Number,
            default: 0  // cm³/g (volume per gram)
        },

        thickness_microns: {
            type: Number,
            default: 0  // Material thickness in microns
        },

        // Tensile strength
        tensile_strength: {
            type: Number,
            default: 0  // kN/m
        },

        // Tear resistance
        tear_resistance: {
            type: Number,
            default: 0  // mN
        },

        // Stiffness
        stiffness: {
            type: Number,
            default: 0  // mN
        }
    }
}
```

**Usage**:
- Helps customers select appropriate materials
- Supports quality comparisons
- Useful for technical documentation

---

### 9. Waste & Efficiency Factors

Add for accurate material consumption calculation:

```javascript
{
    // NEW FIELDS - Waste Calculation
    waste_factors: {
        type: Object,
        required: false,
        default: {},

        // Standard waste percentage
        standard_waste_percentage: {
            type: Number,
            default: 5  // % waste for normal production
        },

        // Waste by printing method
        waste_by_method: [{
            method: {
                type: String,
                enum: ['digital', 'offset', 'large_format']
            },
            waste_percentage: { type: Number }
        }],

        // Setup waste (sheets wasted during setup)
        setup_waste_sheets: {
            type: Number,
            default: 0
        },

        // Cutting/trim waste
        trim_waste_percentage: {
            type: Number,
            default: 3  // % lost to trimming
        },

        // Spoilage allowance
        spoilage_allowance: {
            type: Number,
            default: 2  // % allowance for defects
        }
    }
}
```

**Usage**:
- Calculates accurate material quantities including waste
- Adjusts costs for setup sheets
- Accounts for quality control rejects

---

### 10. Alternative Materials

Add to suggest substitutions:

```javascript
{
    // NEW FIELDS - Material Alternatives
    alternatives: {
        type: Array,
        required: false,
        default: [],

        items: [{
            catalogue_id: {
                type: mongoose.Schema.Types.ObjectId,
                ref: 'supplier_catalogues'
            },
            alternative_type: {
                type: String,
                enum: ['equivalent', 'upgrade', 'downgrade', 'substitute'],
                default: 'equivalent'
            },
            reason: { type: String },  // Why this is an alternative
            price_difference: { type: Number }  // % price difference
        }]
    }
}
```

**Usage**:
- Suggests alternatives when material is out of stock
- Offers upgrade/downgrade options
- Helps optimize costs

---

## Example: Complete Offset Paper Catalogue Entry

```javascript
{
    // Existing required fields
    tenant_id: "tenant_123",
    tenant_name: "Acme Printing",
    supplier: "paper_supplier_001",
    art_nr: "SILK-300-SRA3",
    material: "Silk Coated Paper 300gsm SRA3",
    material_id: ObjectId("..."),
    grs: 300,
    grs_id: ObjectId("..."),
    price: 12500,  // €125.00 per 250 sheets
    ean: "4012345678901",
    density: 1.2,
    height: 320,
    length: 450,
    width: 320,
    sheet: true,
    calc_type: "kg",

    // NEW: Material classification
    material_type: "paper_coated_silk",
    material_category: "sheet",
    material_grade: "premium",

    // NEW: Printing compatibility
    printing_methods: [
        {
            method: "offset",
            compatible: true,
            price_adjustment: 0,
            speed_adjustment: 100,
            quality_notes: "Excellent for offset, minimal dot gain"
        },
        {
            method: "digital",
            compatible: true,
            price_adjustment: 5,
            speed_adjustment: 90,
            quality_notes: "Good quality, slight speed reduction"
        }
    ],

    // NEW: Offset properties
    offset_properties: {
        ink_absorption: "medium",
        ink_absorption_rate: 15,
        drying_time: 30,
        coating_compatible: true,
        coating_types: [
            {
                type: "uv",
                drying_time: 0,  // Instant with UV
                cost_per_sqm: 150  // €1.50 per sqm
            },
            {
                type: "aqueous",
                drying_time: 60,
                cost_per_sqm: 80
            }
        ],
        anti_offset_powder: {
            required: false,
            cost_per_sheet: 0
        },
        grain_direction: "long_grain",
        optimal_humidity: { min: 45, max: 55 },
        optimal_temperature: { min: 20, max: 23 }
    },

    // NEW: Stock management
    stock_management: {
        in_stock: true,
        stock_quantity: 5000,
        stock_unit: "sheets",
        reorder_point: 1000,
        lead_time_days: 3,
        supplier_lead_time: 2,
        moq: 250,
        moq_unit: "sheets"
    },

    // NEW: Sustainability
    sustainability: {
        certifications: ["FSC", "EU_Ecolabel"],
        recycled_content: 0,
        recyclable: true,
        biodegradable: true,
        carbon_footprint: 0.8,  // kg CO2 per kg
        sustainable_source: true,
        eco_premium: 0  // No premium for this material
    },

    // NEW: Pricing tiers
    pricing_tiers: [
        {
            min_quantity: 1,
            max_quantity: 249,
            unit: "sheets",
            price: 50,  // €0.50 per sheet
            discount_percentage: 0
        },
        {
            min_quantity: 250,
            max_quantity: 999,
            unit: "sheets",
            price: 45,  // €0.45 per sheet
            discount_percentage: 10
        },
        {
            min_quantity: 1000,
            unit: "sheets",
            price: 40,  // €0.40 per sheet
            discount_percentage: 20
        }
    ],

    // NEW: Technical specs
    technical_specs: {
        opacity: 98,
        brightness: 95,
        whiteness: 160,
        smoothness: "very_smooth",
        bulk: 0.8,
        thickness_microns: 240,
        tensile_strength: 85,
        tear_resistance: 650,
        stiffness: 180
    },

    // NEW: Waste factors
    waste_factors: {
        standard_waste_percentage: 5,
        waste_by_method: [
            { method: "offset", waste_percentage: 3 },
            { method: "digital", waste_percentage: 2 }
        ],
        setup_waste_sheets: 50,
        trim_waste_percentage: 2,
        spoilage_allowance: 1
    }
}
```

---

## Implementation Guidelines

### When Adding New Fields:

1. **Always Optional**: New fields should have `required: false`
2. **Provide Defaults**: Include sensible default values
3. **Document Purpose**: Explain why each field is useful
4. **Consider Units**: Be explicit about units (mm, kg, %, etc.)
5. **Version Carefully**: Update API version if response structure changes

### Backward Compatibility Checklist:

- ✅ Existing fields are never removed
- ✅ Existing fields maintain same data types
- ✅ New fields are optional with defaults
- ✅ Old calculations continue to work
- ✅ Existing API responses remain valid

---

## Migration Path

### Phase 1: Add Model Fields
- Update SupplierCatalogue schema with new optional fields
- Deploy database schema changes
- Test with existing data (should work with defaults)

### Phase 2: Populate Data
- Create UI for entering new material properties
- Gradually populate existing materials with enhanced data
- Maintain quality with data validation

### Phase 3: Implement Calculation Logic
- Update calculation engine to use new fields when present
- Apply material-specific pricing adjustments
- Calculate waste accurately

### Phase 4: Enable Advanced Features
- Implement material recommendations
- Enable filtering by properties
- Show sustainability information to customers

---

## Testing Considerations

When adding these fields:

1. **Test NULL handling**: Calculations must work when new fields are absent
2. **Test defaults**: Verify default values are reasonable
3. **Test data types**: Ensure numbers are numbers, strings are strings
4. **Test arrays**: Handle empty arrays gracefully
5. **Test references**: Handle missing ObjectId references
6. **Test backward compatibility**: Old API calls should work unchanged

---

## Related Documentation

- See `MACHINE_MODEL_EXTENSIONS.md` for machine/press enhancements
- See `PAYLOAD_EXTENSIONS.md` for request/response payload additions
- See `OFFSET_PRINTING_IMPLEMENTATION.md` for complete implementation guide
- See `REFACTORING_PLAN.md` for overall refactoring strategy

---

## Material Data Quality

### Importance of Accurate Data:

Good material data is critical for accurate calculations:

- **Pricing accuracy**: Incorrect prices lead to wrong quotes
- **Material consumption**: Wrong GSM/dimensions cause material miscalculation
- **Production planning**: Incorrect drying times affect delivery dates
- **Quality**: Wrong material selection leads to production failures

### Data Validation:

Implement validation rules:

```javascript
// Example validation
if (grs < 60 || grs > 600) {
    throw new Error("GSM out of reasonable range for paper");
}

if (material_type === "paper_coated_gloss" && offset_properties.ink_absorption === "high") {
    console.warn("Unusual: coated papers typically have low ink absorption");
}

if (price <= 0) {
    throw new Error("Price must be greater than zero");
}
```

### Regular Audits:

- Review material data quarterly
- Update prices when supplier prices change
- Archive discontinued materials
- Consolidate duplicate entries
