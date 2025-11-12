/**
 * DurationCalculator
 *
 * Calculates production timing and durations.
 * Fixes the "duration: null" issue.
 */
class DurationCalculator {
    /**
     * Calculate duration for a machine calculation
     *
     * @param {Object} machineResult - Machine calculation result
     * @param {Object} format - Format details
     * @param {number} quantity - Quantity
     * @returns {Object} Duration breakdown
     */
    calculateDuration(machineResult, format, quantity) {
        const machine = machineResult.machine;
        const calculation = machineResult.results?.calculation || {};

        // Setup time (in minutes)
        const setupTime = parseFloat(machine.setup_time) || 0;

        // Print time calculation
        const sheetsNeeded = parseFloat(calculation.amount_of_sheets_needed) || 0;
        const spm = parseFloat(machine.spm) || 1; // Sheets per minute
        const printTime = sheetsNeeded / spm;

        // Cooling time
        const coolingTime = parseFloat(machine.cooling_time) || 0;
        const coolingTimePer = parseFloat(machine.cooling_time_per) || 0;

        // Total cooling
        let totalCoolingTime = coolingTime;
        if (coolingTimePer > 0) {
            totalCoolingTime += (sheetsNeeded / coolingTimePer) * coolingTime;
        }

        // Total time in minutes
        const totalMinutes = setupTime + printTime + totalCoolingTime;

        // Estimate delivery days (8-hour workday = 480 minutes)
        const estimatedDays = Math.ceil(totalMinutes / 480);

        return {
            setup_time: Math.round(setupTime * 10) / 10,
            print_time: Math.round(printTime * 10) / 10,
            cooling_time: Math.round(totalCoolingTime * 10) / 10,
            total_time: Math.round(totalMinutes * 10) / 10,
            total_hours: Math.round((totalMinutes / 60) * 10) / 10,
            estimated_delivery_days: estimatedDays,
            duration_type: 'minutes',
            fed: machine.fed || 'sheet',
            machine_name: machine.name,
            machine_id: machine._id?.toString()
        };
    }

    /**
     * Calculate combined duration for multiple operations
     *
     * @param {Array} durations - Array of duration objects
     * @returns {Object} Combined duration
     */
    combineDurations(durations) {
        let totalSetup = 0;
        let totalPrint = 0;
        let totalCooling = 0;
        let totalFinishing = 0;

        for (const duration of durations) {
            totalSetup += duration.setup_time || 0;
            totalPrint += duration.print_time || 0;
            totalCooling += duration.cooling_time || 0;
            totalFinishing += duration.finishing_time || 0;
        }

        const totalMinutes = totalSetup + totalPrint + totalCooling + totalFinishing;

        return {
            setup_minutes: Math.round(totalSetup * 10) / 10,
            print_minutes: Math.round(totalPrint * 10) / 10,
            cooling_minutes: Math.round(totalCooling * 10) / 10,
            finishing_minutes: Math.round(totalFinishing * 10) / 10,
            total_minutes: Math.round(totalMinutes * 10) / 10,
            total_hours: Math.round((totalMinutes / 60) * 10) / 10,
            estimated_days: Math.ceil(totalMinutes / 480),
            duration_type: 'minutes'
        };
    }

    /**
     * Calculate lamination duration
     *
     * @param {Object} laminationResult - Lamination result
     * @param {number} sheets - Number of sheets
     * @returns {Object} Duration
     */
    calculateLaminationDuration(laminationResult, sheets) {
        const machine = laminationResult.machine;

        const setupTime = parseFloat(machine.setup_time) || 0;
        const spm = parseFloat(machine.spm) || 100;
        const laminationTime = sheets / spm;

        return {
            setup_time: setupTime,
            lamination_time: Math.round(laminationTime * 10) / 10,
            total_time: Math.round((setupTime + laminationTime) * 10) / 10,
            machine_name: machine.name
        };
    }

    /**
     * Calculate finishing duration
     *
     * @param {Array} finishingOptions - Finishing options
     * @param {number} quantity - Quantity
     * @returns {Object} Duration
     */
    calculateFinishingDuration(finishingOptions, quantity) {
        let totalTime = 0;

        for (const option of finishingOptions) {
            // Estimate based on option type
            if (option.type === 'binding') {
                totalTime += quantity * 0.1; // 0.1 minutes per piece
            } else if (option.type === 'folding') {
                totalTime += quantity * 0.05; // 0.05 minutes per piece
            } else if (option.type === 'cutting') {
                totalTime += quantity * 0.02; // 0.02 minutes per piece
            }
        }

        return {
            finishing_time: Math.round(totalTime * 10) / 10,
            total_time: Math.round(totalTime * 10) / 10
        };
    }
}

module.exports = DurationCalculator;
