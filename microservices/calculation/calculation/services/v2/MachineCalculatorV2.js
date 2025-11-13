const PrintMachine = require('../../Calculations/Machines/PrintMachine');
const FetchColor = require('../../Calculations/FetchColor');
const { filterByCalcRef } = require('../../Helpers/Helper');

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
            const colorItem = filterByCalcRef(items, 'printing_colors');

            console.log('    Color item:', {
                value: colorItem[0]?.value,
                hasOption: !!colorItem[0]?.option,
                hasBox: !!colorItem[0]?.box,
                optionId: colorItem[0]?.option?._id,
                keys: colorItem[0] ? Object.keys(colorItem[0]) : []
            });

            // Calculate for each machine
            for (const machine of machines) {
                try {
                    const machineType = machine.type || 'printing';

                    if (machineType === 'printing') {
                        const result = await this._calculatePrintMachine(
                            machine,
                            format,
                            catalogue,
                            colorItem[0],
                            category,
                            quantity
                        );

                        if (result) {
                            results.printing.push(result);
                        }
                    } else if (machineType === 'lamination') {
                        const result = await this._calculateLaminationInline(
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
            const catalogueResults = catalogue.results || [];

            if (!catalogueResults.length) {
                console.warn(`      No catalogue results for machine ${machine.name}`);
                return null;
            }

            if (!colorItem || !colorItem.option) {
                console.warn(`      No color option for machine ${machine.name}:`, {
                    hasColorItem: !!colorItem,
                    hasOption: !!colorItem?.option,
                    colorItemKeys: colorItem ? Object.keys(colorItem) : []
                });
                return null;
            }

            // Prepare format object for FetchColor
            const formatForColor = format.format || format;

            console.log(`      Fetching colors for ${machine.name}:`, {
                colorValue: colorItem.value,
                optionId: colorItem.option._id,
                machineId: machine._id,
                formatHasRange: !!formatForColor.range,
                formatHasQuantity: !!formatForColor.quantity,
                formatKeys: formatForColor ? Object.keys(formatForColor).slice(0, 10) : []
            });

            // Fetch color pricing
            // FetchColor expects: (color array, machine object, format object)
            // Format must have: format.range, format.quantity
            const fetchColor = new FetchColor(
                [colorItem],  // color array
                machine,      // machine object
                formatForColor  // format object with range and quantity
            );
            const colorResult = await fetchColor.get();

            console.log(`      FetchColor returned:`, {
                isArray: Array.isArray(colorResult),
                type: typeof colorResult,
                hasStatus: colorResult?.status,
                status: colorResult?.status,
                message: colorResult?.message,
                hasPriceList: !!colorResult?.price_list,
                priceListLength: colorResult?.price_list?.length,
                hasPrice: colorResult?.price !== undefined,
                price: colorResult?.price,
                keys: colorResult ? Object.keys(colorResult) : []
            });

            // FetchColor.get() returns an object, not an array!
            // We need to wrap it in an array for PrintMachine
            const colors = Array.isArray(colorResult) ? colorResult : [colorResult];

            // Validate color result
            if (!colors || colors.length === 0) {
                console.warn(`      No colors returned for machine ${machine.name}`);
                return null;
            }

            if (colors[0]?.status === 422) {
                console.warn(`      Color fetch failed for machine ${machine.name}:`, colors[0]?.message);
                return null;
            }

            // PrintMachine requires price and price_list
            if (!colors[0] || colors[0].price === undefined || !colors[0].price_list) {
                console.warn(`      Color missing required fields for machine ${machine.name}:`, {
                    hasColor: !!colors[0],
                    hasPrice: colors[0]?.price !== undefined,
                    hasPriceList: !!colors[0]?.price_list
                });
                return null;
            }

            // Debug catalogues being passed
            console.log(`      Catalogues for PrintMachine:`, {
                count: catalogueResults.length,
                firstThree: catalogueResults.slice(0, 3).map(c => ({
                    material: c.material,
                    grs: c.grs,
                    width: c.width,
                    height: c.height,
                    sheet: c.sheet
                }))
            });

            // PrintMachine constructor: (machine, catalogues, format, color, content, endpaper, request)
            const printMachine = new PrintMachine(
                machine,
                catalogueResults,
                format.format || format,
                colors[0],
                {}, // content
                {}, // endpaper
                { quantity: quantity, bleed: format.bleed || 0 }
            );

            console.log(`      PrintMachine created:`, {
                hasMachine: !!machine,
                cataloguesCount: catalogueResults.length,
                hasFormat: !!(format.format || format),
                formatKeys: format.format ? Object.keys(format.format) : Object.keys(format),
                hasColor: !!colors[0]
            });

            const calculation = printMachine.calculate();

            console.log(`      PrintMachine.calculate() returned:`, {
                hasCalculation: !!calculation,
                hasCalculationProp: !!calculation?.calculation,
                status: calculation?.status,
                message: calculation?.message,
                selectedMaterial: calculation?.material_used,
                selectedWeight: calculation?.wight_used,
                catalogueWidth: calculation?.catalogue_width,
                catalogueHeight: calculation?.catalogue_height
            });

            if (!calculation || !calculation.calculation) {
                console.warn(`      Print machine ${machine.name} failed: ${calculation?.message || 'No calculation result'}`);
                return null;
            }

            console.log(`      ✓ Print machine ${machine.name} calculated`);

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
     * Calculate lamination inline (like legacy Machines class does)
     *
     * @private
     */
    async _calculateLaminationInline(machine, format, catalogue, items, category, quantity) {
        try {
            // Check if lamination is requested
            const laminationItem = filterByCalcRef(items, 'lamination');

            if (!laminationItem || !laminationItem.length) {
                return null;
            }

            const lamination = laminationItem[0];

            if (!lamination.option || !lamination.option.sheet_runs || !lamination.option.sheet_runs.length) {
                console.warn(`      No lamination sheet_runs for machine ${machine.name}`);
                return null;
            }

            const startCost = parseInt(machine.price) / 100000;
            const areaSqm = quantity * (format.size?.m || 0);

            // Get runs related to this machine
            const runs = lamination.option.sheet_runs?.filter(run =>
                run.machine.toString() === machine._id.toString()
            )[0];

            if (!runs) {
                return null;
            }

            const run = runs.runs.filter((r) =>
                quantity >= parseInt(r.from) && quantity <= parseInt(r.to)
            );

            console.log(`      ✓ Lamination machine ${machine.name} calculated`);

            return {
                type: 'lamination',
                machine: machine,
                results: {
                    machine: machine,
                    format: format.format || format,
                    lamination: lamination,
                    calculation: {
                        calculation_method: lamination.option.calculation_method,
                        area_sqm: areaSqm,
                        start_cost: startCost,
                        option_start_cost: parseInt(lamination.option?.start_cost ?? 0) / 100000,
                        run: run,
                        run_price: Number(parseInt(run ? run[0]?.price ?? 0 : 0) / 100000)
                    }
                }
            };
        } catch (error) {
            console.warn(`      Lamination machine error:`, error.message);
            return null;
        }
    }
}

module.exports = MachineCalculatorV2;
