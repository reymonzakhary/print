/**
 * LaminationCalculator
 *
 * Specialized calculator for lamination machines.
 * Handles glossy, matte, and special effect lamination calculations.
 */
class LaminationCalculator {
    constructor(machine, format, quantity, laminationType, options = {}) {
        this.machine = machine;
        this.format = format;
        this.quantity = quantity;
        this.laminationType = laminationType; // 'gloss', 'matte', 'soft-touch', etc.
        this.options = options;
    }

    /**
     * Calculate lamination cost
     *
     * @returns {Object} Calculation result
     */
    calculate() {
        // Validate inputs
        this._validateInputs();

        // Calculate sheets required
        const sheetInfo = this._calculateSheetRequirements();

        // Calculate film/laminate cost
        const filmCost = this._calculateFilmCost(sheetInfo);

        // Calculate machine runtime cost
        const runtimeCost = this._calculateRuntimeCost(sheetInfo);

        // Calculate setup cost
        const setupCost = this._calculateSetupCost();

        // Calculate timing
        const timing = this._calculateTiming(sheetInfo);

        return {
            status: 200,
            method: 'lamination',
            machine: {
                id: this.machine._id,
                name: this.machine.name,
                type: this.machine.type
            },
            sheets: {
                required: sheetInfo.sheets_required,
                area_sqm: sheetInfo.total_area_sqm
            },
            costs: {
                film_cost: filmCost.total,
                runtime_cost: runtimeCost,
                setup_cost: setupCost,
                subtotal: filmCost.total + runtimeCost + setupCost,
                cost_per_piece: (filmCost.total + runtimeCost + setupCost) / this.quantity
            },
            timing: timing,
            configuration: {
                lamination_type: this.laminationType,
                sides: this.options.sides || 'front' // 'front', 'back', 'both'
            }
        };
    }

    /**
     * Validate inputs
     *
     * @private
     */
    _validateInputs() {
        if (!this.machine || this.machine.type !== 'lamination') {
            throw new Error('Valid lamination machine is required');
        }

        if (!this.format) {
            throw new Error('Format is required');
        }

        if (!this.quantity || this.quantity <= 0) {
            throw new Error('Valid quantity is required');
        }

        if (!this.laminationType) {
            throw new Error('Lamination type is required');
        }
    }

    /**
     * Calculate sheet requirements
     *
     * @returns {Object} Sheet info
     * @private
     */
    _calculateSheetRequirements() {
        const sheetWidth = this.format.width;
        const sheetHeight = this.format.height;
        const sheetAreaSqm = (sheetWidth * sheetHeight) / 1000000;

        const sheetsRequired = this.quantity;

        // Account for both sides if needed
        const sides = this.options.sides || 'front';
        const sidesMultiplier = sides === 'both' ? 2 : 1;

        const totalAreaSqm = sheetAreaSqm * sheetsRequired * sidesMultiplier;

        // Add waste
        const wastePercentage = this.machine.spoilage || 5; // 5% waste for lamination
        const totalWithWaste = totalAreaSqm * (1 + wastePercentage / 100);

        return {
            sheets_required: sheetsRequired,
            sheet_area_sqm: sheetAreaSqm,
            total_area_sqm: totalWithWaste,
            sides: sidesMultiplier,
            waste_percentage: wastePercentage
        };
    }

    /**
     * Calculate film cost
     *
     * @param {Object} sheetInfo - Sheet info
     * @returns {Object} Film cost breakdown
     * @private
     */
    _calculateFilmCost(sheetInfo) {
        // Film cost varies by type
        const filmCosts = {
            gloss: 0.80, // €0.80 per sqm
            matte: 0.85,
            'soft-touch': 1.20,
            'anti-scratch': 1.50,
            holographic: 2.50
        };

        const filmCostPerSqm = filmCosts[this.laminationType] || 0.80;
        const totalFilmCost = sheetInfo.total_area_sqm * filmCostPerSqm;

        return {
            total: totalFilmCost,
            per_sqm: filmCostPerSqm,
            type: this.laminationType
        };
    }

    /**
     * Calculate runtime cost
     *
     * @param {Object} sheetInfo - Sheet info
     * @returns {number} Runtime cost
     * @private
     */
    _calculateRuntimeCost(sheetInfo) {
        // Machine cost per square meter or per hour
        if (this.machine.cost_per_sqm) {
            return (this.machine.cost_per_sqm / 100) * sheetInfo.total_area_sqm;
        }

        // Calculate based on speed and hourly rate
        const sqmPerHour = this.machine.sqm_per_hour || 100;
        const hourlyRate = this.machine.hourly_rate || 3000; // €30/hour
        const hours = sheetInfo.total_area_sqm / sqmPerHour;

        return (hourlyRate / 100) * hours;
    }

    /**
     * Calculate setup cost
     *
     * @returns {number} Setup cost
     * @private
     */
    _calculateSetupCost() {
        return (this.machine.setup_cost || 1000) / 100; // €10 default
    }

    /**
     * Calculate timing
     *
     * @param {Object} sheetInfo - Sheet info
     * @returns {Object} Timing
     * @private
     */
    _calculateTiming(sheetInfo) {
        const setupTimeMinutes = this.machine.setup_time || 10;
        const sqmPerHour = this.machine.sqm_per_hour || 100;
        const runtimeMinutes = (sheetInfo.total_area_sqm / sqmPerHour) * 60;

        return {
            setup_time: setupTimeMinutes,
            runtime: runtimeMinutes,
            total_time: setupTimeMinutes + runtimeMinutes
        };
    }
}

module.exports = LaminationCalculator;
