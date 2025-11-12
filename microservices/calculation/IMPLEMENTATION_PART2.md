# Calculation System Implementation - Part 2

## 5. Duration Calculator Module

```javascript
// microservices/calculations/src/CalculationEngine/Duration/DurationCalculator.js

class DurationCalculator {
    constructor(machine, productionData, positioning) {
        this.machine = machine;
        this.productionData = productionData;
        this.positioning = positioning;
    }

    /**
     * Calculate complete duration breakdown
     */
    calculate(quantity) {
        const sheetsNeeded = Math.ceil(
            quantity / this.positioning.products_per_sheet
        );

        // Get production time from calculator
        const productionTime = this.productionData.productionTime;

        // Calculate dates
        const dates = this.calculateDates(productionTime.total_hours);

        return {
            // Time breakdown
            setup_minutes: productionTime.setup_time,
            print_minutes: productionTime.print_time,
            finishing_minutes: productionTime.cleanup_time,
            drying_minutes: productionTime.cooling_time,
            total_minutes: productionTime.total_minutes,
            total_hours: productionTime.total_hours,
            total_days: productionTime.total_days,

            // Dates
            production_date: dates.production_date,
            delivery_date: dates.delivery_date,
            start_time: dates.start_time,
            end_time: dates.end_time,

            // Scheduling
            shifts_required: this.calculateShiftsRequired(productionTime.total_hours),
            working_days_required: productionTime.total_days,

            // Production metrics
            sheets_per_hour: productionTime.sheets_per_hour,
            estimated_completion_time: dates.end_time,

            // Breakdown by phase
            phases: {
                pre_press: {
                    duration_minutes: productionTime.breakdown?.pre_press || 0,
                    percentage: this.calculatePercentage(
                        productionTime.breakdown?.pre_press || 0,
                        productionTime.total_minutes
                    )
                },
                press: {
                    duration_minutes: productionTime.breakdown?.press || 0,
                    percentage: this.calculatePercentage(
                        productionTime.breakdown?.press || 0,
                        productionTime.total_minutes
                    )
                },
                post_press: {
                    duration_minutes: productionTime.breakdown?.post_press || 0,
                    percentage: this.calculatePercentage(
                        productionTime.breakdown?.post_press || 0,
                        productionTime.total_minutes
                    )
                }
            }
        };
    }

    calculateDates(totalHours) {
        const now = new Date();
        const hoursPerDay = 8; // Working hours per day

        // Calculate production start (next business day)
        const productionDate = this.getNextBusinessDay(now);

        // Calculate completion time
        const workingDays = Math.ceil(totalHours / hoursPerDay);
        const endTime = this.addBusinessDays(productionDate, workingDays);

        // Add buffer for delivery (1 day)
        const deliveryDate = this.addBusinessDays(endTime, 1);

        return {
            production_date: productionDate,
            start_time: productionDate,
            end_time: endTime,
            delivery_date: deliveryDate
        };
    }

    calculateShiftsRequired(totalHours) {
        const hoursPerShift = 8;
        return Math.ceil(totalHours / hoursPerShift);
    }

    calculatePercentage(part, total) {
        return ((part / total) * 100).toFixed(1) + '%';
    }

    getNextBusinessDay(date) {
        const next = new Date(date);
        next.setDate(next.getDate() + 1);

        // Skip weekends
        while (next.getDay() === 0 || next.getDay() === 6) {
            next.setDate(next.getDate() + 1);
        }

        return next;
    }

    addBusinessDays(date, days) {
        const result = new Date(date);
        let addedDays = 0;

        while (addedDays < days) {
            result.setDate(result.getDate() + 1);

            // Skip weekends
            if (result.getDay() !== 0 && result.getDay() !== 6) {
                addedDays++;
            }
        }

        return result;
    }
}

module.exports = DurationCalculator;
```

---

## 6. Updated API Endpoints

```javascript
// microservices/calculations/routes/calculation.routes.js

const express = require('express');
const router = express.Router();
const CalculationController = require('../controllers/CalculationController');
const { validateCalculation } = require('../middleware/validation');

/**
 * V2 Calculation API - Enhanced with positioning and duration
 */
router.post(
    '/api/v2/calculate',
    validateCalculation,
    CalculationController.calculateV2
);

/**
 * Legacy V1 endpoint - maintained for backward compatibility
 */
router.post(
    '/suppliers/:supplier_id/categories/:slug/products/calculate/price',
    CalculationController.calculateV1
);

/**
 * Method-specific endpoints
 */
router.post(
    '/api/v2/calculate/offset',
    validateCalculation,
    CalculationController.calculateOffset
);

router.post(
    '/api/v2/calculate/digital',
    validateCalculation,
    CalculationController.calculateDigital
);

/**
 * Estimate endpoint - quick calculation without full details
 */
router.post(
    '/api/v2/estimate',
    validateCalculation,
    CalculationController.estimatePrice
);

module.exports = router;
```

### Enhanced Controller

```javascript
// microservices/calculations/controllers/CalculationController.js

const CalculationEngine = require('../CalculationEngine');
const PositionCalculator = require('../CalculationEngine/Positioning/PositionCalculator');
const DurationCalculator = require('../CalculationEngine/Duration/DurationCalculator');

class CalculationController {
    constructor() {
        this.engine = new CalculationEngine();
    }

    /**
     * V2 Enhanced Calculation
     */
    async calculateV2(req, res, next) {
        try {
            const request = req.body;

            // Perform calculation
            const result = await this.engine.calculate(request);

            // Format response
            const response = {
                success: true,
                calculation_id: result.metadata.calculation_id,
                timestamp: result.metadata.timestamp,
                version: '2.0.0',

                // Summary
                summary: {
                    method: result.method,
                    total_price: result.pricing.total,
                    total_time_hours: result.duration.total_hours,
                    delivery_date: result.duration.delivery_date,
                    products_per_sheet: result.positioning.products_per_sheet,
                    total_sheets: result.materialUsage.total_sheets
                },

                // Detailed breakdowns
                pricing: result.pricing,
                duration: result.duration,
                positioning: result.positioning,
                materials: result.materials,
                machine: {
                    machine_id: result.machine._id,
                    machine_name: result.machine.name,
                    method: result.machine.printing_method
                },

                // Method-specific details
                method_details: this.formatMethodDetails(result),

                // Warnings
                warnings: result.warnings || []
            };

            res.status(200).json(response);
        } catch (error) {
            next(error);
        }
    }

    /**
     * Offset-specific calculation
     */
    async calculateOffset(req, res, next) {
        try {
            req.body.method = 'offset';
            return await this.calculateV2(req, res, next);
        } catch (error) {
            next(error);
        }
    }

    /**
     * Format method-specific details
     */
    formatMethodDetails(result) {
        if (result.method === 'offset') {
            return {
                plates: result.plateInfo,
                ink: result.costs.ink_costs,
                makeready: {
                    time: result.productionTime.makeready_time,
                    sheets: result.materialUsage.makeready_sheets
                },
                spoilage: {
                    sheets: result.materialUsage.spoilage_sheets,
                    percentage: result.materialUsage.waste_percentage
                }
            };
        }

        return {};
    }

    /**
     * V1 Backward compatibility endpoint
     */
    async calculateV1(req, res, next) {
        try {
            const { supplier_id, slug } = req.params;

            // Convert V1 request to V2 format
            const v2Request = {
                type: 'print',
                quantity: req.body.quantity,
                divided: req.body.divided,
                product: req.body.product,
                supplier_id,
                category_slug: slug
            };

            // Calculate
            const result = await this.engine.calculate(v2Request);

            // Format as V1 response (simplified)
            const v1Response = {
                type: 'print',
                connection: supplier_id,
                external_id: supplier_id,
                calculation_type: 'full_calculation',
                items: req.body.product,
                quantity: req.body.quantity,
                prices: [
                    {
                        qty: req.body.quantity,
                        selling_price_ex: result.pricing.total,
                        selling_price_inc: result.pricing.total * 1.21, // Assuming 21% VAT
                        dlv: { days: result.duration.total_days }
                    }
                ]
            };

            res.status(200).json(v1Response);
        } catch (error) {
            next(error);
        }
    }
}

module.exports = new CalculationController();
```

---

## 7. Database Migration Scripts

```javascript
// microservices/machines/migrations/001_add_offset_config.js

/**
 * Migration: Add offset printing configuration to machines
 */

const mongoose = require('mongoose');

async function up() {
    const Machine = mongoose.model('Machine');

    // Update all existing machines with default offset config
    await Machine.updateMany(
        { printing_method: { $exists: false } },
        {
            $set: {
                printing_method: 'digital',
                method_config: {
                    digital: {
                        click_charge: 0,
                        variable_data_capable: false,
                        color_cost_model: 'click'
                    },
                    offset: {
                        plate_config: {
                            required: false,
                            plate_cost_per_color: 15,
                            plate_size: {
                                width: 1000,
                                height: 700
                            },
                            plate_setup_time: 5,
                            max_plates: 8,
                            plate_types: [
                                {
                                    type: 'aluminum',
                                    cost: 15,
                                    setup_time: 5,
                                    max_impressions: 100000
                                }
                            ],
                            plate_making: {
                                ctp_available: true,
                                manual_time: 20,
                                auto_time: 5
                            }
                        },
                        ink_config: {
                            ink_cost_model: 'per_color_pass',
                            ink_coverage: {
                                light: 0.01,
                                medium: 0.02,
                                heavy: 0.03,
                                default: 0.02
                            },
                            spot_colors_supported: false,
                            spot_color_setup_time: 15,
                            spot_color_cost: 50
                        },
                        press_config: {
                            press_type: 'sheet-fed',
                            max_colors_per_pass: 4,
                            perfecting: false,
                            inline_finishing: false,
                            makeready_time_base: 30,
                            makeready_time_per_color: 10,
                            color_change_time: 15,
                            registration_tolerance: 0.1,
                            color_registration_time: 15
                        },
                        economic_runs: {
                            minimum_viable_quantity: 500,
                            optimal_quantity_start: 2000,
                            break_even_vs_digital: 1500
                        }
                    }
                }
            }
        }
    );

    // Update existing timing fields
    await Machine.updateMany(
        { 'timing.cleanup_time': { $exists: false } },
        {
            $set: {
                'timing.cleanup_time': 10,
                'timing.makeready_time': 30,
                'timing.plate_mounting_time': 0,
                'timing.color_adjustment_time': 15
            }
        }
    );

    // Update existing cost structure
    await Machine.updateMany(
        { 'cost_structure.machine_hour_rate': { $exists: false } },
        {
            $set: {
                'cost_structure.machine_hour_rate': 100,
                'cost_structure.operator_hour_rate': 25,
                'cost_structure.overhead_percentage': 0.30
            }
        }
    );

    console.log('Migration completed: Added offset configuration to machines');
}

async function down() {
    const Machine = mongoose.model('Machine');

    // Remove new fields
    await Machine.updateMany(
        {},
        {
            $unset: {
                printing_method: '',
                method_config: '',
                'timing.cleanup_time': '',
                'timing.makeready_time': '',
                'timing.plate_mounting_time': '',
                'timing.color_adjustment_time': ''
            }
        }
    );

    console.log('Migration rolled back: Removed offset configuration');
}

module.exports = { up, down };
```

### Run migrations

```javascript
// microservices/machines/scripts/migrate.js

const mongoose = require('mongoose');
const migrations = [
    require('../migrations/001_add_offset_config')
];

async function runMigrations() {
    try {
        await mongoose.connect(process.env.MONGO_URI);

        for (const migration of migrations) {
            console.log(`Running migration: ${migration.name}`);
            await migration.up();
        }

        console.log('All migrations completed successfully');
        process.exit(0);
    } catch (error) {
        console.error('Migration failed:', error);
        process.exit(1);
    }
}

runMigrations();
```

---

## 8. Testing Strategy

### Unit Tests

```javascript
// microservices/calculations/tests/unit/OffsetCalculator.test.js

const OffsetCalculator = require('../../src/CalculationEngine/Calculators/OffsetCalculator');

describe('OffsetCalculator', () => {
    let calculator;
    let mockServices;

    beforeEach(() => {
        mockServices = {
            machineService: {
                findById: jest.fn()
            },
            catalogueService: {
                findMaterials: jest.fn()
            },
            optionsService: {
                findOptions: jest.fn()
            }
        };

        calculator = new OffsetCalculator(mockServices);
    });

    describe('parseColorConfig', () => {
        it('should parse 4/4 color configuration', () => {
            const colors = [
                {
                    key: 'printing-colors',
                    value: '44-full-color'
                }
            ];

            const result = calculator.parseColorConfig(colors);

            expect(result).toEqual({
                front: 4,
                back: 4,
                total: 8,
                description: '4/4'
            });
        });

        it('should parse 4/0 color configuration', () => {
            const colors = [
                {
                    key: 'printing-colors',
                    value: '40-full-color'
                }
            ];

            const result = calculator.parseColorConfig(colors);

            expect(result).toEqual({
                front: 4,
                back: 0,
                total: 4,
                description: '4/0'
            });
        });

        it('should default to 4/4 when no color config provided', () => {
            const result = calculator.parseColorConfig([]);

            expect(result.front).toBe(4);
            expect(result.back).toBe(4);
        });
    });

    describe('calculatePlates', () => {
        it('should calculate plate requirements for 4/4', () => {
            const machine = {
                method_config: {
                    offset: {
                        plate_config: {
                            plate_cost_per_color: 15,
                            plate_setup_time: 5,
                            max_plates: 8,
                            plate_making: {
                                ctp_available: true,
                                auto_time: 5,
                                manual_time: 20
                            }
                        }
                    }
                }
            };

            const colors = [{ key: 'printing-colors', value: '44-full-color' }];

            const result = calculator.calculatePlates(colors, machine);

            expect(result.plates_needed).toBe(8);
            expect(result.front_plates).toBe(4);
            expect(result.back_plates).toBe(4);
            expect(result.plate_cost_total).toBe(120); // 8 * 15
            expect(result.plate_setup_time).toBe(40); // 8 * 5
            expect(result.ctp_used).toBe(true);
        });

        it('should throw error if machine cannot handle required plates', () => {
            const machine = {
                method_config: {
                    offset: {
                        plate_config: {
                            max_plates: 4,
                            plate_cost_per_color: 15
                        }
                    }
                }
            };

            const colors = [{ key: 'printing-colors', value: '66-full-color' }]; // 12 plates needed

            expect(() => {
                calculator.calculatePlates(colors, machine);
            }).toThrow('Job requires 12 plates but machine supports max 4');
        });
    });

    describe('calculateMakeReadySheets', () => {
        it('should calculate base make-ready sheets', () => {
            const machine = {};
            const colorConfig = { total: 4 };

            const result = calculator.calculateMakeReadySheets(machine, colorConfig);

            expect(result).toBe(100); // Base sheets for 4 colors
        });

        it('should add sheets for extra colors', () => {
            const machine = {};
            const colorConfig = { total: 6 }; // 2 extra colors

            const result = calculator.calculateMakeReadySheets(machine, colorConfig);

            expect(result).toBe(150); // 100 + (2 * 25)
        });
    });
});
```

### Integration Tests

```javascript
// microservices/calculations/tests/integration/calculation.test.js

const request = require('supertest');
const app = require('../../index');
const mongoose = require('mongoose');

describe('Calculation API - Offset Printing', () => {
    beforeAll(async () => {
        await mongoose.connect(process.env.TEST_MONGO_URI);
        // Seed test data
        await seedTestData();
    });

    afterAll(async () => {
        await mongoose.connection.close();
    });

    describe('POST /api/v2/calculate', () => {
        it('should calculate offset printing for 1000 quantity', async () => {
            const response = await request(app)
                .post('/api/v2/calculate')
                .send({
                    type: 'print',
                    method: 'offset',
                    quantity: 1000,
                    divided: false,
                    product: [
                        {
                            key: 'format',
                            value: 'a4',
                            value_id: 'format-a4-id',
                            key_id: 'format-box-id'
                        },
                        {
                            key: 'printing-colors',
                            value: '44-full-color',
                            value_id: 'color-44-id',
                            key_id: 'color-box-id'
                        }
                    ]
                })
                .expect(200);

            expect(response.body.success).toBe(true);
            expect(response.body.summary.method).toBe('offset');
            expect(response.body.method_details.plates).toBeDefined();
            expect(response.body.method_details.plates.plates_needed).toBe(8);
            expect(response.body.pricing.plate_costs).toBeGreaterThan(0);
            expect(response.body.duration.total_days).toBeGreaterThan(0);
        });

        it('should automatically select offset for large quantities', async () => {
            const response = await request(app)
                .post('/api/v2/calculate')
                .send({
                    type: 'print',
                    quantity: 5000, // Large quantity
                    divided: false,
                    product: [
                        {
                            key: 'format',
                            value: 'a4',
                            value_id: 'format-a4-id',
                            key_id: 'format-box-id'
                        }
                    ]
                })
                .expect(200);

            expect(response.body.summary.method).toBe('offset');
        });

        it('should include positioning information', async () => {
            const response = await request(app)
                .post('/api/v2/calculate')
                .send({
                    type: 'print',
                    method: 'offset',
                    quantity: 1000,
                    product: [/*...*/]
                })
                .expect(200);

            expect(response.body.positioning).toBeDefined();
            expect(response.body.positioning.products_per_sheet).toBeGreaterThan(0);
            expect(response.body.positioning.layout_pattern).toBeDefined();
            expect(response.body.positioning.sheet_utilization).toBeGreaterThan(0);
        });

        it('should validate quantity is positive', async () => {
            const response = await request(app)
                .post('/api/v2/calculate')
                .send({
                    type: 'print',
                    quantity: -100,
                    product: []
                })
                .expect(400);

            expect(response.body.errors).toBeDefined();
        });
    });
});
```

---

## 9. Frontend Integration Guide

### Example API Call

```javascript
// Frontend code example

async function calculatePrice(productConfig) {
    const response = await fetch('http://calculations:3333/api/v2/calculate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            type: 'print',
            method: 'offset', // or 'digital', or omit for auto-selection
            quantity: 1000,
            divided: false,
            product: productConfig
        })
    });

    const result = await response.json();

    return {
        price: result.summary.total_price,
        deliveryDate: result.summary.delivery_date,
        productsPerSheet: result.summary.products_per_sheet,
        duration: result.duration,
        positioning: result.positioning,
        plateInfo: result.method_details?.plates
    };
}
```

### Display Components

```vue
<!-- Vue component example -->
<template>
  <div class="calculation-result">
    <div class="summary">
      <h3>Calculation Summary</h3>
      <p>Method: {{ result.summary.method }}</p>
      <p>Total Price: €{{ result.summary.total_price }}</p>
      <p>Delivery: {{ formatDate(result.summary.delivery_date) }}</p>
      <p>Production Time: {{ result.summary.total_time_hours }} hours</p>
    </div>

    <div v-if="result.method_details.plates" class="plate-info">
      <h4>Offset Plate Information</h4>
      <p>Plates Needed: {{ result.method_details.plates.plates_needed }}</p>
      <p>Plate Cost: €{{ result.method_details.plates.plate_cost_total }}</p>
      <p>Front Plates: {{ result.method_details.plates.front_plates }}</p>
      <p>Back Plates: {{ result.method_details.plates.back_plates }}</p>
    </div>

    <div class="positioning">
      <h4>Sheet Layout</h4>
      <p>Products per Sheet: {{ result.positioning.products_per_sheet }}</p>
      <p>Layout: {{ result.positioning.layout_pattern }}</p>
      <p>Sheet Utilization: {{ result.positioning.sheet_utilization }}%</p>

      <!-- SVG Preview -->
      <div v-html="result.positioning.layout_preview.svg"></div>
    </div>

    <div class="cost-breakdown">
      <h4>Cost Breakdown</h4>
      <ul>
        <li>Plates: €{{ result.pricing.plate_costs }}</li>
        <li>Setup: €{{ result.pricing.setup_costs }}</li>
        <li>Machine Time: €{{ result.pricing.machine_time_cost }}</li>
        <li>Materials: €{{ result.pricing.material_cost }}</li>
        <li>Overhead: €{{ result.pricing.overhead }}</li>
      </ul>
    </div>
  </div>
</template>
```

---

## 10. Next Steps & Deployment

### Phase 1: Core Implementation (Week 1-2)
- [ ] Update Machine model schema
- [ ] Implement OffsetCalculator
- [ ] Implement PositionCalculator
- [ ] Implement DurationCalculator
- [ ] Update API endpoints
- [ ] Write unit tests

### Phase 2: Integration (Week 3-4)
- [ ] Database migrations
- [ ] Integration testing
- [ ] Update existing machines with offset config
- [ ] Frontend updates
- [ ] Documentation

### Phase 3: Testing & Optimization (Week 5-6)
- [ ] End-to-end testing
- [ ] Performance optimization
- [ ] User acceptance testing
- [ ] Bug fixes

### Phase 4: Deployment (Week 7-8)
- [ ] Staging deployment
- [ ] Production deployment
- [ ] Monitoring setup
- [ ] Training & documentation

---

## Documentation Complete

This implementation guide covers:
✅ Enhanced machine models for offset printing
✅ Calculation engine architecture
✅ Offset-specific calculator
✅ Positioning calculation
✅ Duration calculation
✅ API endpoint updates
✅ Database migrations
✅ Testing strategy
✅ Frontend integration

Ready to start implementation!
