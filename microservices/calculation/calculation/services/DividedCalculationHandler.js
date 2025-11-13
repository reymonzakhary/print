const { filterByCalcRef, groupByDividerWithCalcRefCopy } = require('../Helpers/Helper');
const MachineCalculationService = require('./MachineCalculationService');
const PriceCalculationService = require('./PriceCalculationService');
const DurationCalculator = require('./DurationCalculator');
const MachineCalculatorV2 = require('./v2/MachineCalculatorV2');

/**
 * DividedCalculationHandler
 *
 * Handles divided calculations (cover + content, etc.)
 * Separates items by divider and calculates each division independently.
 */
class DividedCalculationHandler {
    constructor(useV2 = false) {
        this.useV2 = useV2;
        this.machineService = new MachineCalculationService();
        this.machineCalculatorV2 = new MachineCalculatorV2();
        this.priceService = new PriceCalculationService();
        this.durationCalculator = new DurationCalculator();
    }

    /**
     * Check if calculation should be divided
     *
     * @param {Array} items - Product items
     * @param {Object} boops - Boops configuration
     * @returns {boolean} True if should be divided
     */
    shouldDivide(items, boops) {
        // ALWAYS check if items have multiple dividers first
        // This is the primary indicator of divided calculations
        const dividers = new Set();
        for (const item of items) {
            if (item.divider && item.divider !== 'default' && item.divider !== '') {
                dividers.add(item.divider);
            }
        }

        // If items have multiple dividers (e.g., cover + content), always divide
        if (dividers.size > 1) {
            console.log(`✓ Divided calculation detected: ${Array.from(dividers).join(', ')}`);
            return true;
        }

        // Fallback: Check if boops has divided flag
        if (boops?.divided === true) {
            console.log('✓ Divided calculation enabled via boops configuration');
            return true;
        }

        console.log('✓ Simple calculation (no division needed)');
        return false;
    }

    /**
     * Group items by divider
     *
     * @param {Array} items - Product items
     * @returns {Object} Grouped items by divider
     */
    groupByDivider(items) {
        const grouped = {};

        for (const item of items) {
            const divider = item.divider || 'default';

            if (!grouped[divider]) {
                grouped[divider] = [];
            }

            grouped[divider].push(item);
        }

        return grouped;
    }

    /**
     * Calculate divided sections
     *
     * @param {Object} context - Calculation context
     * @param {Object} formatResult - Format calculation result
     * @param {Object} catalogue - Catalogue results (may be overridden per division)
     * @returns {Promise<Object>} Divided calculation results
     */
    async calculateDivided(context, formatResult, catalogue) {
        console.log('🔀 Divided Calculation - Starting');

        // Group items by divider using helper function
        const grouped = groupByDividerWithCalcRefCopy(
            context.matchedItems,
            ['format', 'printing-colors', 'printing_colors']
        );

        console.log('Dividers found:', Object.keys(grouped));

        const divisions = [];

        for (const [dividerName, dividerData] of Object.entries(grouped)) {
            if (!dividerData.calculateable) {
                console.warn(`Divider '${dividerName}' not calculateable:`, dividerData.missings);
                continue;
            }

            console.log(`Calculating divider: ${dividerName}`);

            const divisionItems = dividerData.data;

            // Fetch catalogue specific to this division's material/weight
            const divisionCatalogue = await this._fetchDivisionCatalogue(
                divisionItems,
                context.supplierId
            );

            // Calculate this division with its own catalogue
            const divisionResult = await this._calculateDivision(
                dividerName,
                divisionItems,
                context,
                formatResult,
                divisionCatalogue || catalogue // Fallback to main catalogue if fetch fails
            );

            divisions.push(divisionResult);
        }

        // Combine all divisions
        const combined = this._combineDivisions(divisions);

        console.log('✓ Divided Calculation - Complete');

        return {
            divided: true,
            divisions: divisions,
            combined: combined
        };
    }

    /**
     * Fetch catalogue specific to a division's material and weight
     *
     * @param {Array} items - Division items
     * @param {string} supplierId - Supplier ID
     * @returns {Promise<Object|null>} Catalogue results or null
     * @private
     */
    async _fetchDivisionCatalogue(items, supplierId) {
        try {
            // Extract material and weight from this division's items
            const materialItem = items.find(i => i.box_calc_ref === 'material');
            const weightItem = items.find(i => i.box_calc_ref === 'weight');

            if (!materialItem || !weightItem) {
                console.log('  → Using shared catalogue (no material/weight in division)');
                return null;
            }

            const FetchCatalogue = require('../Calculations/Catalogues/FetchCatalogue');

            console.log(`  → Fetching catalogue: ${materialItem.value} @ ${weightItem.value}`);

            const catalogue = await new FetchCatalogue(
                materialItem,
                weightItem,
                supplierId
            ).get();

            if (catalogue && catalogue.results && catalogue.results.length > 0) {
                console.log(`  ✓ Division catalogue fetched: ${catalogue.results.length} materials`);

                // Add material and weight items to catalogue object
                // (required by MachineCalculationService)
                catalogue.material = materialItem;
                catalogue.weight = weightItem;

                return catalogue;
            }

            return null;
        } catch (error) {
            console.warn(`  ⚠ Failed to fetch division catalogue:`, error.message);
            return null;
        }
    }

    /**
     * Calculate a single division
     *
     * @param {string} dividerName - Name of divider (cover, content, etc.)
     * @param {Array} items - Items for this division
     * @param {Object} context - Calculation context
     * @param {Object} formatResult - Format calculation result
     * @param {Object} catalogue - Catalogue results
     * @returns {Promise<Object>} Division calculation result
     * @private
     */
    async _calculateDivision(dividerName, items, context, formatResult, catalogue) {
        // Calculate format specific to this division
        // Each division may have different pages/sides/specifications
        const FormatService = require('./FormatService');
        const formatService = new FormatService();

        const bleed = context.request?.bleed || context.category.bleed;
        const divisionFormatResult = await formatService.calculate(
            context.category,
            items,
            context.quantity,
            bleed,
            context.request
        );

        if (divisionFormatResult.status !== 200) {
            console.warn(`  ⚠ Format calculation failed for ${dividerName}: ${divisionFormatResult.message}`);
            console.warn(`  → Using shared format as fallback`);
            // Use shared format as fallback
            divisionFormatResult.width = formatResult.width;
            divisionFormatResult.height = formatResult.height;
            divisionFormatResult.format = formatResult.format;
        } else {
            console.log(`  ✓ Division format: ${divisionFormatResult.width}x${divisionFormatResult.height}mm`);
        }

        // Extract binding and other special options
        const bindingMethod = filterByCalcRef(items, 'binding_method');
        const bindingDirection = filterByCalcRef(items, 'binding_direction');
        const endpapers = filterByCalcRef(items, 'endpapers');

        // Run machine calculations for this division with its own format
        let combinations;
        if (this.useV2) {
            // Use V2 pure implementation
            console.log('  → Using V2 Machine Calculator');
            const machineGroups = await this.machineCalculatorV2.calculate(
                context.machines,
                divisionFormatResult,
                catalogue,
                items,
                context.category,
                context.quantity
            );

            // Convert V2 groups to combinations format
            const { combinations: combinationHelper } = require('../Helpers/Helper');
            combinations = combinationHelper(machineGroups);
        } else {
            // Use V1 legacy implementation
            combinations = await this.machineService.runCombinations(
                context.machines,
                divisionFormatResult,
                catalogue,
                items,
                context.request,
                context.category,
                {}, // content config
                bindingMethod,
                bindingDirection,
                endpapers
            );
        }

        // Calculate prices for this division
        const priceResult = this.priceService.calculateDividedPrice(
            combinations,
            items,
            formatResult,
            context.category
        );

        // Calculate duration
        let duration = null;
        if (priceResult.calculation) {
            duration = this.durationCalculator.calculateDuration(
                { machine: priceResult.machine, results: { calculation: priceResult.calculation } },
                formatResult,
                context.quantity
            );

            // Add lamination timing if lamination machine present
            if (priceResult.laminate_machine && priceResult.calculation) {
                const laminationDuration = this.durationCalculator.calculateLaminationDuration(
                    { machine: priceResult.laminate_machine },
                    parseFloat(priceResult.calculation.amount_of_sheets_printed) || 0
                );

                // Add lamination time to total duration
                duration.lamination_time = laminationDuration.lamination_time;
                duration.total_time += laminationDuration.total_time;
                duration.total_hours = Math.round((duration.total_time / 60) * 10) / 10;
                duration.estimated_delivery_days = Math.ceil(duration.total_time / 480);
            }
        }

        // Extract costs breakdown
        const printingCost = parseFloat(priceResult.calculation?.total_sheet_price) || 0;

        // Calculate lamination cost if laminate machine present
        let laminationCost = 0;
        if (priceResult.laminate_machine) {
            // Try to extract from options_cost or calculate based on row_price difference
            if (priceResult.options_cost?.breakdown) {
                const laminationOption = priceResult.options_cost.breakdown.find(
                    opt => opt.key === 'lamination' || opt.box_calc_ref === 'lamination'
                );
                if (laminationOption) {
                    laminationCost = laminationOption.total_cost || 0;
                }
            }
        }

        // Extract options cost (excluding lamination if already counted)
        let optionsCost = 0;
        if (priceResult.options_cost) {
            optionsCost = priceResult.options_cost.total || 0;
            // Subtract lamination if it was included
            if (laminationCost > 0) {
                optionsCost -= laminationCost;
            }
        }

        return {
            name: dividerName.charAt(0).toUpperCase() + dividerName.slice(1),
            divider: dividerName,
            items: items,
            format: divisionFormatResult,
            machine: priceResult.machine,
            laminate_machine: priceResult.laminate_machine || null,
            calculation: priceResult.calculation,
            duration: duration,
            costs: {
                printing: printingCost,
                lamination: laminationCost,
                options: optionsCost,
                subtotal: priceResult.row_price
            },
            sheets: {
                needed: parseFloat(priceResult.calculation?.amount_of_sheets_needed) || 0,
                with_spoilage: parseFloat(priceResult.calculation?.amount_of_sheets_with_spoilage) || 0,
                products_per_sheet: parseFloat(priceResult.calculation?.maximum_prints_per_sheet) || 0
            },
            row_price: priceResult.row_price,
            price: priceResult.price
        };
    }

    /**
     * Combine all divisions into final result
     *
     * @param {Array} divisions - Array of division results
     * @returns {Object} Combined result
     * @private
     */
    _combineDivisions(divisions) {
        let totalRowPrice = 0;
        const durations = [];

        for (const division of divisions) {
            totalRowPrice += division.row_price;
            if (division.duration) {
                durations.push(division.duration);
            }
        }

        // Combine durations
        const combinedDuration = this.durationCalculator.combineDurations(durations);

        return {
            total_row_price: totalRowPrice,
            total_price: Math.round(totalRowPrice * 100), // In cents
            duration: combinedDuration,
            divisions_count: divisions.length
        };
    }

    /**
     * Format divided response
     *
     * @param {Object} dividedResult - Divided calculation result
     * @param {Object} context - Calculation context
     * @param {Array} margins - Margins
     * @param {Object} category - Category
     * @returns {Object} Formatted response
     */
    formatDividedResponse(dividedResult, context, margins, category) {
        const divisions = dividedResult.divisions;
        const combined = dividedResult.combined;

        return {
            type: 'print',
            calculation_type: 'divided_calculation',
            divided: true,
            quantity: context.quantity,

            // Summary
            summary: {
                total_price_ex_vat: combined.total_row_price,
                price_per_piece: combined.total_row_price / context.quantity,
                production_time: combined.duration,
                divisions_count: divisions.length
            },

            // Each division
            divisions: divisions.map(div => ({
                name: div.name,
                divider: div.divider,
                items: this._formatDivisionItems(div.items),
                calculation: {
                    machine: {
                        id: div.machine?._id?.toString(),
                        name: div.machine?.name,
                        type: div.machine?.type
                    },
                    laminate_machine: div.laminate_machine ? {
                        id: div.laminate_machine?._id?.toString(),
                        name: div.laminate_machine?.name,
                        type: div.laminate_machine?.type
                    } : null,
                    sheets: div.sheets,
                    costs: div.costs,
                    timing: div.duration,
                    row_price: div.row_price
                },
                details: div.calculation // Full calculation details
            })),

            // Combined totals
            totals: {
                row_price: combined.total_row_price,
                duration: combined.duration
            },

            // Original fields for compatibility
            items: context.matchedItems,
            product: context.items,
            category: category,
            margins: context.internal && margins.length ? margins[0] : []
        };
    }

    /**
     * Format division items for clean display
     *
     * @param {Array} items - Division items
     * @returns {Array} Formatted items
     * @private
     */
    _formatDivisionItems(items) {
        return items.map(item => ({
            key: item.key,
            value: item.value,
            display: item.option?.display_name?.[0]?.display_name || item.option?.name || item.value,
            calc_ref: item.box_calc_ref
        }));
    }
}

module.exports = DividedCalculationHandler;
