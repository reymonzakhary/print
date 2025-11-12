# Hybrid Calculation Testing Guide

## Overview

The **Hybrid Calculation Controller** allows you to test the new V2 calculation logic while keeping your existing V1 payload format. This means:

- ✅ No changes to your client code
- ✅ Keep existing product array format
- ✅ Use new accurate digital printing calculations
- ✅ Compare old vs new results side-by-side

---

## How It Works

```
Your V1 Payload (product array)
         ↓
   Transformer Service
         ↓
   V2 Format (clean objects)
         ↓
   V2 Calculation Logic
         ↓
   V1 Compatible Response
```

**Magic**: You send V1 format, get V2 calculations, receive V1-compatible response!

---

## Test Routes

### 1. Internal Calculation (with margins)
```
POST /test/v2-logic/suppliers/:supplier_id/categories/:slug/products/calculate/price
```

### 2. Shop Calculation (without margins)
```
POST /test/v2-logic/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price
```

### 3. Price List
```
POST /test/v2-logic/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/list
```

### 4. Debug Transformation
```
POST /test/v2-logic/debug/transform
```
Shows how your V1 payload is transformed (without running calculation)

---

## Your Example Payload

```javascript
{
  product: [
    {
      key: 'format',
      key_id: '6674377469c372eb54d1ce78',
      value: 'a4',
      value_id: '6674377469c372eb54d1ce7c',
      divider: 'cover'
    },
    {
      key: 'printing-colors',
      key_id: '6674377469c372eb54d1ce9e',
      value: '44-full-color',
      value_id: '6907772a0f440364371c0bb1',
      divider: 'cover'
    },
    {
      key: 'weight',
      key_id: '6674377469c372eb54d1ce89',
      value: '135-grs',
      value_id: '6674377469c372eb54d1ce8c',
      divider: 'cover'
    },
    {
      key: 'material',
      key_id: '6674377469c372eb54d1ce9b',
      value: 'woodfree-coated-glossy-mc',
      value_id: '6674377469c372eb54d1ce9c',
      divider: 'cover'
    },
    {
      key: 'number-of-sides',
      key_id: '6705255845a0a072806d7421',
      value: '4-sides',
      value_id: '670539a6779cd7c4be226cdf',
      divider: 'cover'
    },
    {
      key: 'afwerking',
      key_id: '67db2fd4b2c8d2caec2c1ffb',
      value: 'double-sided-matt-lamination',
      value_id: '67dd624409a1a13108bc3515',
      divider: 'cover'
    }
  ],
  quantity: 40,
  vat: '21',
  vat_override: false
}
```

### What Gets Extracted

The transformer automatically extracts:

- **Format**: A4 (210×297mm) from `format: 'a4'`
- **Colors**: 4/4 (CMYK both sides) from `'44-full-color'`
- **Weight**: 135 GSM from `'135-grs'`
- **Material**: Coated glossy paper from `'woodfree-coated-glossy-mc'`
- **Sides**: 4 sides from `'4-sides'`
- **Finishing**: Matte lamination (both sides) from `'double-sided-matt-lamination'`

---

## Testing Steps

### Step 1: Test Debug Transformation

First, see how your payload is transformed:

```bash
curl -X POST http://localhost:3333/test/v2-logic/debug/transform \
  -H "Content-Type: application/json" \
  -d '{
    "product": [
      {"key": "format", "value": "a4", "divider": "cover"},
      {"key": "printing-colors", "value": "44-full-color", "divider": "cover"},
      {"key": "weight", "value": "135-grs", "divider": "cover"},
      {"key": "material", "value": "woodfree-coated-glossy-mc", "divider": "cover"},
      {"key": "afwerking", "value": "double-sided-matt-lamination", "divider": "cover"}
    ],
    "quantity": 40,
    "vat": "21"
  }'
```

**Expected Output**:
```json
{
  "message": "V1 to V2 transformation (no calculation)",
  "v1_input": { ... },
  "v2_output": {
    "format": { "width": 210, "height": 297, "name": "A4" },
    "material": { "type": "paper_coated_gloss", "gsm": 135 },
    "colors": { "front": 4, "back": 4 },
    "finishing": [
      { "type": "lamination", "lamination_type": "matte", "sides": "both" }
    ]
  },
  "transformation_details": {
    "format_extracted": true,
    "material_extracted": true,
    "colors_extracted": true,
    "finishing_count": 1
  }
}
```

---

### Step 2: Test Actual Calculation

Run calculation with your existing payload:

```bash
curl -X POST http://localhost:3333/test/v2-logic/shop/suppliers/8297018d-8ca8-4906-b6d8-77fdf19c71dd/categories/magazines/products/calculate/price \
  -H "Content-Type: application/json" \
  -d @your-payload.json
```

**Response** (V1-compatible format):
```json
{
  "type": "print",
  "connection": "8297018d-8ca8-4906-b6d8-77fdf19c71dd",
  "calculation_type": "full_calculation",
  "items": [ ... ],  // Your original product array
  "product": [ ... ],  // Your original product array
  "category": { ... },
  "quantity": 40,
  "calculation": [ ... ],  // Machine results
  "prices": [
    {
      "id": "abc123",
      "qty": 40,
      "p": 125.50,
      "ppp": 3.14,
      "selling_price_inc": 151.86,
      "v2_breakdown": {
        "machine": { "name": "HP Indigo", "type": "digital" },
        "base_cost": 98.50,
        "timing": {
          "setup_time": 5,
          "print_time": 8,
          "total_time": 13
        },
        "sheets": {
          "required": 2,
          "with_waste": 5,
          "products_per_sheet": 20
        }
      }
    }
  ],
  "v2_enhanced": true,
  "v2_configuration": {
    "format": { "width": 210, "height": 297 },
    "colors": { "front": 4, "back": 4 }
  }
}
```

---

### Step 3: Compare with V1

Run the SAME payload through old V1 route:

```bash
# Old V1 calculation
curl -X POST http://localhost:3333/shop/suppliers/8297018d-8ca8-4906-b6d8-77fdf19c71dd/categories/magazines/products/calculate/price \
  -H "Content-Type: application/json" \
  -d @your-payload.json > v1-result.json

# New V2 logic (hybrid)
curl -X POST http://localhost:3333/test/v2-logic/shop/suppliers/8297018d-8ca8-4906-b6d8-77fdf19c71dd/categories/magazines/products/calculate/price \
  -H "Content-Type: application/json" \
  -d @your-payload.json > v2-result.json

# Compare
diff v1-result.json v2-result.json
```

---

## What Gets Transformed

### Supported calc_ref Values

| calc_ref | Extracts | Example |
|----------|----------|---------|
| `format` | Width, height, bleed | a4 → 210×297mm |
| `printing-colors` | Front/back colors | 44-full-color → 4/4 |
| `weight` | GSM | 135-grs → 135 gsm |
| `material` | Material type, GSM | woodfree-coated-glossy-mc → coated gloss |
| `number-of-pages` | Page count | 96-pages → 96 |
| `number-of-sides` | Sides | 4-sides → 4 |
| `afwerking` | Finishing type | double-sided-matt-lamination → lamination |
| `lamination` | Lamination details | gloss-lamination → gloss |
| `binding_method` | Binding type | perfect-binding → perfect |
| `binding_direction` | Binding direction | left → left |

### Divider Support

The transformer respects dividers:
- `cover` - Cover specifications
- `content` - Content specifications
- `default` - Default specifications

Multiple dividers are supported (e.g., different formats for cover vs content).

---

## Response Differences

### V1 Response (Old)
```json
{
  "type": "print",
  "prices": [
    {
      "qty": 40,
      "p": 125.50,
      "ppp": 3.14
      // Basic info only
    }
  ]
}
```

### Hybrid Response (New - V2 logic, V1 format)
```json
{
  "type": "print",
  "prices": [
    {
      "qty": 40,
      "p": 125.50,
      "ppp": 3.14,
      // PLUS V2 enhancements:
      "v2_breakdown": {
        "machine": { "name": "HP Indigo", "type": "digital" },
        "base_cost": 98.50,
        "discount_amount": 0,
        "margin_amount": 27.00,
        "timing": {
          "setup_time": 5,
          "print_time": 8,
          "total_time": 13,
          "estimated_delivery_days": 1
        },
        "sheets": {
          "required": 2,
          "with_waste": 5,
          "products_per_sheet": 20,
          "sheet_size": { "width": 330, "height": 487 }
        },
        "method": "digital"
      }
    }
  ],
  "v2_enhanced": true,
  "v2_configuration": { ... },
  "v2_machines_calculated": 2
}
```

**Benefit**: Old clients ignore new fields, new clients get enhanced data!

---

## Console Logging

The hybrid controller logs transformation details:

```
=== Hybrid Calculation (V1 input → V2 logic) ===
Supplier: 8297018d-8ca8-4906-b6d8-77fdf19c71dd
Category: magazines
Quantity: 40
Products count: 11

Transformed to V2:
- Format: A4 210x297
- Material: paper_coated_gloss 135gsm
- Colors: 4/4
- Finishing: 1 operations

V2 Calculation complete:
- Machines calculated: 2
- Prices: 2

=== Calculation Complete ===
```

Check your console to see transformation details!

---

## Migration Path

### Phase 1: Test (Current)
```
Old route: /shop/suppliers/.../calculate/price  (V1 logic)
Test route: /test/v2-logic/shop/suppliers/.../calculate/price  (V2 logic)

Compare results, validate accuracy
```

### Phase 2: Gradual Rollout
```
Option A: Replace old controllers one route at a time
Option B: Add flag to switch between V1/V2 logic
Option C: Use test routes for specific categories first
```

### Phase 3: Full Migration
```
Replace all old controllers with hybrid controllers
V2 logic becomes default
Remove old calculation classes
```

---

## Troubleshooting

### Issue: Transformation Not Working

**Check**:
1. Are `key` names matching calc_ref patterns?
2. Are `value` names in expected format?
3. Check console logs for transformation details

**Debug**:
```bash
curl -X POST http://localhost:3333/test/v2-logic/debug/transform \
  -H "Content-Type: application/json" \
  -d @your-payload.json
```

### Issue: Wrong Material/Format Extracted

**Solution**: The transformer uses pattern matching. If your values don't match patterns, you may need to:

1. Update transformer patterns in `V1toV2PayloadTransformer.js`
2. Add custom mappings for your specific values
3. Use more standard naming conventions

### Issue: Missing Finishing Operations

**Check**: Finishing is extracted from `key: 'afwerking'` or `key: 'lamination'`.

**Add more** finishing types in `_extractFinishing()` method if needed.

---

## Supported Features

### ✅ Fully Supported
- Digital printing calculation
- Lamination (gloss, matte, soft-touch)
- Format extraction (A4, A5, A3, custom)
- Color extraction (4/4, 4/0, 1/1, etc.)
- Material type detection
- Weight/GSM extraction
- Multi-divider support (cover, content)
- Price list generation

### 🚧 Partially Supported
- Finishing operations (die-cut, fold, etc.) - basic support
- Binding - structure extracted but calculation TBD
- Complex multi-part products

### 📋 Planned
- Offset printing calculation
- Large format printing
- More finishing types
- Visual layout generation

---

## Best Practices

1. **Always test with debug endpoint first** to verify transformation
2. **Compare with V1 results** to validate accuracy
3. **Check console logs** for transformation details
4. **Start with simple products** then move to complex ones
5. **Report issues** with specific payload examples

---

## Example: Your Exact Payload

Using your provided payload:

```bash
curl -X POST http://localhost:3333/test/v2-logic/shop/suppliers/8297018d-8ca8-4906-b6d8-77fdf19c71dd/categories/magazines/products/calculate/price \
  -H "Content-Type: application/json" \
  -d '{
    "product": [
      {
        "key": "format",
        "key_id": "6674377469c372eb54d1ce78",
        "value": "a4",
        "value_id": "6674377469c372eb54d1ce7c",
        "divider": "cover"
      },
      {
        "key": "printing-colors",
        "key_id": "6674377469c372eb54d1ce9e",
        "value": "44-full-color",
        "value_id": "6907772a0f440364371c0bb1",
        "divider": "cover"
      },
      {
        "key": "weight",
        "key_id": "6674377469c372eb54d1ce89",
        "value": "135-grs",
        "value_id": "6674377469c372eb54d1ce8c",
        "divider": "cover"
      },
      {
        "key": "material",
        "key_id": "6674377469c372eb54d1ce9b",
        "value": "woodfree-coated-glossy-mc",
        "value_id": "6674377469c372eb54d1ce9c",
        "divider": "cover"
      },
      {
        "key": "number-of-sides",
        "key_id": "6705255845a0a072806d7421",
        "value": "4-sides",
        "value_id": "670539a6779cd7c4be226cdf",
        "divider": "cover"
      },
      {
        "key": "afwerking",
        "key_id": "67db2fd4b2c8d2caec2c1ffb",
        "value": "double-sided-matt-lamination",
        "value_id": "67dd624409a1a13108bc3515",
        "divider": "cover"
      },
      {
        "key": "format",
        "key_id": "6674377469c372eb54d1ce78",
        "value": "a4",
        "value_id": "6674377469c372eb54d1ce7c",
        "divider": "content"
      },
      {
        "key": "printing-colors",
        "key_id": "6674377469c372eb54d1ce9e",
        "value": "44-full-color",
        "value_id": "6907772a0f440364371c0bb1",
        "divider": "content"
      },
      {
        "key": "weight",
        "key_id": "6674377469c372eb54d1ce89",
        "value": "135-grs",
        "value_id": "6674377469c372eb54d1ce8c",
        "divider": "content"
      },
      {
        "key": "material",
        "key_id": "6674377469c372eb54d1ce9b",
        "value": "woodfree-coated-glossy-mc",
        "value_id": "6674377469c372eb54d1ce9c",
        "divider": "content"
      },
      {
        "key": "number-of-pages",
        "key_id": "66b4bb0dc60a729bb6d963e3",
        "value": "96-pages",
        "value_id": "66b4bb0ec60a729bb6d96409",
        "divider": "content"
      }
    ],
    "suppliers": [
      {
        "host_id": "92858cee-ef81-4875-ad6a-8d1a43ded582",
        "supplier_id": "8297018d-8ca8-4906-b6d8-77fdf19c71dd"
      }
    ],
    "divided": false,
    "quantity": 40,
    "vat": "21",
    "vat_override": false
  }'
```

**This will**:
1. Extract A4 format
2. Extract 4/4 colors (CMYK both sides)
3. Extract 135gsm weight
4. Extract coated glossy material
5. Extract double-sided matte lamination
6. Extract 96 pages (for content)
7. Calculate using V2 digital printing logic
8. Return V1-compatible response with V2 enhancements

---

## Summary

✅ **Keep your existing payload format**
✅ **Test new calculations without code changes**
✅ **Compare old vs new side-by-side**
✅ **Get enhanced data (timing, sheets, breakdown)**
✅ **Backward compatible responses**
✅ **Easy migration path**

**Next**: Test with your actual payloads and compare results!
