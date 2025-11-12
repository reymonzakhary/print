# V2 Calculation API - Digital Printing Focus

## 🚀 Overview

**V2 API** is a complete rewrite of the calculation engine with focus on **accurate digital printing calculations** and support for all finishing types. It provides better cost breakdown, timing estimates, and machine-specific calculations.

**Status**: ✅ Ready for Testing
**Base URL**: `/v2`
**Compatibility**: Works alongside V1 (no breaking changes)

---

## 🎯 Key Features

### What Makes V2 Better?

1. **Accurate Digital Printing**
   - Proper click/impression cost calculation
   - Coverage-based toner/ink costs
   - Sheet optimization and waste calculation
   - Speed and quality considerations

2. **Machine-Specific Calculators**
   - `DigitalPrintingCalculator` - Accurate digital press calculations
   - `LaminationCalculator` - All lamination types (gloss, matte, soft-touch, etc.)
   - `FinishingCalculator` - Die-cutting, folding, perforating, scoring, drilling

3. **Better Cost Breakdown**
   - Click costs vs material costs
   - Setup costs
   - Finishing costs
   - Timing estimates
   - Cost per piece

4. **Multiple Machine Comparison**
   - Single request calculates for all compatible machines
   - Compare digital vs offset vs large format
   - Choose best machine for the job

5. **Clean API Design**
   - Structured input (not legacy arrays)
   - Clear error messages
   - Proper HTTP status codes
   - Comprehensive responses

---

## 📍 Endpoints

### 1. Main Calculation
```
POST /v2/calculate
```

Calculate price for a print job using all compatible machines.

**Request Body**:
```json
{
  "slug": "business-cards",
  "supplier_id": "tenant_123",
  "quantity": 500,
  "format": {
    "width": 85,
    "height": 55,
    "bleed": 3
  },
  "material": {
    "type": "paper_coated",
    "gsm": 300,
    "price": 4500,
    "calc_type": "kg"
  },
  "colors": {
    "front": 4,
    "back": 4
  },
  "finishing": [
    {
      "type": "lamination",
      "lamination_type": "gloss",
      "sides": "front"
    }
  ],
  "contract": null,
  "internal": false
}
```

**Response**:
```json
{
  "api_version": "v2",
  "type": "print",
  "connection": "tenant_123",
  "external_id": "tenant_123",
  "calculation_type": "full_calculation_v2",
  "category": {
    "_id": "cat123",
    "slug": "business-cards",
    "name": "Business Cards"
  },
  "configuration": {
    "quantity": 500,
    "format": { "width": 85, "height": 55, "bleed": 3 },
    "material": { "type": "paper_coated", "gsm": 300 },
    "colors": { "front": 4, "back": 4 },
    "finishing": [...]
  },
  "machines_calculated": 2,
  "machines": [
    {
      "id": "machine1",
      "name": "HP Indigo 7900",
      "type": "digital",
      "method": "digital"
    },
    {
      "id": "machine2",
      "name": "Xerox Versant 280",
      "type": "digital",
      "method": "digital"
    }
  ],
  "prices": [
    {
      "id": "abc123",
      "qty": 500,
      "dlv": { "days": 3, "title": "Standard" },
      "p": 125.50,
      "ppp": 0.251,
      "selling_price_ex": 125.50,
      "selling_price_inc": 151.86,
      "vat": 21,
      "vat_p": 26.36,
      "v2_breakdown": {
        "machine": {
          "id": "machine1",
          "name": "HP Indigo 7900",
          "type": "digital"
        },
        "base_cost": 98.50,
        "discount_amount": 0,
        "cost_after_discount": 98.50,
        "margin_amount": 27.00,
        "timing": {
          "setup_time": 5,
          "print_time": 12,
          "cooling_time": 0,
          "total_time": 17,
          "estimated_delivery_days": 1
        },
        "sheets": {
          "required": 28,
          "with_waste": 31,
          "waste_percentage": 3,
          "products_per_sheet": 18,
          "sheet_size": { "width": 330, "height": 487 }
        },
        "method": "digital"
      }
    }
  ]
}
```

---

### 2. Internal Calculation
```
POST /v2/calculate/internal
```

Same as `/calculate` but forces `internal: true` (includes margins and profit).

---

### 3. Shop Calculation
```
POST /v2/calculate/shop
```

Same as `/calculate` but forces `internal: false` (excludes internal margins).

---

### 4. Price List
```
POST /v2/calculate/price-list
```

Calculate prices for multiple quantities in one request.

**Request Body**:
```json
{
  "slug": "business-cards",
  "supplier_id": "tenant_123",
  "quantities": [100, 250, 500, 1000, 2500],
  "format": {...},
  "material": {...},
  "colors": {...},
  "internal": false
}
```

**Response**: Same structure as `/calculate` but with additional `price_variations` array.

---

### 5. Machine Types
```
GET /v2/calculate/machine-types
```

Get available machine types and their configuration requirements.

**Response**:
```json
{
  "api_version": "v2",
  "machine_types": [
    {
      "type": "digital",
      "name": "Digital Printing",
      "description": "High-quality digital printing for short to medium runs",
      "required_config": ["format", "material", "colors"],
      "optional_config": ["coverage", "finishing"],
      "best_for": {
        "min_quantity": 1,
        "max_quantity": 5000,
        "turnaround": "fast"
      }
    },
    {...}
  ]
}
```

---

## 📝 Request Format

### Format Object
```json
{
  "width": 85,        // mm
  "height": 55,       // mm
  "bleed": 3          // mm (optional, default: 3)
}
```

### Material Object
```json
{
  "type": "paper_coated",    // Material type
  "gsm": 300,                // Grams per square meter
  "price": 4500,             // Price in cents (€45.00 per unit)
  "calc_type": "kg"          // "kg", "sheet", or "sqm"
}
```

### Colors Object
```json
{
  "front": 4,    // Colors on front (0-4 for CMYK, or more for spot)
  "back": 4      // Colors on back
}
```

### Finishing Array
```json
[
  {
    "type": "lamination",
    "lamination_type": "gloss",    // gloss, matte, soft-touch, anti-scratch, holographic
    "sides": "front"                // front, back, both
  },
  {
    "type": "die-cut",
    "custom_shape": true
  },
  {
    "type": "fold",
    "fold_type": "standard",        // standard, complex
    "fold_count": 2
  },
  {
    "type": "perforate"
  },
  {
    "type": "score"
  },
  {
    "type": "drill",
    "hole_count": 2
  }
]
```

---

## 🧮 Digital Printing Calculation Logic

### How Digital Printing Cost is Calculated

1. **Sheet Requirements**
   ```
   Products Per Sheet = floor(
     (MachineWidth - GripperEdge - Margin) / (ProductWidth + Margin)
   ) * floor(
     (MachineHeight - GripperEdge - Margin) / (ProductHeight + Margin)
   )

   Sheets Required = ceil(Quantity / Products Per Sheet)
   Sheets With Waste = Sheets Required * (1 + Waste%) + Setup Waste
   ```

2. **Click Cost** (Toner/Ink)
   ```
   Base Click Cost = €0.02 per side (for 5% coverage, A4 size)

   Adjusted Click Cost = Base * (Coverage / 5) * (Sheet Size / A4 Size)

   Front Cost = Adjusted Click Cost (if front colors > 0)
   Back Cost = Adjusted Click Cost (if back colors > 0)

   Total Click Cost = (Front + Back) * Sheets With Waste
   ```

3. **Material Cost**
   ```
   Sheet Area (sqm) = (Width * Height) / 1,000,000
   Sheet Weight (kg) = Sheet Area * GSM / 1000

   Material Cost Per Sheet = Sheet Weight * Price Per Kg
   Total Material Cost = Material Cost Per Sheet * Sheets With Waste
   ```

4. **Setup Cost**
   ```
   Setup Cost = Machine Setup Cost (or Setup Time * Hourly Rate)
   ```

5. **Finishing Cost**
   - Calculated separately for each finishing operation
   - Lamination: Based on square meters
   - Die-cutting: Based on pieces or time
   - Folding: Based on pieces and fold complexity

6. **Total Cost**
   ```
   Base Cost = Click Cost + Material Cost + Setup Cost + Finishing Cost
   After Discount = Base Cost - Discount Amount
   After Margin = After Discount + Margin Amount
   Final Price = After Margin + VAT
   ```

---

## 🆚 V1 vs V2 Comparison

| Feature | V1 (Legacy) | V2 (New) |
|---------|-------------|----------|
| **Input Format** | Legacy product array | Clean structured objects |
| **Digital Calculation** | Generic formula | Accurate click cost calculation |
| **Sheet Optimization** | Basic | Advanced with rotation |
| **Cost Breakdown** | Minimal | Detailed with timing |
| **Machine Types** | All mixed together | Dedicated calculators |
| **Finishing** | Limited | All types supported |
| **Error Messages** | Generic | Specific and helpful |
| **HTTP Status Codes** | Always 200 (even errors) | Proper codes (400, 404, 422, 500) |
| **Response Structure** | Legacy format | Enhanced with v2_breakdown |
| **Extensibility** | Hard to extend | Easy to add new calculators |

---

## 🔧 Configuration

### Machine Configuration for Digital Printing

To get accurate digital printing calculations, machines should have:

```javascript
{
  type: 'digital' or 'printing',
  width: 330,              // Machine sheet width (mm)
  height: 487,             // Machine sheet height (mm)
  gripper_edge: 10,        // Non-printable gripper area (mm)
  margin: 5,               // Margin between products (mm)

  // Click cost (optional - calculated if not provided)
  click_cost: 200,         // Cost per sheet in cents (€2.00)
  // OR
  base_click_cost: 2,      // Base cost for 5% coverage, A4 size (cents)

  // Setup
  setup_cost: 500,         // Setup cost in cents (€5.00)
  // OR
  setup_time: 5,           // Setup time in minutes
  hourly_rate: 5000,       // Hourly rate in cents (€50/hour)

  // Speed
  sph: 3000,               // Sheets per hour

  // Waste
  spoilage: 3,             // Waste percentage (%)
  setup_waste: 10          // Setup waste sheets
}
```

---

## 📊 Example Calculations

### Example 1: Business Cards (500 pcs)

**Input**:
- Format: 85×55mm
- Material: 300gsm coated paper
- Colors: 4/4 (CMYK both sides)
- Quantity: 500

**Calculation**:
- Machine: HP Indigo (330×487mm sheet)
- Products per sheet: 18 (3 across × 6 down)
- Sheets required: 28
- Sheets with waste: 31 (3% waste + 10 setup)
- Click cost: €62.00 (€2.00 per sheet × 31 sheets)
- Material cost: €24.80
- Setup cost: €5.00
- **Total**: €91.80 (before margin)

### Example 2: Flyers with Lamination (1000 pcs)

**Input**:
- Format: 148×210mm (A5)
- Material: 170gsm coated paper
- Colors: 4/0 (CMYK front only)
- Lamination: Gloss front
- Quantity: 1000

**Printing Calculation**:
- Products per sheet: 2
- Sheets required: 500
- Sheets with waste: 525
- Click cost: €105.00 (front only)
- Material cost: €52.50
- Setup cost: €5.00
- **Subtotal**: €162.50

**Lamination Calculation**:
- Area: 1000 × 0.031 sqm = 31 sqm
- Film cost: €24.80 (€0.80/sqm)
- Runtime cost: €9.30
- Setup cost: €10.00
- **Subtotal**: €44.10

**Total**: €206.60 (before margin)

---

## 🐛 Error Handling

V2 uses proper HTTP status codes and detailed error messages.

### Error Response Format
```json
{
  "error": {
    "message": "Valid quantity is required",
    "code": "VALIDATION_ERROR",
    "status": 400,
    "field": "quantity"
  }
}
```

### Common Errors

| Status | Code | Cause |
|--------|------|-------|
| 400 | VALIDATION_ERROR | Missing or invalid input |
| 404 | NOT_FOUND | Category or machine not found |
| 422 | CALCULATION_ERROR | Calculation failed (e.g., product too large) |
| 500 | INTERNAL_ERROR | Server error |
| 503 | EXTERNAL_SERVICE_ERROR | External service (e.g., margin service) unavailable |

---

## 🧪 Testing

### Test with cURL

```bash
# Basic digital printing calculation
curl -X POST http://localhost:3333/v2/calculate \
  -H "Content-Type: application/json" \
  -d '{
    "slug": "business-cards",
    "supplier_id": "tenant_123",
    "quantity": 500,
    "format": {
      "width": 85,
      "height": 55,
      "bleed": 3
    },
    "material": {
      "type": "paper_coated",
      "gsm": 300,
      "price": 4500
    },
    "colors": {
      "front": 4,
      "back": 4
    },
    "internal": false
  }'

# Price list
curl -X POST http://localhost:3333/v2/calculate/price-list \
  -H "Content-Type: application/json" \
  -d '{
    "slug": "flyers",
    "supplier_id": "tenant_123",
    "quantities": [100, 250, 500, 1000, 2500],
    "format": {"width": 148, "height": 210},
    "material": {"gsm": 170, "price": 3000},
    "colors": {"front": 4, "back": 0}
  }'

# Get machine types
curl http://localhost:3333/v2/calculate/machine-types
```

---

## 🚀 Migration from V1

### Gradual Migration Strategy

1. **Phase 1**: Test V2 alongside V1
   - V1 continues serving production
   - V2 used for testing
   - Compare results

2. **Phase 2**: Migrate specific categories
   - Start with new products
   - Migrate one category at a time
   - Keep V1 as fallback

3. **Phase 3**: Full migration
   - All new requests use V2
   - V1 maintained for legacy clients
   - Eventually deprecate V1

### Converting V1 Request to V2

**V1 Format**:
```json
{
  "product": [
    {"key_id": "format_box", "value_id": "format_85x55"},
    {"key_id": "material_box", "value_id": "material_300gsm"}
  ],
  "quantity": 500
}
```

**V2 Format**:
```json
{
  "slug": "business-cards",
  "supplier_id": "tenant_123",
  "quantity": 500,
  "format": {"width": 85, "height": 55},
  "material": {"gsm": 300},
  "colors": {"front": 4, "back": 4}
}
```

Much cleaner and more intuitive!

---

## 📈 Performance

V2 is designed for performance:

- Parallel machine calculations
- Optimized database queries (via repositories)
- Minimal external service calls
- Efficient sheet optimization algorithms

**Expected Response Times**:
- Single machine: <200ms
- Multiple machines: <500ms
- Price list (5 quantities): <1s

---

## 🔮 Future Enhancements

Planned for future V2 releases:

1. **Offset Printing Calculator** (coming soon)
   - Plate costs
   - Make-ready time
   - Economic run lengths

2. **Large Format Calculator**
   - Roll-fed calculations
   - Banner/signage specific costs

3. **Binding Calculator**
   - Perfect binding
   - Saddle stitch
   - Wire-O

4. **Visual Layout Generator**
   - SVG sheet layout preview
   - Product positioning visualization

5. **Optimization Engine**
   - Suggest optimal quantity
   - Recommend best machine
   - Cost-saving suggestions

---

## 💡 Best Practices

1. **Always provide bleed** for print products (default: 3mm)
2. **Use accurate GSM** for material calculations
3. **Specify coverage** for digital printing (default: 15%)
4. **Test with different quantities** to find optimal pricing
5. **Compare multiple machines** to choose best option
6. **Use price-list endpoint** for tiered pricing displays

---

## 📞 Support

### Issues or Questions?

- Check this documentation first
- Review example requests in this file
- Test with cURL before integrating
- Check error messages (they're designed to be helpful!)

### Need Help?

- V2 API is new - feedback welcome!
- Report issues or suggest improvements
- We want V2 to be better than multipress! 🚀

---

**API Version**: 2.0
**Last Updated**: 2025-11-12
**Status**: Ready for Testing
**Compatibility**: Works alongside V1 (no breaking changes)
