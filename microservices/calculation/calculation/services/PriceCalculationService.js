const OptionsCalculationService = require('./OptionsCalculationService');

/**
 * PriceCalculationService
 *
 * Handles final price calculations by combining:
 * - Printing costs
 * - Lamination costs
 * - Finishing costs
 * - Extra options costs
 */
class PriceCalculationService {
    constructor() {
        this.optionsService = new OptionsCalculationService();
    }

    /**
     * Calculate final price for all combinations and find cheapest
     *
     * @param {Array} combinations - Array of machine combinations
     * @param {Array} items - Product items
     * @param {Object} format - Format calculation result
     * @param {Object} category - Category object
     * @param {Array} margins - Margins array
     * @returns {Object} Cheapest option with full calculation
     */
    calculatePrices(combinations, items, format, category, margins = []) {
        try {
            const allPrices = [];

            for (const combination of combinations) {
                const priceResult = this._calculateSingleCombination(
                    combination,
                    items,
                    format,
                    category,
                    margins
                );

                allPrices.push(priceResult);
            }

            // Find cheapest option
            const cheapest = this._findCheapest(allPrices);

            return {
                status: 200,
                cheapest: cheapest,
                all_prices: allPrices
            };
        } catch (error) {
            return {
                status: 422,
                message: error.message,
                cheapest: null,
                all_prices: []
            };
        }
    }

    /**
     * Calculate price for a single machine combination
     *
     * @param {Object} combination - Single combination object
     * @param {Array} items - Product items
     * @param {Object} format - Format calculation result
     * @param {Object} category - Category object
     * @param {Array} margins - Margins array
     * @returns {Object} Price calculation result
     * @private
     */
    _calculateSingleCombination(combination, items, format, category, margins) {
        let rowPrice = 0;
        let amountOfSheetsPrinted = 0;
        let dlv = null;
        let machine = null;
        let color = null;
        let duration = null;
        let calculation = null;
        let laminateMachine = null;

        // Get printing cost
        if (combination.printing) {
            const printingResult = combination.printing.results;

            amountOfSheetsPrinted = parseFloat(
                printingResult.calculation.amount_of_sheets_printed
            ) || 0;

            if (printingResult.color.status !== 422) {
                dlv = printingResult.calculation.color.dlv;
            }

            machine = printingResult.machine;
            color = printingResult.calculation.color;
            duration = printingResult.duration;
            calculation = printingResult.calculation;

            rowPrice = parseFloat(printingResult.calculation.total_sheet_price) || 0;
        }

        // Add lamination cost
        if (combination.lamination) {
            laminateMachine = combination.lamination.results.machine;

            const laminationCost = this.optionsService.calculateLaminationCost(
                combination.lamination.results,
                format,
                amountOfSheetsPrinted
            );

            rowPrice += laminationCost;
        }

        // Calculate extra options (binding, folding, etc.)
        const optionsItems = this.optionsService.filterOptionsItems(items);
        const optionsCost = this.optionsService.calculateOptions(
            optionsItems,
            format,
            format.quantity,
            category,
            amountOfSheetsPrinted
        );

        rowPrice += optionsCost.total;

        // Convert to cents for price field
        const priceInCents = Number(rowPrice.toFixed(2).replace('.', ''));

        return {
            category: category,
            items: items,
            objects: items,
            margins: margins.length ? margins[0] : [],
            row_price: rowPrice,
            price: priceInCents,
            dlv: dlv,
            machine: machine,
            laminate_machine: laminateMachine,
            color: color,
            duration: duration,
            calculation: calculation,
            options_cost: optionsCost,
            error: {
                message: '',
                status: 200
            }
        };
    }

    /**
     * Find cheapest option from array of price results
     *
     * @param {Array} priceResults - Array of price calculation results
     * @returns {Object} Cheapest option
     * @private
     */
    _findCheapest(priceResults) {
        if (!priceResults.length) {
            throw new Error('No price results to compare');
        }

        // Filter valid results
        const validResults = priceResults.filter(
            result => result.error.status === 200 && result.row_price > 0
        );

        if (!validResults.length) {
            throw new Error('No valid price results found');
        }

        // Find minimum price
        const minPrice = Math.min(...validResults.map(result => result.row_price));

        // Return first result with minimum price
        return validResults.find(result => result.row_price === minPrice);
    }

    /**
     * Calculate price for divided calculation
     *
     * Used when dividers are present (e.g. cover vs content)
     *
     * @param {Array} combinations - Array of machine combinations
     * @param {Array} items - Product items for this division
     * @param {Object} format - Format calculation result
     * @param {Object} category - Category object
     * @returns {Object} Price calculation for division
     */
    calculateDividedPrice(combinations, items, format, category) {
        try {
            const allPrices = [];

            for (const combination of combinations) {
                let rowPrice = 0;
                let amountOfSheetsPrinted = 0;
                let dlv = null;
                let machine = null;
                let color = null;
                let duration = null;
                let calculation = null;

                // Get printing cost
                if (combination.printing) {
                    const printingResult = combination.printing.results;

                    amountOfSheetsPrinted = parseFloat(
                        printingResult.calculation.amount_of_sheets_printed
                    ) || 0;

                    if (printingResult.color.status !== 422) {
                        dlv = printingResult.calculation.color.dlv;
                    }

                    machine = printingResult.machine;
                    color = printingResult.calculation.color;
                    duration = printingResult.duration;
                    calculation = printingResult.calculation;

                    rowPrice = parseFloat(printingResult.calculation.total_sheet_price) +
                        parseFloat(printingResult.calculation.endpaper_total_sheet_price || 0);
                }

                // Add lamination cost
                if (combination.lamination) {
                    const laminationCost = this.optionsService.calculateLaminationCost(
                        combination.lamination.results,
                        format,
                        amountOfSheetsPrinted
                    );

                    rowPrice += laminationCost;
                }

                // Calculate extra options
                const optionsItems = this.optionsService.filterOptionsItems(items);
                const optionsCost = this.optionsService.calculateOptions(
                    optionsItems,
                    format,
                    format.quantity,
                    category,
                    amountOfSheetsPrinted
                );

                rowPrice += optionsCost.total;

                const priceInCents = Number(rowPrice.toFixed(2).replace('.', ''));

                allPrices.push({
                    items: items,
                    row_price: rowPrice,
                    price: priceInCents,
                    dlv: dlv,
                    machine: machine,
                    color: color,
                    duration: duration,
                    calculation: calculation,
                    error: {
                        message: '',
                        status: 200
                    }
                });
            }

            return this._findCheapest(allPrices);
        } catch (error) {
            throw new Error(`Divided price calculation failed: ${error.message}`);
        }
    }
}

module.exports = PriceCalculationService;
