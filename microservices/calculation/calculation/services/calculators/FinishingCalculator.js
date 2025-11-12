/**
 * FinishingCalculator
 *
 * Handles finishing operations like:
 * - Die cutting / punching
 * - Folding
 * - Perforating
 * - Scoring
 * - Drilling
 */
class FinishingCalculator {
    constructor(machine, quantity, finishingType, options = {}) {
        this.machine = machine;
        this.quantity = quantity;
        this.finishingType = finishingType; // 'die-cut', 'fold', 'perforate', 'score', 'drill'
        this.options = options;
    }

    /**
     * Calculate finishing cost
     *
     * @returns {Object} Calculation result
     */
    calculate() {
        this._validateInputs();

        let cost, timing;

        switch (this.finishingType) {
            case 'die-cut':
            case 'punching':
                ({ cost, timing } = this._calculateDieCutting());
                break;
            case 'fold':
                ({ cost, timing } = this._calculateFolding());
                break;
            case 'perforate':
                ({ cost, timing } = this._calculatePerforating());
                break;
            case 'score':
                ({ cost, timing } = this._calculateScoring());
                break;
            case 'drill':
                ({ cost, timing } = this._calculateDrilling());
                break;
            default:
                throw new Error(`Unknown finishing type: ${this.finishingType}`);
        }

        return {
            status: 200,
            method: 'finishing',
            finishing_type: this.finishingType,
            machine: {
                id: this.machine._id,
                name: this.machine.name,
                type: this.machine.type
            },
            costs: cost,
            timing: timing,
            quantity: this.quantity
        };
    }

    /**
     * Validate inputs
     *
     * @private
     */
    _validateInputs() {
        if (!this.machine) {
            throw new Error('Machine is required');
        }

        if (!this.quantity || this.quantity <= 0) {
            throw new Error('Valid quantity is required');
        }

        if (!this.finishingType) {
            throw new Error('Finishing type is required');
        }
    }

    /**
     * Calculate die cutting / punching cost
     *
     * @returns {Object} Cost and timing
     * @private
     */
    _calculateDieCutting() {
        // Die cost (one-time or amortized)
        const dieRequired = this.options.custom_shape || false;
        const dieCost = dieRequired ? (this.machine.die_cost || 15000) / 100 : 0; // €150 die

        // Setup cost
        const setupCost = (this.machine.setup_cost || 2000) / 100; // €20

        // Running cost
        const piecesPerHour = this.machine.pieces_per_hour || 5000;
        const hourlyRate = this.machine.hourly_rate || 4000; // €40/hour
        const hours = this.quantity / piecesPerHour;
        const runningCost = (hourlyRate / 100) * hours;

        // Timing
        const setupTimeMinutes = this.machine.setup_time || 15;
        const runtimeMinutes = hours * 60;

        return {
            cost: {
                die_cost: dieCost,
                setup_cost: setupCost,
                running_cost: runningCost,
                subtotal: dieCost + setupCost + runningCost,
                cost_per_piece: (dieCost + setupCost + runningCost) / this.quantity
            },
            timing: {
                setup_time: setupTimeMinutes,
                runtime: runtimeMinutes,
                total_time: setupTimeMinutes + runtimeMinutes
            }
        };
    }

    /**
     * Calculate folding cost
     *
     * @returns {Object} Cost and timing
     * @private
     */
    _calculateFolding() {
        const foldCount = this.options.fold_count || 1;
        const foldType = this.options.fold_type || 'standard'; // 'standard', 'complex'

        // Setup cost varies by fold complexity
        const setupCost = foldType === 'complex'
            ? (this.machine.setup_cost || 3000) / 100
            : (this.machine.setup_cost || 1500) / 100;

        // Running cost
        const piecesPerHour = this.machine.pieces_per_hour || 10000;
        const hourlyRate = this.machine.hourly_rate || 3500; // €35/hour
        const hours = this.quantity / piecesPerHour;
        const runningCost = (hourlyRate / 100) * hours * foldCount;

        // Timing
        const setupTimeMinutes = foldType === 'complex' ? 20 : 10;
        const runtimeMinutes = hours * 60;

        return {
            cost: {
                setup_cost: setupCost,
                running_cost: runningCost,
                subtotal: setupCost + runningCost,
                cost_per_piece: (setupCost + runningCost) / this.quantity
            },
            timing: {
                setup_time: setupTimeMinutes,
                runtime: runtimeMinutes,
                total_time: setupTimeMinutes + runtimeMinutes
            }
        };
    }

    /**
     * Calculate perforating cost
     *
     * @returns {Object} Cost and timing
     * @private
     */
    _calculatePerforating() {
        const setupCost = (this.machine.setup_cost || 1000) / 100;
        const piecesPerHour = this.machine.pieces_per_hour || 8000;
        const hourlyRate = this.machine.hourly_rate || 3000;
        const hours = this.quantity / piecesPerHour;
        const runningCost = (hourlyRate / 100) * hours;

        return {
            cost: {
                setup_cost: setupCost,
                running_cost: runningCost,
                subtotal: setupCost + runningCost,
                cost_per_piece: (setupCost + runningCost) / this.quantity
            },
            timing: {
                setup_time: 10,
                runtime: hours * 60,
                total_time: 10 + hours * 60
            }
        };
    }

    /**
     * Calculate scoring cost
     *
     * @returns {Object} Cost and timing
     * @private
     */
    _calculateScoring() {
        const setupCost = (this.machine.setup_cost || 1200) / 100;
        const piecesPerHour = this.machine.pieces_per_hour || 6000;
        const hourlyRate = this.machine.hourly_rate || 3500;
        const hours = this.quantity / piecesPerHour;
        const runningCost = (hourlyRate / 100) * hours;

        return {
            cost: {
                setup_cost: setupCost,
                running_cost: runningCost,
                subtotal: setupCost + runningCost,
                cost_per_piece: (setupCost + runningCost) / this.quantity
            },
            timing: {
                setup_time: 12,
                runtime: hours * 60,
                total_time: 12 + hours * 60
            }
        };
    }

    /**
     * Calculate drilling cost
     *
     * @returns {Object} Cost and timing
     * @private
     */
    _calculateDrilling() {
        const holeCount = this.options.hole_count || 2;
        const setupCost = (this.machine.setup_cost || 1500) / 100;
        const piecesPerHour = this.machine.pieces_per_hour || 4000;
        const hourlyRate = this.machine.hourly_rate || 3800;
        const hours = this.quantity / piecesPerHour;
        const runningCost = (hourlyRate / 100) * hours * (holeCount / 2); // Adjust for hole count

        return {
            cost: {
                setup_cost: setupCost,
                running_cost: runningCost,
                subtotal: setupCost + runningCost,
                cost_per_piece: (setupCost + runningCost) / this.quantity
            },
            timing: {
                setup_time: 15,
                runtime: hours * 60,
                total_time: 15 + hours * 60
            }
        };
    }
}

module.exports = FinishingCalculator;
