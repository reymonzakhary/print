/**
 * DigitalPrintingCalculator
 *
 * Specialized calculator for digital printing machines.
 * Provides accurate calculations based on machine-specific configurations.
 *
 * This calculator handles:
 * - Sheet-fed digital presses
 * - Digital web presses
 * - Large format digital printers
 * - Variable data printing
 * - Color management
 * - Substrate compatibility
 * - Speed and quality trade-offs
 */
class DigitalPrintingCalculator {
    constructor(machine, format, quantity, materials, options = {}) {
        this.machine = machine;
        this.format = format;
        this.quantity = quantity;
        this.materials = materials;
        this.options = options;
    }

    /**
     * Calculate digital printing cost
     *
     * @returns {Object} Calculation result
     */
    calculate() {
        // Validate inputs
        this._validateInputs();

        // Calculate sheet requirements
        const sheetInfo = this._calculateSheetRequirements();

        // Calculate click/impression cost
        const clickCost = this._calculateClickCost(sheetInfo);

        // Calculate material cost
        const materialCost = this._calculateMaterialCost(sheetInfo);

        // Calculate setup cost
        const setupCost = this._calculateSetupCost();

        // Calculate finishing cost (if any)
        const finishingCost = this._calculateFinishingCost(sheetInfo);

        // Calculate total time
        const timing = this._calculateTiming(sheetInfo);

        // Build result
        return {
            status: 200,
            method: 'digital',
            machine: {
                id: this.machine._id,
                name: this.machine.name,
                type: this.machine.type
            },
            sheets: {
                required: sheetInfo.sheets_required,
                with_waste: sheetInfo.sheets_with_waste,
                waste_percentage: sheetInfo.waste_percentage,
                products_per_sheet: sheetInfo.products_per_sheet,
                sheet_size: sheetInfo.sheet_size
            },
            costs: {
                click_cost: clickCost.total,
                click_cost_breakdown: clickCost.breakdown,
                material_cost: materialCost.total,
                material_cost_breakdown: materialCost.breakdown,
                setup_cost: setupCost,
                finishing_cost: finishingCost,
                subtotal: clickCost.total + materialCost.total + setupCost + finishingCost,
                cost_per_piece: (clickCost.total + materialCost.total + setupCost + finishingCost) / this.quantity
            },
            timing: timing,
            configuration: this._getConfiguration()
        };
    }

    /**
     * Validate calculation inputs
     *
     * @throws {Error} If validation fails
     * @private
     */
    _validateInputs() {
        if (!this.machine) {
            throw new Error('Machine is required for digital printing calculation');
        }

        if (!this.format) {
            throw new Error('Format is required for digital printing calculation');
        }

        if (!this.quantity || this.quantity <= 0) {
            throw new Error('Valid quantity is required');
        }

        // Check machine is digital type
        if (this.machine.type !== 'printing' && this.machine.type !== 'digital') {
            throw new Error('Machine must be digital printing type');
        }

        // Check format dimensions
        if (!this.format.width || !this.format.height) {
            throw new Error('Format must have width and height');
        }
    }

    /**
     * Calculate sheet requirements
     *
     * @returns {Object} Sheet calculation details
     * @private
     */
    _calculateSheetRequirements() {
        // Machine sheet size
        const machineWidth = this.machine.width || 330; // mm
        const machineHeight = this.machine.height || 487; // mm (SRA3)

        // Product size (including bleed if specified)
        const productWidth = this.format.width + (this.format.bleed || 3) * 2;
        const productHeight = this.format.height + (this.format.bleed || 3) * 2;

        // Calculate products per sheet (considering gripper edge and margins)
        const gripperEdge = this.machine.gripper_edge || 10; // mm
        const margin = this.machine.margin || 5; // mm between products

        const usableWidth = machineWidth - gripperEdge - margin;
        const usableHeight = machineHeight - gripperEdge - margin;

        // Calculate fit in both orientations
        const portraitFit = {
            across: Math.floor(usableWidth / (productWidth + margin)),
            down: Math.floor(usableHeight / (productHeight + margin))
        };
        const portraitTotal = portraitFit.across * portraitFit.down;

        const landscapeFit = {
            across: Math.floor(usableWidth / (productHeight + margin)),
            down: Math.floor(usableHeight / (productWidth + margin))
        };
        const landscapeTotal = landscapeFit.across * landscapeFit.down;

        // Choose best orientation
        const productsPerSheet = Math.max(portraitTotal, landscapeTotal);
        const bestOrientation = portraitTotal >= landscapeTotal ? 'portrait' : 'landscape';

        if (productsPerSheet === 0) {
            throw new Error('Product size is too large for machine sheet size');
        }

        // Calculate sheets required
        const sheetsRequired = Math.ceil(this.quantity / productsPerSheet);

        // Add waste (spoilage)
        const wastePercentage = this.machine.spoilage || 3; // 3% default for digital
        const setupWaste = this.machine.setup_waste || 10; // Setup waste sheets

        const sheetsWithWaste = Math.ceil(sheetsRequired * (1 + wastePercentage / 100)) + setupWaste;

        return {
            sheets_required: sheetsRequired,
            sheets_with_waste: sheetsWithWaste,
            waste_percentage: wastePercentage,
            setup_waste: setupWaste,
            products_per_sheet: productsPerSheet,
            orientation: bestOrientation,
            sheet_size: {
                width: machineWidth,
                height: machineHeight
            },
            product_size: {
                width: productWidth,
                height: productHeight
            }
        };
    }

    /**
     * Calculate click cost (impression cost)
     *
     * Digital presses charge per impression/click
     *
     * @param {Object} sheetInfo - Sheet calculation info
     * @returns {Object} Click cost breakdown
     * @private
     */
    _calculateClickCost(sheetInfo) {
        // Get click cost from machine or calculate based on coverage
        let clickCostPerSheet = 0;

        // Color configuration
        const colors = this.options.colors || { front: 4, back: 0 }; // Default CMYK front only

        // Digital presses have click costs that vary by:
        // 1. Number of colors
        // 2. Coverage percentage
        // 3. Sheet size

        if (this.machine.click_cost) {
            // Machine has predefined click cost
            clickCostPerSheet = this.machine.click_cost / 100; // Convert from cents
        } else {
            // Calculate based on toner/ink usage
            const coverage = this.options.coverage || 15; // 15% default coverage

            // Base click cost (for 5% coverage, A4 size)
            const baseClickCost = this.machine.base_click_cost || 2; // €0.02 per side

            // Adjust for coverage
            const coverageMultiplier = coverage / 5;

            // Adjust for size (larger sheets cost more)
            const sizeMultiplier = (sheetInfo.sheet_size.width * sheetInfo.sheet_size.height) / (210 * 297); // Relative to A4

            // Calculate front cost
            const frontColors = colors.front || 4;
            const frontCost = frontColors > 0 ? baseClickCost * coverageMultiplier * sizeMultiplier : 0;

            // Calculate back cost
            const backColors = colors.back || 0;
            const backCost = backColors > 0 ? baseClickCost * coverageMultiplier * sizeMultiplier : 0;

            clickCostPerSheet = frontCost + backCost;
        }

        const totalClickCost = clickCostPerSheet * sheetInfo.sheets_with_waste;

        return {
            total: totalClickCost,
            per_sheet: clickCostPerSheet,
            breakdown: {
                sheets: sheetInfo.sheets_with_waste,
                click_cost_per_sheet: clickCostPerSheet,
                colors: {
                    front: colors.front || 0,
                    back: colors.back || 0
                },
                coverage: this.options.coverage || 15
            }
        };
    }

    /**
     * Calculate material cost
     *
     * @param {Object} sheetInfo - Sheet calculation info
     * @returns {Object} Material cost breakdown
     * @private
     */
    _calculateMaterialCost(sheetInfo) {
        if (!this.materials || this.materials.length === 0) {
            return { total: 0, breakdown: {} };
        }

        // Get material (paper/substrate)
        const material = this.materials[0]; // Primary material

        // Calculate sheet area in square meters
        const sheetAreaSqm = (sheetInfo.sheet_size.width * sheetInfo.sheet_size.height) / 1000000;

        // Material price (per sheet or per kg)
        let materialCostPerSheet = 0;

        if (material.calc_type === 'sheet') {
            // Price per sheet
            materialCostPerSheet = material.price / 100; // Convert from cents
        } else if (material.calc_type === 'kg' || material.calc_type === 'sqm') {
            // Calculate weight or area
            const gsm = material.grs || 300; // Grams per square meter
            const sheetWeightKg = (sheetAreaSqm * gsm) / 1000;
            const pricePerKg = material.price / 100; // Convert from cents

            materialCostPerSheet = sheetWeightKg * pricePerKg;
        }

        const totalMaterialCost = materialCostPerSheet * sheetInfo.sheets_with_waste;

        return {
            total: totalMaterialCost,
            per_sheet: materialCostPerSheet,
            breakdown: {
                sheets: sheetInfo.sheets_with_waste,
                material_type: material.material || 'Unknown',
                gsm: material.grs || 0,
                sheet_area_sqm: sheetAreaSqm,
                calc_type: material.calc_type
            }
        };
    }

    /**
     * Calculate setup cost
     *
     * Digital presses have minimal setup but still some preparation
     *
     * @returns {number} Setup cost
     * @private
     */
    _calculateSetupCost() {
        // Setup cost from machine
        let setupCost = 0;

        if (this.machine.setup_cost) {
            setupCost = this.machine.setup_cost / 100; // Convert from cents
        } else if (this.machine.setup_time) {
            // Calculate based on setup time and hourly rate
            const hourlyRate = this.machine.hourly_rate || 5000; // €50/hour default
            const setupTimeMinutes = this.machine.setup_time || 5;
            setupCost = (hourlyRate / 100) * (setupTimeMinutes / 60);
        } else {
            // Default minimal setup for digital
            setupCost = 5; // €5 default
        }

        return setupCost;
    }

    /**
     * Calculate finishing cost
     *
     * @param {Object} sheetInfo - Sheet calculation info
     * @returns {number} Finishing cost
     * @private
     */
    _calculateFinishingCost(sheetInfo) {
        if (!this.options.finishing || this.options.finishing.length === 0) {
            return 0;
        }

        let totalFinishingCost = 0;

        for (const finishing of this.options.finishing) {
            if (finishing.type === 'cutting') {
                // Cutting cost
                const cutsRequired = this.quantity; // One cut per piece
                const costPerCut = finishing.cost_per_cut || 0.01; // €0.01 per cut
                totalFinishingCost += cutsRequired * costPerCut;
            } else if (finishing.type === 'lamination') {
                // Lamination cost
                const sheetsToLaminate = sheetInfo.sheets_with_waste;
                const costPerSheet = finishing.cost_per_sheet || 0.50; // €0.50 per sheet
                totalFinishingCost += sheetsToLaminate * costPerSheet;
            }
            // Add more finishing types as needed
        }

        return totalFinishingCost;
    }

    /**
     * Calculate production timing
     *
     * @param {Object} sheetInfo - Sheet calculation info
     * @returns {Object} Timing breakdown
     * @private
     */
    _calculateTiming(sheetInfo) {
        // Setup time
        const setupTimeMinutes = this.machine.setup_time || 5;

        // Print speed (sheets per hour)
        const sheetsPerHour = this.machine.sph || 3000; // 3000 sheets/hour default for digital
        const printTimeMinutes = (sheetInfo.sheets_with_waste / sheetsPerHour) * 60;

        // Drying/cooling time (minimal for digital)
        const coolingTimeMinutes = this.machine.cooling_time || 0;

        // Total time
        const totalMinutes = setupTimeMinutes + printTimeMinutes + coolingTimeMinutes;

        return {
            setup_time: setupTimeMinutes,
            print_time: printTimeMinutes,
            cooling_time: coolingTimeMinutes,
            total_time: totalMinutes,
            estimated_delivery_days: Math.ceil(totalMinutes / (8 * 60)) // 8-hour workday
        };
    }

    /**
     * Get configuration used for calculation
     *
     * @returns {Object} Configuration
     * @private
     */
    _getConfiguration() {
        return {
            quantity: this.quantity,
            format: {
                width: this.format.width,
                height: this.format.height,
                bleed: this.format.bleed || 3
            },
            colors: this.options.colors || { front: 4, back: 0 },
            coverage: this.options.coverage || 15,
            finishing: this.options.finishing || []
        };
    }
}

module.exports = DigitalPrintingCalculator;
