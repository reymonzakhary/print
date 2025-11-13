const Machine = require('../../Calculations/Machine');

/**
 * MachineCalculatorV2
 *
 * Pure V2 implementation - does NOT use legacy Machines class
 * Calculates machine combinations directly for V2 pipeline
 */
class MachineCalculatorV2 {
    /**
     * Run machine calculations for V2 pipeline
     *
     * @param {Array} machines - Available machines
     * @param {Object} format - Format details with width/height
     * @param {Object} catalogue - Catalogue with material/weight
     * @param {Array} items - Product items
     * @param {Object} category - Category object
     * @param {number} quantity - Quantity
     * @returns {Promise<Array>} Machine calculation results
     */
    async calculate(machines, format, catalogue, items, category, quantity) {
        try {
            console.log('  → V2 Machine Calculator - Starting');
            console.log('    Format:', { width: format.width, height: format.height });
            console.log('    Material:', catalogue.material?.value);
            console.log('    Weight:', catalogue.weight?.value);
            console.log('    Machines:', machines.length);

            const results = [];

            // Calculate for each machine
            for (const machine of machines) {
                try {
                    const result = await this._calculateForMachine(
                        machine,
                        format,
                        catalogue,
                        items,
                        category,
                        quantity
                    );

                    if (result) {
                        results.push(result);
                    }
                } catch (error) {
                    console.warn(`    ⚠ Machine ${machine.name} failed:`, error.message);
                    // Continue with other machines
                }
            }

            console.log(`  ✓ V2 Machine calculations complete: ${results.length} results`);

            // Group by type
            return this._groupByType(results);
        } catch (error) {
            console.error('  ❌ V2 Machine Calculator error:', error.message);
            throw error;
        }
    }

    /**
     * Calculate for a single machine
     *
     * @private
     */
    async _calculateForMachine(machine, format, catalogue, items, category, quantity) {
        // Extract material and weight
        const materialItem = catalogue.material;
        const weightItem = catalogue.weight;
        const materialResults = catalogue.results || [];

        // Find matching catalogue entry for this machine
        const catalogueEntry = materialResults.find(cat => {
            // Check if this catalogue entry is compatible with this machine
            return true; // For now, accept all
        });

        if (!catalogueEntry) {
            return null;
        }

        // Extract printing colors
        const colorItem = items.find(i =>
            i.box_calc_ref === 'printing_colors' ||
            i.box_calc_ref === 'printing-colors' ||
            i.key === 'printing-colors'
        );

        // Run Machine calculation (singular - not Machines)
        const machineCalc = new Machine(
            machine,
            format.width,
            format.height,
            format.bleed || 0,
            quantity,
            materialItem,
            weightItem,
            catalogueEntry,
            colorItem,
            items
        );

        const calculation = await machineCalc.calculate();

        if (!calculation || calculation.status !== 200) {
            return null;
        }

        return {
            type: machine.type || 'printing',
            machine: machine,
            results: calculation,
            calculation: calculation.calculation
        };
    }

    /**
     * Group results by machine type
     *
     * @private
     */
    _groupByType(results) {
        const groups = {
            printing: [],
            lamination: [],
            finishing: []
        };

        for (const result of results) {
            const type = result.type || 'printing';

            if (groups[type]) {
                groups[type].push(result);
            } else {
                groups[type] = [result];
            }
        }

        console.log('    Groups:', {
            printing: groups.printing.length,
            lamination: groups.lamination.length,
            finishing: groups.finishing.length
        });

        return groups;
    }
}

module.exports = MachineCalculatorV2;
