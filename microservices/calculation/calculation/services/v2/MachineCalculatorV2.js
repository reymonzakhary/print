const PrintMachine = require('../../Calculations/Machines/PrintMachine');
const LaminateMachine = require('../../Calculations/Machines/LaminateMachine');
const FetchColor = require('../../Calculations/FetchColor');

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
     * @returns {Promise<Object>} Grouped machine results
     */
    async calculate(machines, format, catalogue, items, category, quantity) {
        try {
            console.log('  → V2 Machine Calculator - Starting');
            console.log('    Format:', { width: format.width, height: format.height });
            console.log('    Material:', catalogue.material?.value);
            console.log('    Weight:', catalogue.weight?.value);
            console.log('    Machines:', machines.length);

            const results = {
                printing: [],
                lamination: [],
                finishing: []
            };

            // Extract color from items
            const colorItem = items.find(i =>
                i.box_calc_ref === 'printing_colors' ||
                i.box_calc_ref === 'printing-colors' ||
                i.key === 'printing-colors'
            );

            console.log('    Color item:', colorItem?.value);

            // Calculate for each machine
            for (const machine of machines) {
                try {
                    const machineType = machine.type || 'printing';

                    if (machineType === 'printing') {
                        const result = await this._calculatePrintMachine(
                            machine,
                            format,
                            catalogue,
                            colorItem,
                            category,
                            quantity
                        );

                        if (result) {
                            results.printing.push(result);
                        }
                    } else if (machineType === 'lamination') {
                        const result = await this._calculateLaminateMachine(
                            machine,
                            format,
                            catalogue,
                            items,
                            category,
                            quantity
                        );

                        if (result) {
                            results.lamination.push(result);
                        }
                    }
                } catch (error) {
                    console.warn(`    ⚠ Machine ${machine.name} failed:`, error.message);
                    // Continue with other machines
                }
            }

            console.log(`  ✓ V2 Machine calculations complete:`, {
                printing: results.printing.length,
                lamination: results.lamination.length,
                finishing: results.finishing.length
            });

            return results;
        } catch (error) {
            console.error('  ❌ V2 Machine Calculator error:', error.message);
            throw error;
        }
    }

    /**
     * Calculate for a print machine
     *
     * @private
     */
    async _calculatePrintMachine(machine, format, catalogue, colorItem, category, quantity) {
        try {
            const materialResults = catalogue.results || [];

            // Find matching catalogue entry
            const catalogueEntry = materialResults[0]; // Use first matching catalogue

            if (!catalogueEntry) {
                return null;
            }

            // Fetch color pricing
            const fetchColor = new FetchColor(
                colorItem,
                machine._id,
                category.tenant_id
            );
            const colors = await fetchColor.get();

            if (!colors || colors.length === 0) {
                console.warn(`      No colors found for machine ${machine.name}`);
                return null;
            }

            // Run PrintMachine calculation
            const printMachine = new PrintMachine(
                machine,
                format.format || format, // Pass format object
                catalogue.material,
                catalogue.weight,
                catalogueEntry,
                quantity,
                colors[0],
                format.bleed || 0
            );

            const calculation = printMachine.calculate();

            if (!calculation || !calculation.calculation) {
                return null;
            }

            return {
                type: 'printing',
                machine: machine,
                color: colors[0],
                results: calculation,
                calculation: calculation.calculation
            };
        } catch (error) {
            console.warn(`      Print machine error:`, error.message);
            return null;
        }
    }

    /**
     * Calculate for a lamination machine
     *
     * @private
     */
    async _calculateLaminateMachine(machine, format, catalogue, items, category, quantity) {
        try {
            // Check if lamination is requested
            const laminationItem = items.find(i =>
                i.box_calc_ref === 'lamination' ||
                i.key === 'lamination' ||
                i.key === 'afwerking'
            );

            if (!laminationItem) {
                return null;
            }

            const laminateMachine = new LaminateMachine(
                machine,
                format.format || format,
                quantity,
                laminationItem
            );

            const calculation = laminateMachine.calculate();

            if (!calculation || !calculation.calculation) {
                return null;
            }

            return {
                type: 'lamination',
                machine: machine,
                results: calculation,
                calculation: calculation.calculation
            };
        } catch (error) {
            console.warn(`      Lamination machine error:`, error.message);
            return null;
        }
    }
}

module.exports = MachineCalculatorV2;
