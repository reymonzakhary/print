# V1 vs V2 Calculation Response - Complete Comparison

## Overview

**V1 (Unchanged):** Original calculation response - backward compatible, still works exactly as before
**V2 (New):** Detailed transparent response showing HOW calculations happened

## Routes

### V1 Route (Keep using for existing clients)
```
POST /test/v2-logic/shop/suppliers/:id/categories/:slug/products/calculate/price
```

### V2 Route (New detailed format)
```
POST /test/v2-pipeline/shop/suppliers/:id/categories/:slug/products/calculate/price
```

Same request body for both!

## Request (Same for Both)
```json
{
  "product": [
    {"key": "format", "value": "a4", "divider": "cover"},
    {"key": "printing-colors", "value": "44-full-color", "divider": "cover"},
    {"key": "weight", "value": "135-grs", "divider": "cover"},
    {"key": "material", "value": "woodfree-coated-glossy-mc", "divider": "cover"},
    {"key": "number-of-sides", "value": "4-sides", "divider": "cover"},
    {"key": "lamination", "value": "double-sided-matt", "divider": "cover"},
    {"key": "format", "value": "a4", "divider": "content"},
    {"key": "printing-colors", "value": "44-full-color", "divider": "content"},
    {"key": "weight", "value": "135-grs", "divider": "content"},
    {"key": "material", "value": "woodfree-coated-glossy-mc", "divider": "content"},
    {"key": "number-of-pages", "value": "96-pages", "divider": "content"}
  ],
  "quantity": 100,
  "vat": 21
}
```

## V1 Response (Old Format - Unchanged)

```json
{
  "type": "print",
  "connection": "8f05bfc5-f960-4bc5-9e93-47b39401419c",
  "external_id": "8f05bfc5-f960-4bc5-9e93-47b39401419c",
  "external_name": "reseller.prindustry.test",
  "calculation_type": "full_calculation",
  "divided": false,
  "quantity": 100,

  "items": [...],  // Full item array
  "product": [...],  // Full product array
  "category": {...},  // Full category object
  "margins": [],

  "calculation": [{
    "name": null,
    "items": [...],
    "dlv": [...],
    "machine": {...},
    "color": {...},
    "row_price": 4204.405,
    "duration": {
      "setup_time": 10,
      "print_time": 60,
      "total_time": 70
    },
    "price_list": [...],
    "details": {...},
    "price": {...},
    "error": {"status": 200}
  }],

  "prices": [{
    "id": "...",
    "qty": 100,
    "dlv": {...},
    "gross_price": 462484,
    "gross_ppp": 4624.84,
    "selling_price_ex": 462484,
    "selling_price_inc": 559605.64,
    "vat": 21,
    "vat_p": 97121.64
  }]
}
```

**V1 Characteristics:**
- ✅ Backward compatible
- ✅ All existing integrations work
- ❌ Hard to understand what happened
- ❌ Nested data structures
- ❌ No transparency in calculations

## V2 Response (New Detailed Format)

```json
{
  "version": "2.0",
  "calculation_type": "full_calculation",
  "divided": false,
  "timestamp": "2025-01-15T10:30:00.000Z",

  // 📊 Quick Summary (Easy Access)
  "summary": {
    "total_cost": {
      "value": 4624.84,
      "currency": "EUR",
      "formatted": "€4,624.84"
    },
    "cost_per_piece": {
      "value": 46.25,
      "currency": "EUR",
      "formatted": "€46.25"
    },
    "quantity": 100,
    "production_time": {
      "minutes": 90,
      "hours": 1.5,
      "estimated_days": 1,
      "formatted": "1.5h (1 days)"
    },
    "machine_used": "HP 7200",
    "machine_type": "printing"
  },

  // 📦 Product Configuration
  "product": {
    "category": {
      "name": "Brochures with cover",
      "slug": "brochures-with-cover",
      "type": "print"
    },
    "specifications": {
      "format": {
        "value": "a4",
        "display": "A4"
      },
      "printing-colors": {
        "value": "44-full-color",
        "display": "4/4 Full Color"
      },
      "weight": {
        "value": "135-grs",
        "display": "135 Grs"
      },
      "material": {
        "value": "woodfree-coated-glossy-mc",
        "display": "Woodfree Coated Glossy Mc"
      },
      "number-of-sides": {
        "value": "4-sides",
        "display": "4 sides"
      },
      "lamination": {
        "value": "double-sided-matt-lamination",
        "display": "Double Sided Matt Lamination"
      }
    },
    "quantity": 100
  },

  // 📐 Format Calculation
  "format_calculation": {
    "format_selected": {
      "name": "A4",
      "dimensions": {
        "width": 210,
        "height": 297,
        "unit": "mm"
      },
      "bleed": 3,
      "area_sqm": 0.06237
    },
    "pages": 4,
    "sheets_per_product": 1
  },

  // 📦 Material Selection (Shows HOW paper was chosen)
  "material_selection": {
    "material": {
      "name": "Woodfree Coated Glossy Mc",
      "slug": "woodfree-coated-glossy-mc"
    },
    "paper_specification": {
      "weight": "135 Grs",
      "type": "Woodfree Coated Glossy Mc",
      "finish": "Double Sided Matt Lamination"
    },
    "paper_details": {
      "supplier": "reymon",
      "thickness": 0.16,
      "density": 0.85,
      "gsm": 135,
      "price_per_kg": {
        "value": 1000.00,
        "currency": "EUR",
        "formatted": "€1,000.00"
      }
    }
  },

  // 🖨️ Machine Selection (Shows WHY this machine)
  "machine_selection": {
    "machine_chosen": {
      "id": "668e38b0d1911334d1635392",
      "name": "HP 7200",
      "type": "printing",
      "print_method": "digital"
    },
    "machine_specifications": {
      "sheet_size": {
        "width": 1050,
        "height": 550,
        "unit": "mm"
      },
      "speed": {
        "sheets_per_minute": 100,
        "sheets_per_hour": 6000
      },
      "capabilities": {
        "min_gsm": 60,
        "max_gsm": 300,
        "fed": "sheet"
      }
    },
    "why_this_machine": "Machine selected based on product size (210x297mm), material weight compatibility (60-300 gsm), and best cost efficiency for quantity."
  },

  // 🎨 Color Configuration (Front/Back breakdown)
  "color_configuration": {
    "printing_colors": {
      "name": "4/4 Full Color",
      "display": "4/4 Full Color"
    },
    "sides": {
      "front": 4,  // 4 colors front
      "back": 4,   // 4 colors back
      "total_sides": 4  // 4-sided product
    },
    "color_details": {
      "rpm": 0,
      "price_per_side": {
        "value": 0.01,
        "currency": "EUR",
        "formatted": "€0.01"
      },
      "delivery_options": [...]
    }
  },

  // 📊 Sheet Calculations (Step-by-Step Journey)
  "sheet_calculations": {
    "step_1_layout": {
      "description": "How many products fit on one sheet",
      "machine_sheet_size": {
        "width": 1050,
        "height": 550
      },
      "product_size_with_bleed": {
        "width": 216,
        "height": 303
      },
      "products_per_sheet": 2,
      "layout_orientation": "Landscape"
    },
    "step_2_quantity": {
      "description": "Calculate sheets needed for quantity",
      "products_wanted": 100,
      "products_per_sheet": 2,
      "sheets_needed": 50
    },
    "step_3_spoilage": {
      "description": "Add waste/spoilage for setup and testing",
      "spoilage_percentage": 100,
      "sheets_before_spoilage": 50,
      "spoilage_sheets": 50,
      "sheets_with_spoilage": 100
    },
    "step_4_printing": {
      "description": "Total sheets to print",
      "sheets_to_print": 100
    }
  },

  // 💰 Cost Breakdown (Every cost explained)
  "cost_breakdown": {
    "printing": {
      "description": "Cost of printing sheets",
      "sheets": 100,
      "price_per_sheet": {
        "value": 1.74,
        "currency": "EUR",
        "formatted": "€1.74"
      },
      "total": {
        "value": 174.35,
        "currency": "EUR",
        "formatted": "€174.35"
      }
    },
    "paper": {
      "description": "Cost of paper/substrate",
      "sheets": 100,
      "area_per_sheet_sqm": 0.3224,
      "total_area_sqm": 32.24,
      "price_per_sqm": {
        "value": 1.35,
        "currency": "EUR",
        "formatted": "€1.35"
      },
      "total": {
        "value": 43.52,
        "currency": "EUR",
        "formatted": "€43.52"
      }
    },
    "setup": {
      "description": "Machine setup cost",
      "setup_time_minutes": 10,
      "total": {
        "value": 50.00,
        "currency": "EUR",
        "formatted": "€50.00"
      }
    },
    "color": {
      "description": "Color printing cost",
      "total": {
        "value": 1.20,
        "currency": "EUR",
        "formatted": "€1.20"
      }
    },
    "lamination": {
      "description": "Lamination finishing",
      "total": {
        "value": 2500.00,
        "currency": "EUR",
        "formatted": "€2,500.00"
      }
    },
    "options": {
      "description": "Additional finishing options",
      "items": [
        {
          "name": "4 sides",
          "cost": {
            "value": 50.44,
            "currency": "EUR",
            "formatted": "€50.44"
          }
        }
      ],
      "total": {
        "value": 50.44,
        "currency": "EUR",
        "formatted": "€50.44"
      }
    },
    "subtotal": {
      "value": 2819.51,
      "currency": "EUR",
      "formatted": "€2,819.51"
    }
  },

  // ⏱️ Production Timeline (Time per operation)
  "production_timeline": {
    "setup": {
      "time_minutes": 10,
      "description": "Machine setup and calibration"
    },
    "printing": {
      "time_minutes": 60,
      "sheets": 100,
      "description": "Printing 100 sheets"
    },
    "cooling": null,
    "finishing": {
      "time_minutes": 20,
      "description": "Finishing operations"
    },
    "total": {
      "time_minutes": 90,
      "time_hours": 1.5,
      "estimated_delivery_days": 1
    }
  },

  // 🎯 Final Pricing
  "pricing": {
    "gross_price": {
      "value": 2819.51,
      "currency": "EUR",
      "formatted": "€2,819.51"
    },
    "price_per_piece": {
      "value": 28.20,
      "currency": "EUR",
      "formatted": "€28.20"
    },
    "margins_applied": null,  // Or margin details if internal=true
    "vat": {
      "percentage": 21,
      "amount": {
        "value": 592.10,
        "currency": "EUR",
        "formatted": "€592.10"
      }
    },
    "total_with_vat": {
      "value": 3411.61,
      "currency": "EUR",
      "formatted": "€3,411.61"
    },
    "delivery_options": [...]
  },

  // 🔧 Calculation Details (For debugging)
  "calculation_details": {
    "calculation_method": [...],
    "price_list": [...],
    "all_positions": [...]
  }
}
```

**V2 Characteristics:**
- ✅ Complete transparency
- ✅ Shows HOW calculations happened
- ✅ Material selection explained
- ✅ Machine reasoning shown
- ✅ Color breakdown (front/back)
- ✅ Step-by-step sheet calculations
- ✅ Lamination details included
- ✅ Every cost explained
- ✅ Production timeline
- ✅ Clean, organized structure
- ✅ Easy to understand

## Key Differences

| Feature | V1 | V2 |
|---------|----|----|
| **Format** | Nested arrays | Clean sections |
| **Transparency** | Result only | Full journey |
| **Material Info** | Basic | Full specs + supplier |
| **Machine Info** | ID + name | Specs + reasoning |
| **Colors** | Generic | Front/back breakdown |
| **Sheet Calc** | Final numbers | Step-by-step |
| **Lamination** | Included in total | Separate cost shown |
| **Timeline** | Basic duration | Per-operation breakdown |
| **Cost Breakdown** | One price | Every cost explained |
| **Money Format** | Integers (cents) | Objects with formatted strings |
| **Readability** | Hard | Easy |
| **Debugging** | Difficult | Simple |

## When to Use Each

### Use V1 When:
- Existing integrations already built
- Need backward compatibility
- Don't need detailed breakdown
- Quick price check only

### Use V2 When:
- Need to show calculation details to users
- Building new frontend
- Debugging calculations
- Want transparency
- Need to explain pricing
- Customer wants to understand costs

## Migration Path

1. **Phase 1:** Both V1 and V2 available
2. **Phase 2:** New clients use V2
3. **Phase 3:** Migrate existing clients gradually
4. **Phase 4:** V1 deprecated (optional, can keep both)

## Console Output

### V1
```
=== Hybrid Calculation (V1 input → V2 logic) ===
Supplier: 8f05bfc5...
Category: brochures-with-cover
✓ Category loaded
✓ Items enriched
=== Calculation Complete ===
Prices generated: 3
```

### V2
```
=== V2 Pipeline Calculation ===
🚀 V2 Calculation Pipeline - Starting
✓ Category loaded: Brochures with cover (4 machines)
✓ Products matched: 11 items
✓ Format calculated: 210x297mm
✓ Materials fetched: 1 materials
✓ Calculation type: Simple
✓ Machine combinations: 4 options
✓ Final price calculated: €2,819.51
✓ Response formatted
=== V2 Pipeline Complete ===
Response version: 2.0
Calculation type: full_calculation
Total cost: €2,819.51
Production time: 1.5h (1 days)
```

## Summary

**V1 = Keep for backward compatibility**
**V2 = Use for transparency and better UX**

Both work with same request, just different level of detail in response!

🎉 **Best of both worlds!**
