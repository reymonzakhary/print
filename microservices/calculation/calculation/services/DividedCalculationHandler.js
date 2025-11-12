const { filterByCalcRef, groupByDividerWithCalcRefCopy } = require('../Helpers/Helper');
const MachineCalculationService = require('./MachineCalculationService');
const PriceCalculationService = require('./PriceCalculationService');
const DurationCalculator = require('./DurationCalculator');

/**
 * DividedCalculationHandler
 *
 * Handles divided calculations (cover + content, etc.)
 * Separates items by divider and calculates each division independently.
 */
class DividedCalculationHandler {
    constructor() {
        this.machineService = new MachineCalculationService();
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
        // Check if boops has divided flag
        if (boops.divided === true) {
            return true;
        }

        // Check if items have dividers
        const dividers = new Set();
        for (const item of items) {
            if (item.divider) {
                dividers.add(item.divider);
            }
        }

        // If more than one divider, it's divided
        return dividers.size > 1;
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
     * @param {Object} catalogue - Catalogue results
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

            // Calculate this division
            const divisionResult = await this._calculateDivision(
                dividerName,
                divisionItems,
                context,
                formatResult,
                catalogue
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
        // Extract binding and other special options
        const bindingMethod = filterByCalcRef(items, 'binding_method');
        const bindingDirection = filterByCalcRef(items, 'binding_direction');
        const endpapers = filterByCalcRef(items, 'endpapers');

        // Run machine calculations for this division
        const combinations = await this.machineService.runCombinations(
            context.machines,
            formatResult,
            catalogue,
            items,
            context.request,
            context.category,
            {}, // content config
            bindingMethod,
            bindingDirection,
            endpapers
        );

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
        }

        return {
            name: dividerName.charAt(0).toUpperCase() + dividerName.slice(1),
            divider: dividerName,
            items: items,
            machine: priceResult.machine,
            laminate_machine: priceResult.laminate_machine || null,
            calculation: priceResult.calculation,
            duration: duration,
            costs: {
                printing: parseFloat(priceResult.calculation?.total_sheet_price) || 0,
                lamination: 0, // TODO: Extract from lamination result
                options: 0, // TODO: Extract from options result
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
