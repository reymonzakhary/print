# Machine Model Extensions

## Overview

This document describes optional fields that can be ADDED to the `SupplierMachine` model to support enhanced calculation features, particularly for offset printing and other printing methods.

**IMPORTANT**: All existing fields in the model are maintained and MUST NOT be removed. This document only covers additions.

---

## Current Machine Model Structure

### Existing Fields (DO NOT REMOVE)

```javascript
{
    // Identity & Tenant
    tenant_id: String (required),
    tenant_name: String (required),
    name: String (required),
    description: String (optional, default: ""),

    // Machine Type & Specifications
    type: String (required), // 'printing', 'covering', 'lamination', 'bundling'
    unit: String (optional, default: 'mm'),
    width: Number (required),
    height: Number (required),

    // Pricing & Performance
    spm: Number (optional, default: 0), // Sheets per minute
    price: Number (optional, default: 0),
    sqcm: Number (required), // Square centimeters
    ean: Number (required),
    pm: String (required), // Price model

    // Timing
    setup_time: Number (optional, default: 0),
    cooling_time: Number (optional, default: 0),
    cooling_time_per: Number (optional, default: 0),
    mpm: Number (optional, default: 0), // Minutes per meter

    // Configuration
    divide_start_cost: Boolean (optional, default: false),
    spoilage: Number (optional, default: 0),
    wf: Number (optional, default: 0), // Waste factor

    // Material Constraints
    min_gsm: Number (optional, default: 0),
    max_gsm: Number (optional, default: 0),
    colors: Array (optional, default: []),
    materials: Array (optional, default: []),

    // Printable Area
    printable_frame_length_min: Number (optional, default: 0),
    printable_frame_length_max: Number (optional, default: 0),
    fed: String (required, default: "sheet"),
    attributes: Array (optional),

    // Trim & Margins
    trim_area: Number (optional, default: 0),
    trim_area_exclude_y: Boolean (optional, default: false),
    trim_area_exclude_x: Boolean (optional, default: false),
    margin_right: Number (optional, default: 0),
    margin_left: Number (optional, default: 0),
    margin_top: Number (optional, default: 0),
    margin_bottom: Number (optional, default: 0),

    // Timestamps
    created_at: Date (default: Date.now),
    updated_at: Date (default: Date.now)
}
```

---

## Extensible Fields (CAN BE ADDED)

### 1. Printing Method Identification

Add to support multiple printing methods (digital, offset, large format):

```javascript
{
    // NEW FIELDS
    printing_method: {
        type: String,
        enum: ['digital', 'offset', 'large_format', 'screen', 'flexo'],
        default: 'digital',
        required: false
    },

    printing_method_details: {
        type: String,
        required: false,
        default: ""
    }
}
```

**Usage**: Allows the calculation engine to select appropriate calculation logic based on printing method.

---

### 2. Offset Printing Configuration

Add for offset printing machines (when `printing_method: 'offset'`):

```javascript
{
    // NEW FIELDS - Offset Plate Configuration
    offset_config: {
        type: Object,
        required: false,
        default: {},

        // Plate Configuration
        plate_required: {
            type: Boolean,
            default: true
        },

        plate_cost_per_color: {
            type: Number,
            default: 1500  // In cents (€15.00)
        },

        plate_size: {
            width: { type: Number, default: 0 },  // mm
            height: { type: Number, default: 0 }   // mm
        },

        plate_setup_time: {
            type: Number,
            default: 0  // Minutes per plate
        },

        max_plates: {
            type: Number,
            default: 4  // Maximum number of plates/colors per pass
        },

        // Plate Types
        plate_types: [{
            type: {
                type: String,
                enum: ['CTP', 'manual', 'polymer'],
                default: 'CTP'
            },
            cost: { type: Number, default: 0 },  // Cost in cents
            setup_time: { type: Number, default: 0 },  // Minutes
            max_impressions: { type: Number, default: 0 }  // Maximum uses
        }],

        // Plate Making Process
        plate_making: {
            ctp_available: { type: Boolean, default: true },  // Computer to Plate
            manual_time: { type: Number, default: 30 },  // Minutes for manual platemaking
            auto_time: { type: Number, default: 5 }   // Minutes for CTP
        },

        // Ink Configuration
        ink_config: {
            ink_cost_model: {
                type: String,
                enum: ['per_sheet', 'per_sqm', 'per_impression', 'per_color'],
                default: 'per_sheet'
            },

            ink_cost_per_unit: {
                type: Number,
                default: 0  // Cost in cents
            },

            ink_coverage: {
                light: { type: Number, default: 5 },   // % coverage
                medium: { type: Number, default: 15 },
                heavy: { type: Number, default: 30 },
                default: { type: Number, default: 15 }
            },

            spot_colors_supported: { type: Boolean, default: false },
            spot_color_extra_cost: { type: Number, default: 0 }  // Extra cost per spot color
        },

        // Press Configuration
        press_config: {
            press_type: {
                type: String,
                enum: ['sheet_fed', 'web_fed', 'digital_offset'],
                default: 'sheet_fed'
            },

            max_colors_per_pass: {
                type: Number,
                default: 4  // 4-color CMYK standard
            },

            perfecting: {
                type: Boolean,
                default: false  // Can print both sides in one pass
            },

            // Make-Ready Configuration
            makeready_time_base: {
                type: Number,
                default: 30  // Base setup time in minutes
            },

            makeready_time_per_color: {
                type: Number,
                default: 10  // Additional minutes per color
            },

            makeready_sheets: {
                type: Number,
                default: 50  // Sheets wasted during setup
            },

            // Economic Run Length
            economic_run_min: {
                type: Number,
                default: 500  // Minimum economical quantity
            },

            economic_run_optimal: {
                type: Number,
                default: 5000  // Optimal quantity for cost efficiency
            }
        }
    }
}
```

**Usage**:
- Enables accurate offset printing cost calculation
- Supports plate cost calculation based on colors (e.g., 4/4 = 8 plates, 4/0 = 4 plates)
- Calculates make-ready time and waste sheets
- Determines economic viability for given quantities

---

### 3. Large Format Printing Configuration

Add for large format/wide format machines:

```javascript
{
    // NEW FIELDS - Large Format Configuration
    large_format_config: {
        type: Object,
        required: false,
        default: {},

        max_width: {
            type: Number,
            default: 0  // Maximum printable width in mm
        },

        media_types: [{
            type: String,
            enum: ['banner', 'vinyl', 'canvas', 'paper', 'fabric', 'film']
        }],

        ink_type: {
            type: String,
            enum: ['solvent', 'eco_solvent', 'latex', 'UV', 'water_based'],
            default: 'eco_solvent'
        },

        ink_cost_per_sqm: {
            type: Number,
            default: 0  // Cost in cents per square meter
        },

        finishing_options: [{
            name: { type: String },
            cost_per_sqm: { type: Number },
            cost_per_unit: { type: Number },
            setup_time: { type: Number }
        }]
    }
}
```

**Usage**: Supports wide format banner/signage printing calculations.

---

### 4. Enhanced Timing & Production Planning

Add for more accurate production scheduling:

```javascript
{
    // NEW FIELDS - Enhanced Production Timing
    production_timing: {
        type: Object,
        required: false,
        default: {},

        warmup_time: {
            type: Number,
            default: 0  // Minutes to warm up machine
        },

        job_change_time: {
            type: Number,
            default: 15  // Minutes to change between jobs
        },

        cleanup_time: {
            type: Number,
            default: 10  // Minutes to clean after job
        },

        inspection_time_per_100: {
            type: Number,
            default: 2  // Minutes of quality check per 100 units
        },

        // Operating Hours
        operating_hours: {
            start_hour: { type: Number, default: 8 },   // 8 AM
            end_hour: { type: Number, default: 17 },    // 5 PM
            working_days: { type: Array, default: [1,2,3,4,5] }  // Mon-Fri
        },

        // Shifts
        shifts_available: {
            type: Number,
            default: 1  // Number of shifts per day
        },

        shift_hours: {
            type: Number,
            default: 8  // Hours per shift
        }
    }
}
```

**Usage**:
- Enables accurate delivery date calculation
- Supports production scheduling
- Accounts for setup/teardown time in cost calculations

---

### 5. Maintenance & Availability

Add for production planning and cost adjustments:

```javascript
{
    // NEW FIELDS - Machine Availability
    maintenance_config: {
        type: Object,
        required: false,
        default: {},

        availability_percentage: {
            type: Number,
            default: 95  // % of time machine is operational
        },

        scheduled_maintenance: [{
            day_of_week: { type: Number },  // 0-6 (Sunday-Saturday)
            start_time: { type: String },   // "14:00"
            duration: { type: Number }       // Minutes
        }],

        maintenance_cost_per_sheet: {
            type: Number,
            default: 0  // Maintenance cost allocation per sheet (cents)
        }
    }
}
```

**Usage**: Adjusts production capacity and costs based on maintenance schedules.

---

### 6. Quality & Substrate Compatibility

Add for better material matching:

```javascript
{
    // NEW FIELDS - Enhanced Material Compatibility
    substrate_compatibility: {
        type: Object,
        required: false,
        default: {},

        // Extended GSM ranges by type
        gsm_ranges: [{
            material_type: { type: String },  // 'coated', 'uncoated', 'cardboard'
            min_gsm: { type: Number },
            max_gsm: { type: Number },
            speed_adjustment: { type: Number, default: 100 }  // % of normal speed
        }],

        // Coating compatibility
        coatings_supported: [{
            type: String,  // 'gloss', 'matte', 'silk', 'uncoated'
            speed_adjustment: { type: Number, default: 100 },
            quality_grade: { type: String, enum: ['premium', 'standard', 'economy'] }
        }],

        // Finish quality
        finish_quality: {
            type: String,
            enum: ['premium', 'standard', 'economy', 'draft'],
            default: 'standard'
        }
    }
}
```

**Usage**: Allows calculation engine to select appropriate machine based on material requirements and adjust costs/speeds accordingly.

---

### 7. Color Management

Add for advanced color calculation:

```javascript
{
    // NEW FIELDS - Advanced Color Management
    color_management: {
        type: Object,
        required: false,
        default: {},

        max_colors_front: {
            type: Number,
            default: 4  // CMYK
        },

        max_colors_back: {
            type: Number,
            default: 4
        },

        spot_color_capability: {
            type: Boolean,
            default: false
        },

        spot_color_stations: {
            type: Number,
            default: 0
        },

        // Color notation parsing support
        color_notations: [{
            notation: { type: String },  // e.g., "4/4", "4/0", "4/1+1"
            description: { type: String }
        }],

        // Cost per color
        color_cost_model: {
            fixed_cost_per_color: { type: Number, default: 0 },
            variable_cost_per_color_per_sheet: { type: Number, default: 0 }
        }
    }
}
```

**Usage**: Supports complex color configurations like "4/4" (4 colors front + 4 colors back) for offset printing.

---

### 8. Position/Imposition Support

Add for sheet layout optimization:

```javascript
{
    // NEW FIELDS - Imposition Configuration
    imposition_config: {
        type: Object,
        required: false,
        default: {},

        supports_imposition: {
            type: Boolean,
            default: false
        },

        // Gripper edge (non-printable area for sheet feeding)
        gripper_edge: {
            size: { type: Number, default: 10 },  // mm
            side: { type: String, enum: ['top', 'bottom', 'left', 'right'], default: 'top' }
        },

        // Gap between products on sheet
        min_gap_between_products: {
            type: Number,
            default: 5  // mm
        },

        // Bleed requirements
        bleed_requirements: {
            default_bleed: { type: Number, default: 3 },  // mm
            max_bleed: { type: Number, default: 5 }
        },

        // Auto-rotation support
        auto_rotation: {
            type: Boolean,
            default: true  // Can rotate products to optimize sheet usage
        }
    }
}
```

**Usage**: Enables automatic calculation of how many products fit on a sheet and optimal positioning.

---

### 9. Cost Centers & Accounting

Add for internal cost tracking:

```javascript
{
    // NEW FIELDS - Cost Tracking
    cost_accounting: {
        type: Object,
        required: false,
        default: {},

        cost_center: {
            type: String,
            default: ""  // Internal cost center code
        },

        hourly_rate: {
            type: Number,
            default: 0  // Machine hourly rate in cents
        },

        labor_cost_per_hour: {
            type: Number,
            default: 0  // Operator cost per hour
        },

        overhead_percentage: {
            type: Number,
            default: 0  // Overhead as % of direct costs
        },

        depreciation_per_sheet: {
            type: Number,
            default: 0  // Machine depreciation cost per sheet
        }
    }
}
```

**Usage**: Enables detailed cost breakdown for internal analysis and pricing decisions.

---

## Implementation Guidelines

### When Adding New Fields:

1. **Always Optional**: New fields should have `required: false` to maintain backward compatibility
2. **Provide Defaults**: Always include sensible default values
3. **Document Usage**: Update this document when adding fields
4. **Version API**: Consider creating V2 endpoints if response structure changes significantly
5. **Migrate Gradually**: Add fields to model first, then implement calculation logic

### Backward Compatibility Checklist:

- ✅ Existing fields are never removed
- ✅ Existing fields maintain same data types
- ✅ New fields are optional with defaults
- ✅ Old calculations continue to work without new fields
- ✅ Existing API responses remain valid

---

## Example: Complete Offset Machine Configuration

```javascript
{
    // Existing required fields
    tenant_id: "tenant_123",
    tenant_name: "Acme Printing",
    name: "Heidelberg Speedmaster SM 52-4",
    description: "4-color offset press, sheet-fed",
    type: "printing",
    width: 520,
    height: 370,
    spm: 15000,
    price: 120000,
    sqcm: 192400,
    ean: 12345,
    pm: "per_sheet",
    setup_time: 30,
    min_gsm: 80,
    max_gsm: 400,
    colors: [],
    materials: [],

    // NEW: Printing method identification
    printing_method: "offset",
    printing_method_details: "Sheet-fed offset lithography",

    // NEW: Offset configuration
    offset_config: {
        plate_required: true,
        plate_cost_per_color: 1500,  // €15.00
        plate_size: { width: 520, height: 370 },
        plate_setup_time: 5,
        max_plates: 4,

        plate_types: [{
            type: "CTP",
            cost: 1500,
            setup_time: 5,
            max_impressions: 50000
        }],

        plate_making: {
            ctp_available: true,
            manual_time: 30,
            auto_time: 5
        },

        ink_config: {
            ink_cost_model: "per_sheet",
            ink_cost_per_unit: 2,  // €0.02 per sheet
            ink_coverage: {
                light: 5,
                medium: 15,
                heavy: 30,
                default: 15
            },
            spot_colors_supported: false
        },

        press_config: {
            press_type: "sheet_fed",
            max_colors_per_pass: 4,
            perfecting: false,
            makeready_time_base: 30,
            makeready_time_per_color: 10,
            makeready_sheets: 50,
            economic_run_min: 500,
            economic_run_optimal: 5000
        }
    },

    // NEW: Enhanced timing
    production_timing: {
        warmup_time: 15,
        job_change_time: 15,
        cleanup_time: 10,
        inspection_time_per_100: 2,
        operating_hours: {
            start_hour: 8,
            end_hour: 17,
            working_days: [1,2,3,4,5]
        }
    },

    // NEW: Color management
    color_management: {
        max_colors_front: 4,
        max_colors_back: 4,
        spot_color_capability: false,
        color_notations: [
            { notation: "4/4", description: "4 colors front, 4 colors back (CMYK both sides)" },
            { notation: "4/0", description: "4 colors front only" },
            { notation: "1/1", description: "1 color both sides (black both sides)" }
        ]
    },

    // NEW: Imposition support
    imposition_config: {
        supports_imposition: true,
        gripper_edge: { size: 10, side: "top" },
        min_gap_between_products: 5,
        bleed_requirements: { default_bleed: 3, max_bleed: 5 },
        auto_rotation: true
    }
}
```

---

## Migration Path

### Phase 1: Add Model Fields
- Update SupplierMachine schema with new optional fields
- Deploy database schema changes
- **No calculation logic changes needed yet**

### Phase 2: Implement Calculation Logic
- Create OffsetCalculator class
- Update CalculationEngine to use new fields when present
- Maintain existing calculation path for machines without new fields

### Phase 3: Frontend Updates
- Update machine management UI to support new fields
- Add offset-specific configuration forms
- Display enhanced calculation results

### Phase 4: Full Rollout
- Migrate existing offset machines to use new configuration
- Train users on new features
- Monitor and optimize

---

## Testing Considerations

When adding these fields:

1. **Test with NULL/undefined**: Ensure calculations work when new fields are absent
2. **Test with defaults**: Verify default values produce reasonable results
3. **Test edge cases**: Min/max values, zero values, empty arrays
4. **Test combinations**: Different printing_method values with different configs
5. **Test backward compatibility**: Old API clients should continue working

---

## Related Documentation

- See `CATALOGUE_MODEL_EXTENSIONS.md` for catalogue/material enhancements
- See `PAYLOAD_EXTENSIONS.md` for request/response payload additions
- See `OFFSET_PRINTING_IMPLEMENTATION.md` for complete implementation guide
- See `REFACTORING_PLAN.md` for overall refactoring strategy
