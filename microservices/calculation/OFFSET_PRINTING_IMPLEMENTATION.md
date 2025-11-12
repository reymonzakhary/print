# Offset Printing Implementation Plan

## Overview
This document outlines the implementation plan for adding offset printing calculation support to the existing calculation microservice, along with an extensible architecture for future printing methods.

---

## 1. Enhanced Machine Model for Offset Printing

### Database Schema Updates for Machines Service

```javascript
// microservices/machines/Models/Machine.js

const machineSchema = new mongoose.Schema({
    // Existing fields...
    _id: ObjectId,
    tenant_id: String,
    tenant_name: String,
    name: String,
    description: String,
    type: String,  // "printing", "finishing", etc.

    // NEW: Printing Method Configuration
    printing_method: {
        type: String,
        enum: ['digital', 'offset', 'large_format', 'flexo', 'gravure'],
        required: true,
        default: 'digital'
    },

    // NEW: Method-Specific Configuration
    method_config: {
        // Digital Configuration
        digital: {
            click_charge: Number,              // Cost per impression
            variable_data_capable: Boolean,
            color_cost_model: String,          // "click", "toner", "ink"
            toner_costs: {
                cyan: Number,
                magenta: Number,
                yellow: Number,
                black: Number,
                white: Number,
                spot_colors: [{
                    name: String,
                    cost: Number
                }]
            }
        },

        // Offset Configuration
        offset: {
            // Plate Configuration
            plate_config: {
                required: Boolean,             // true for offset
                plate_cost_per_color: Number,  // €15.00 per plate
                plate_size: {
                    width: Number,
                    height: Number
                },
                plate_setup_time: Number,      // Minutes per plate
                max_plates: Number,            // Max plates machine can handle

                // Plate Types
                plate_types: [{
                    type: String,              // "aluminum", "polyester", "paper"
                    cost: Number,
                    setup_time: Number,
                    max_impressions: Number
                }],

                // Plate Making Process
                plate_making: {
                    ctp_available: Boolean,    // Computer-to-Plate
                    manual_time: Number,       // Minutes for manual plate making
                    auto_time: Number          // Minutes for CTP
                }
            },

            // Ink Configuration
            ink_config: {
                ink_cost_model: String,        // "per_kg", "per_color_pass"
                ink_coverage: {
                    light: Number,             // 5% coverage cost
                    medium: Number,            // 50% coverage cost
                    heavy: Number,             // 100% coverage cost
                    default: Number            // Average coverage cost
                },

                // Pantone/Spot Colors
                spot_colors_supported: Boolean,
                spot_color_setup_time: Number, // Minutes per spot color
                spot_color_cost: Number,       // Additional cost per spot

                // Standard Colors
                standard_colors: {
                    cyan: { cost: Number, coverage_per_kg: Number },
                    magenta: { cost: Number, coverage_per_kg: Number },
                    yellow: { cost: Number, coverage_per_kg: Number },
                    black: { cost: Number, coverage_per_kg: Number }
                }
            },

            // Press Configuration
            press_config: {
                press_type: String,            // "sheet-fed", "web", "heatset", "coldset"
                max_colors_per_pass: Number,   // 4, 6, 8, etc.
                perfecting: Boolean,           // Can print both sides in one pass
                inline_finishing: Boolean,     // Inline coating, folding, etc.

                // Make-Ready
                makeready_time_base: Number,   // Base make-ready minutes
                makeready_time_per_color: Number, // Additional minutes per color
                color_change_time: Number,     // Minutes to change color

                // Registration
                registration_tolerance: Number, // mm
                color_registration_time: Number // Minutes for color registration
            },

            // Economic Run Lengths
            economic_runs: {
                minimum_viable_quantity: Number, // e.g., 500 sheets
                optimal_quantity_start: Number,   // e.g., 2000 sheets
                break_even_vs_digital: Number     // Quantity where offset becomes cheaper
            }
        },

        // Large Format Configuration
        large_format: {
            media_types: [String],             // "banner", "vinyl", "canvas", etc.
            max_width_roll: Number,
            grommets_per_meter: Number,
            hemming_cost: Number
        }
    },

    // Existing fields with enhancements...
    unit: String,
    width: Number,
    height: Number,

    // Enhanced speed configuration
    speed_config: {
        spm: Number,                           // Sheets per minute
        mpm: Number,                           // Meters per minute

        // NEW: Speed by print method
        speed_by_method: {
            simple_jobs: Number,               // SPM for simple jobs
            complex_jobs: Number,              // SPM for complex jobs
            first_color: Number,               // SPM for first color pass
            additional_colors: Number          // SPM for each additional color
        },

        // NEW: Speed by paper weight
        speed_by_weight: [{
            weight_min: Number,
            weight_max: Number,
            speed_factor: Number               // Multiplier (0.8 = 80% speed)
        }],

        // NEW: Speed by paper type
        speed_by_type: [{
            type: String,                      // "coated", "uncoated", "textured"
            speed_factor: Number
        }]
    },

    // Enhanced timing
    timing: {
        setup_time: Number,                    // Base setup time
        setup_time_per_color: Number,          // Additional per color
        cooling_time: Number,
        cooling_time_per: Number,

        // NEW: Offset-specific timing
        makeready_time: Number,                // Make-ready for offset
        plate_mounting_time: Number,           // Time to mount plates
        color_adjustment_time: Number,         // Time to adjust colors
        cleanup_time: Number,                  // Post-job cleanup

        // NEW: Changeover times
        changeover: {
            paper_change: Number,              // Minutes
            ink_change: Number,                // Minutes
            plate_change: Number               // Minutes
        }
    },

    // Enhanced cost structure
    cost_structure: {
        machine_hour_rate: Number,             // €100/hour
        operator_hour_rate: Number,            // €25/hour
        overhead_percentage: Number,           // 30%

        // NEW: Cost by method
        setup_costs: {
            digital_setup: Number,
            offset_setup: Number,
            plate_cost_per_plate: Number,
            ink_setup_cost: Number
        }
    },

    // Enhanced quality/waste
    waste_config: {
        spoilage: Number,                      // Base spoilage sheets
        spoilage_percentage: Number,           // Percentage waste
        wf: Number,                            // Waste factor

        // NEW: Method-specific waste
        spoilage_by_method: {
            digital: Number,                   // 50 sheets
            offset: Number,                    // 200 sheets
            offset_per_color: Number           // +50 per additional color
        },

        // NEW: Paper type waste factors
        spoilage_by_paper: [{
            paper_type: String,
            additional_spoilage: Number
        }]
    },

    // Existing fields...
    trim_area: Number,
    margin_right: Number,
    margin_left: Number,
    margin_top: Number,
    margin_bottom: Number,

    // NEW: Enhanced print area configuration
    print_area_config: {
        printable_width: Number,               // Actual printable width
        printable_height: Number,              // Actual printable height
        grip_edge: Number,                     // Gripper edge margin
        tail_edge: Number,                     // Tail edge margin

        // NEW: Bleed and trim specifications
        bleed: {
            default_bleed: Number,             // 3mm standard
            minimum_bleed: Number,             // 2mm minimum
            maximum_bleed: Number              // 5mm maximum
        },

        // NEW: Registration marks
        registration_marks: {
            required: Boolean,
            space_required: Number,            // mm needed for reg marks
            position: String                   // "outside", "inside"
        }
    },

    // Material constraints (enhanced)
    material_constraints: {
        min_gsm: Number,
        max_gsm: Number,

        // NEW: Material type compatibility
        compatible_materials: [{
            material_type: String,             // "coated", "uncoated", "synthetic"
            min_gsm: Number,
            max_gsm: Number,
            speed_factor: Number,
            additional_cost: Number
        }],

        // NEW: Special handling requirements
        special_requirements: [{
            requirement: String,               // "double_hit", "flood_coat"
            time_impact: Number,
            cost_impact: Number
        }]
    },

    // NEW: Color capabilities
    color_capabilities: {
        max_colors: Number,                    // Maximum colors per pass
        process_colors: {
            cmyk: Boolean,
            cmyk_plus: Boolean,                // CMYK + spot
            hex_chrome: Boolean,               // 6-color
            extended_gamut: Boolean            // 7-color CMYKOGV
        },

        spot_colors: {
            available: Boolean,
            max_spots: Number,
            pantone_matching: Boolean,
            metallic_inks: Boolean,
            fluorescent_inks: Boolean,
            white_ink: Boolean
        },

        // Color configurations pricing
        color_pricing: [{
            color_count: Number,               // 1, 2, 4, 6, etc.
            setup_cost: Number,
            run_cost_factor: Number            // Multiplier for run cost
        }]
    },

    // NEW: Finishing capabilities
    finishing_capabilities: {
        inline_coating: Boolean,
        inline_varnish: Boolean,
        inline_lamination: Boolean,
        perforation: Boolean,
        scoring: Boolean,
        numbering: Boolean,

        finishing_costs: [{
            finishing_type: String,
            setup_cost: Number,
            run_cost: Number,
            time_per_unit: Number
        }]
    },

    // NEW: Quality control
    quality_control: {
        color_management: Boolean,
        spectrophotometer: Boolean,
        inline_inspection: Boolean,
        inspection_time: Number,               // Minutes per job
        waste_from_inspection: Number          // Additional sheets
    },

    created_at: Date,
    updated_at: Date
});

module.exports = mongoose.model('Machine', machineSchema);
```

---

## 2. Calculation Engine Architecture

### Core Calculation Service Structure

```javascript
// microservices/calculations/src/CalculationEngine/index.js

class CalculationEngine {
    constructor() {
        this.calculators = new Map();
        this.registerCalculators();
    }

    registerCalculators() {
        this.calculators.set('digital', require('./Calculators/DigitalCalculator'));
        this.calculators.set('offset', require('./Calculators/OffsetCalculator'));
        this.calculators.set('large_format', require('./Calculators/LargeFormatCalculator'));
    }

    async calculate(request) {
        // Validate request
        const validation = this.validateRequest(request);
        if (!validation.valid) {
            throw new ValidationError(validation.errors);
        }

        // Determine calculation method
        const method = await this.determineMethod(request);

        // Get appropriate calculator
        const Calculator = this.calculators.get(method);
        if (!Calculator) {
            throw new Error(`Calculator not found for method: ${method}`);
        }

        // Initialize calculator
        const calculator = new Calculator({
            machineService: this.machineService,
            catalogueService: this.catalogueService,
            optionsService: this.optionsService
        });

        // Perform calculation
        const result = await calculator.calculate(request);

        // Add universal calculations
        result.positioning = await this.calculatePositioning(request, result);
        result.duration = await this.calculateDuration(request, result);
        result.pricing = await this.calculatePricing(request, result);

        // Add metadata
        result.metadata = {
            calculation_id: this.generateCalculationId(),
            timestamp: new Date(),
            method: method,
            version: '2.0.0'
        };

        return result;
    }

    async determineMethod(request) {
        // If method explicitly provided
        if (request.method) {
            return request.method;
        }

        // Auto-detect based on quantity and configuration
        const quantity = request.quantity;

        // Rules-based method selection
        if (quantity < 100) {
            return 'digital';
        } else if (quantity > 1000) {
            return 'offset';
        }

        // Cost comparison
        const costs = await Promise.all([
            this.estimateCost('digital', request),
            this.estimateCost('offset', request)
        ]);

        return costs[0] < costs[1] ? 'digital' : 'offset';
    }

    validateRequest(request) {
        const errors = [];

        if (!request.quantity || request.quantity < 1) {
            errors.push('Quantity must be greater than 0');
        }

        if (!request.product || !Array.isArray(request.product)) {
            errors.push('Product array is required');
        }

        // Validate product items
        request.product?.forEach((item, index) => {
            if (!item.key_id || !item.value_id) {
                errors.push(`Product item ${index} missing required IDs`);
            }
        });

        return {
            valid: errors.length === 0,
            errors
        };
    }
}

module.exports = CalculationEngine;
```

---

## 3. Offset Calculator Implementation

```javascript
// microservices/calculations/src/CalculationEngine/Calculators/OffsetCalculator.js

const BaseCalculator = require('./BaseCalculator');

class OffsetCalculator extends BaseCalculator {
    constructor(services) {
        super(services);
        this.method = 'offset';
    }

    async calculate(request) {
        const {
            quantity,
            product,
            divided,
            options = {}
        } = request;

        // Fetch necessary data
        const machine = await this.selectMachine(request);
        const materials = await this.fetchMaterials(product);
        const colors = this.extractColors(product);

        // Calculate plate requirements
        const plateInfo = this.calculatePlates(colors, machine);

        // Calculate material usage
        const materialUsage = this.calculateMaterialUsage(
            quantity,
            product,
            machine,
            materials
        );

        // Calculate production time
        const productionTime = this.calculateProductionTime(
            quantity,
            materialUsage,
            machine,
            plateInfo,
            colors
        );

        // Calculate costs
        const costs = this.calculateCosts(
            materialUsage,
            productionTime,
            plateInfo,
            machine,
            colors
        );

        return {
            method: this.method,
            machine,
            materials,
            plateInfo,
            materialUsage,
            productionTime,
            costs,
            colors
        };
    }

    /**
     * Calculate plate requirements for offset printing
     */
    calculatePlates(colors, machine) {
        const offsetConfig = machine.method_config.offset;
        const plateConfig = offsetConfig.plate_config;

        // Determine number of plates needed
        const colorConfig = this.parseColorConfig(colors);
        const platesNeeded = colorConfig.front + colorConfig.back;

        // Check if machine can handle plates
        if (platesNeeded > plateConfig.max_plates) {
            throw new Error(
                `Job requires ${platesNeeded} plates but machine supports max ${plateConfig.max_plates}`
            );
        }

        // Calculate plate costs
        const plateCost = platesNeeded * plateConfig.plate_cost_per_color;

        // Calculate plate setup time
        const plateSetupTime = platesNeeded * plateConfig.plate_setup_time;

        // Plate making time
        const plateMakingTime = plateConfig.plate_making.ctp_available
            ? platesNeeded * plateConfig.plate_making.auto_time
            : platesNeeded * plateConfig.plate_making.manual_time;

        return {
            plates_needed: platesNeeded,
            front_plates: colorConfig.front,
            back_plates: colorConfig.back,
            plate_cost_total: plateCost,
            plate_cost_per_plate: plateConfig.plate_cost_per_color,
            plate_setup_time: plateSetupTime,
            plate_making_time: plateMakingTime,
            total_plate_time: plateSetupTime + plateMakingTime,
            plate_type: plateConfig.plate_types[0]?.type || 'aluminum',
            ctp_used: plateConfig.plate_making.ctp_available
        };
    }

    /**
     * Parse color configuration from product
     * Examples: "4/4", "4/0", "4/1", "1/0", "1/1", "5/5"
     */
    parseColorConfig(colors) {
        // Find printing-colors option
        const colorOption = colors.find(c =>
            c.key === 'printing-colors' ||
            c.box?.calc_ref === 'printing-colors'
        );

        if (!colorOption) {
            return { front: 4, back: 4, total: 8 }; // Default to 4/4
        }

        // Parse format "4/4", "4/0", etc.
        const colorValue = colorOption.value || '';
        const match = colorValue.match(/(\d+)[-\/](\d+)/);

        if (match) {
            const front = parseInt(match[1]);
            const back = parseInt(match[2]);
            return {
                front,
                back,
                total: front + back,
                description: `${front}/${back}`
            };
        }

        return { front: 4, back: 4, total: 8 };
    }

    /**
     * Calculate material usage for offset printing
     */
    calculateMaterialUsage(quantity, product, machine, materials) {
        const offsetConfig = machine.method_config.offset;

        // Base sheets needed
        const sheetsNeeded = quantity; // Will be adjusted by positioning

        // Offset spoilage (higher than digital)
        const colorConfig = this.parseColorConfig(product);
        const baseSpoilage = machine.waste_config.spoilage_by_method.offset || 200;
        const colorSpoilage = (colorConfig.total - 4) *
            (machine.waste_config.spoilage_by_method.offset_per_color || 50);

        const totalSpoilage = baseSpoilage + colorSpoilage;

        // Make-ready sheets
        const makeReadySheets = this.calculateMakeReadySheets(machine, colorConfig);

        // Total sheets
        const totalSheets = sheetsNeeded + totalSpoilage + makeReadySheets;

        // Waste percentage
        const wastePercentage = ((totalSpoilage + makeReadySheets) / sheetsNeeded) * 100;

        return {
            sheets_needed: sheetsNeeded,
            spoilage_sheets: totalSpoilage,
            makeready_sheets: makeReadySheets,
            total_sheets: totalSheets,
            waste_percentage: wastePercentage.toFixed(2),
            material_details: materials
        };
    }

    /**
     * Calculate make-ready sheets for offset
     */
    calculateMakeReadySheets(machine, colorConfig) {
        const offsetConfig = machine.method_config.offset;
        const baseSheets = 100; // Base make-ready sheets

        // Additional sheets per color over 4
        const extraColors = Math.max(0, colorConfig.total - 4);
        const additionalSheets = extraColors * 25;

        return baseSheets + additionalSheets;
    }

    /**
     * Calculate production time for offset
     */
    calculateProductionTime(quantity, materialUsage, machine, plateInfo, colors) {
        const offsetConfig = machine.method_config.offset;
        const pressConfig = offsetConfig.press_config;

        // Make-ready time
        const makeReadyTime = pressConfig.makeready_time_base +
            (colors.total * pressConfig.makeready_time_per_color);

        // Plate mounting time
        const plateMountingTime = plateInfo.total_plate_time;

        // Color registration time
        const registrationTime = pressConfig.color_registration_time || 15;

        // Setup time
        const setupTime = machine.timing.setup_time + makeReadyTime;

        // Print time (including make-ready sheets)
        const totalSheets = materialUsage.total_sheets;
        const effectiveSpeed = this.calculateEffectiveSpeed(machine, colors);
        const printTime = totalSheets / effectiveSpeed;

        // Cooling/drying time
        const coolingTime = machine.timing.cooling_time;

        // Cleanup time
        const cleanupTime = machine.timing.cleanup_time || 10;

        // Total time
        const totalMinutes =
            plateMountingTime +
            setupTime +
            registrationTime +
            printTime +
            coolingTime +
            cleanupTime;

        return {
            plate_making_time: plateInfo.plate_making_time,
            plate_mounting_time: plateMountingTime,
            makeready_time: makeReadyTime,
            registration_time: registrationTime,
            setup_time: setupTime,
            print_time: printTime,
            cooling_time: coolingTime,
            cleanup_time: cleanupTime,
            total_minutes: totalMinutes,
            total_hours: (totalMinutes / 60).toFixed(2),
            total_days: Math.ceil(totalMinutes / (8 * 60)),

            // Speed metrics
            effective_spm: effectiveSpeed,
            sheets_per_hour: effectiveSpeed * 60,

            // Breakdown
            breakdown: {
                pre_press: plateMountingTime + setupTime,
                press: printTime,
                post_press: coolingTime + cleanupTime
            }
        };
    }

    /**
     * Calculate effective printing speed
     */
    calculateEffectiveSpeed(machine, colorConfig) {
        let baseSPM = machine.speed_config.spm;

        // Adjust for number of colors
        if (colorConfig.total > 4) {
            const speedReduction = (colorConfig.total - 4) * 0.05; // 5% slower per color
            baseSPM = baseSPM * (1 - speedReduction);
        }

        // Adjust for paper weight
        // (would need paper weight from request)

        return baseSPM;
    }

    /**
     * Calculate all costs for offset printing
     */
    calculateCosts(materialUsage, productionTime, plateInfo, machine, colorConfig) {
        const offsetConfig = machine.method_config.offset;

        // Plate costs
        const plateCosts = plateInfo.plate_cost_total;

        // Setup costs
        const setupCosts = machine.cost_structure.setup_costs.offset_setup || 0;

        // Machine time cost
        const machineHourRate = machine.cost_structure.machine_hour_rate || 100;
        const machineTimeCost = (productionTime.total_hours * machineHourRate);

        // Operator cost
        const operatorHourRate = machine.cost_structure.operator_hour_rate || 25;
        const operatorCost = (productionTime.total_hours * operatorHourRate);

        // Material cost (would come from catalogue)
        const materialCost = 0; // Calculate from materialUsage

        // Ink costs
        const inkCosts = this.calculateInkCosts(
            materialUsage,
            colorConfig,
            offsetConfig.ink_config
        );

        // Subtotal
        const subtotal =
            plateCosts +
            setupCosts +
            machineTimeCost +
            operatorCost +
            materialCost +
            inkCosts;

        // Overhead
        const overheadPercentage = machine.cost_structure.overhead_percentage || 0.30;
        const overhead = subtotal * overheadPercentage;

        // Total
        const total = subtotal + overhead;

        return {
            plate_costs: plateCosts,
            setup_costs: setupCosts,
            machine_time_cost: machineTimeCost,
            operator_cost: operatorCost,
            material_cost: materialCost,
            ink_costs: inkCosts,
            subtotal: subtotal,
            overhead: overhead,
            overhead_percentage: (overheadPercentage * 100).toFixed(1) + '%',
            total: total,

            // Per unit
            cost_per_unit: (total / materialUsage.sheets_needed).toFixed(2),

            // Breakdown
            cost_breakdown: {
                plates: ((plateCosts / total) * 100).toFixed(1) + '%',
                setup: ((setupCosts / total) * 100).toFixed(1) + '%',
                production: (((machineTimeCost + operatorCost) / total) * 100).toFixed(1) + '%',
                materials: ((materialCost / total) * 100).toFixed(1) + '%',
                ink: ((inkCosts / total) * 100).toFixed(1) + '%',
                overhead: ((overhead / total) * 100).toFixed(1) + '%'
            }
        };
    }

    /**
     * Calculate ink costs
     */
    calculateInkCosts(materialUsage, colorConfig, inkConfig) {
        // Simplified ink cost calculation
        // In reality, this would be much more complex based on coverage, ink type, etc.

        const sheetsTotal = materialUsage.total_sheets;
        const inkCostPerSheet = 0.02; // Example: €0.02 per sheet base

        // Adjust for number of colors
        const colorMultiplier = colorConfig.total / 4; // Normalized to 4-color

        const totalInkCost = sheetsTotal * inkCostPerSheet * colorMultiplier;

        return totalInkCost;
    }
}

module.exports = OffsetCalculator;
```

---

## 4. Position Calculation Module

```javascript
// microservices/calculations/src/CalculationEngine/Positioning/PositionCalculator.js

class PositionCalculator {
    constructor(machine, format, product) {
        this.machine = machine;
        this.format = format;
        this.product = product;
    }

    /**
     * Calculate optimal positioning
     */
    calculate() {
        // Get product dimensions
        const productDims = this.getProductDimensions();

        // Get sheet dimensions
        const sheetDims = this.getSheetDimensions();

        // Calculate layouts
        const layouts = this.calculateLayouts(productDims, sheetDims);

        // Select optimal layout
        const optimal = this.selectOptimalLayout(layouts);

        // Generate position details
        const positions = this.generatePositions(optimal, productDims, sheetDims);

        return {
            products_per_sheet: optimal.products_per_sheet,
            layout_pattern: optimal.pattern,
            orientation: optimal.orientation,
            rotation: optimal.rotation,
            sheet_utilization: optimal.efficiency,
            waste_area: optimal.waste_area,
            positions: positions,
            layout_preview: this.generatePreview(optimal, positions, sheetDims)
        };
    }

    getProductDimensions() {
        // Extract from product configuration
        const format = this.product.find(p => p.key === 'format');
        const bleed = this.machine.print_area_config?.bleed?.default_bleed || 3;

        // Example: A4 = 210x297mm
        let width = 210;
        let height = 297;

        // Parse format value
        if (format?.value) {
            const dims = this.getFormatDimensions(format.value);
            width = dims.width;
            height = dims.height;
        }

        return {
            width: width + (bleed * 2),
            height: height + (bleed * 2),
            bleed: bleed
        };
    }

    getSheetDimensions() {
        return {
            width: this.machine.width,
            height: this.machine.height,
            printable_width: this.machine.width -
                this.machine.margin_left -
                this.machine.margin_right,
            printable_height: this.machine.height -
                this.machine.margin_top -
                this.machine.margin_bottom
        };
    }

    calculateLayouts(productDims, sheetDims) {
        const layouts = [];
        const trimArea = this.machine.trim_area || 5;

        // Try different orientations
        const orientations = [
            { width: productDims.width, height: productDims.height, rotation: 0, name: 'portrait' },
            { width: productDims.height, height: productDims.width, rotation: 90, name: 'landscape' }
        ];

        for (const orientation of orientations) {
            const cols = Math.floor(
                sheetDims.printable_width / (orientation.width + trimArea)
            );
            const rows = Math.floor(
                sheetDims.printable_height / (orientation.height + trimArea)
            );

            const productsPerSheet = cols * rows;

            if (productsPerSheet > 0) {
                const usedArea = productsPerSheet * orientation.width * orientation.height;
                const totalArea = sheetDims.printable_width * sheetDims.printable_height;
                const efficiency = (usedArea / totalArea) * 100;

                layouts.push({
                    columns: cols,
                    rows: rows,
                    products_per_sheet: productsPerSheet,
                    orientation: orientation.name,
                    rotation: orientation.rotation,
                    efficiency: efficiency,
                    waste_area: totalArea - usedArea,
                    pattern: `${cols}x${rows}`
                });
            }
        }

        return layouts;
    }

    selectOptimalLayout(layouts) {
        if (layouts.length === 0) {
            throw new Error('No valid layouts found');
        }

        // Sort by products per sheet (descending), then efficiency (descending)
        layouts.sort((a, b) => {
            if (b.products_per_sheet !== a.products_per_sheet) {
                return b.products_per_sheet - a.products_per_sheet;
            }
            return b.efficiency - a.efficiency;
        });

        return layouts[0];
    }

    generatePositions(layout, productDims, sheetDims) {
        const positions = [];
        const trimArea = this.machine.trim_area || 5;

        const startX = this.machine.margin_left;
        const startY = this.machine.margin_top;

        const productWidth = layout.rotation === 90 ? productDims.height : productDims.width;
        const productHeight = layout.rotation === 90 ? productDims.width : productDims.height;

        for (let row = 0; row < layout.rows; row++) {
            for (let col = 0; col < layout.columns; col++) {
                positions.push({
                    row: row,
                    column: col,
                    x: startX + (col * (productWidth + trimArea)),
                    y: startY + (row * (productHeight + trimArea)),
                    width: productWidth,
                    height: productHeight,
                    rotation: layout.rotation
                });
            }
        }

        return positions;
    }

    generatePreview(layout, positions, sheetDims) {
        return {
            sheet_width: sheetDims.width,
            sheet_height: sheetDims.height,
            layout: layout.pattern,
            products_per_sheet: layout.products_per_sheet,
            positions: positions,
            svg: this.generateSVG(positions, sheetDims)
        };
    }

    generateSVG(positions, sheetDims) {
        // Generate SVG representation
        let svg = `<svg width="${sheetDims.width}" height="${sheetDims.height}" xmlns="http://www.w3.org/2000/svg">`;

        // Sheet outline
        svg += `<rect x="0" y="0" width="${sheetDims.width}" height="${sheetDims.height}" fill="white" stroke="black" stroke-width="2"/>`;

        // Product positions
        for (const pos of positions) {
            svg += `<rect x="${pos.x}" y="${pos.y}" width="${pos.width}" height="${pos.height}" fill="lightblue" stroke="blue" stroke-width="1" opacity="0.7"/>`;
            svg += `<text x="${pos.x + pos.width/2}" y="${pos.y + pos.height/2}" text-anchor="middle" font-size="12">${pos.row * positions.length + pos.column + 1}</text>`;
        }

        svg += '</svg>';

        return svg;
    }

    getFormatDimensions(format) {
        const formats = {
            'a4': { width: 210, height: 297 },
            'a3': { width: 297, height: 420 },
            'a5': { width: 148, height: 210 },
            // ... more formats
        };

        return formats[format] || formats['a4'];
    }
}

module.exports = PositionCalculator;
```

---

## Implementation continues in next message...

Would you like me to continue with:
1. Duration Calculator
2. API Endpoint Updates
3. Database Migration Scripts
4. Testing Strategy
5. Or focus on a specific component?
