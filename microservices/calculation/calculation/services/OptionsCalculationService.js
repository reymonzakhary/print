/**
 * OptionsCalculationService
 *
 * Handles calculation of extra options like binding, folding, finishing, etc.
 * Calculates costs based on quantity, format, and option configuration.
 */
class OptionsCalculationService {
    /**
     * Calculate total cost for all extra options
     *
     * @param {Array} items - Product items (filtered to exclude printing/lamination)
     * @param {Object} format - Format calculation result
     * @param {number} quantity - Quantity
     * @param {Object} category - Category object
     * @param {number} amountOfSheetsPrinted - Number of sheets printed
     * @returns {Object} Options cost breakdown
     */
    calculateOptions(items, format, quantity, category, amountOfSheetsPrinted = 0) {
        try {
            let totalCost = 0;
            const breakdown = [];

            // Add category start cost
            const categoryStartCost = category?.start_cost
                ? parseFloat(category.start_cost) / 100000
                : 0;

            totalCost += categoryStartCost;

            if (categoryStartCost > 0) {
                breakdown.push({
                    type: 'category_start_cost',
                    name: 'Category Setup',
                    cost: categoryStartCost
                });
            }

            // Calculate each option
            for (const item of items) {
                const option = item.option;

                // Skip if no runs defined
                if (!option.runs || !Array.isArray(option.runs)) {
                    continue;
                }

                // Find runs for this category
                const categoryRuns = option.runs.filter(
                    run => run.category_id?.toString() === category._id.toString()
                )[0];

                if (!categoryRuns || !categoryRuns.runs) {
                    continue;
                }

                // Find run slot that matches quantity
                let run = categoryRuns.runs.filter(
                    r => quantity >= parseInt(r.from) && quantity <= parseInt(r.to)
                );

                // If no matching run, use last one
                if (!run?.length) {
                    run = categoryRuns.runs.slice(-1);
                }

                // Calculate costs
                const startCost = parseFloat(categoryRuns?.start_cost ?? 0) / 100000;
                const runPrice = parseFloat(run && run[0]?.price ? run[0].price : 0) / 100000;

                totalCost += startCost;

                // Calculate variable cost based on calculation method
                let variableCost = 0;
                const calculationMethod = option.calculation_method || 'qty';

                switch (calculationMethod) {
                    case 'sqm':
                        variableCost = runPrice * (quantity * format.size.m);
                        break;
                    case 'lm':
                        variableCost = runPrice * (format.size.lm * quantity);
                        break;
                    case 'sheet':
                        variableCost = runPrice * amountOfSheetsPrinted;
                        break;
                    case 'qty':
                    default:
                        variableCost = runPrice * quantity;
                        break;
                }

                totalCost += variableCost;

                // Add to breakdown
                breakdown.push({
                    type: 'option',
                    key: item.key,
                    value: item.value,
                    name: option.display_name || option.name,
                    calculation_method: calculationMethod,
                    start_cost: startCost,
                    run_price: runPrice,
                    variable_cost: variableCost,
                    total_cost: startCost + variableCost
                });
            }

            return {
                status: 200,
                total: totalCost,
                category_start_cost: categoryStartCost,
                breakdown: breakdown
            };
        } catch (error) {
            return {
                status: 422,
                message: error.message,
                total: 0,
                breakdown: []
            };
        }
    }

    /**
     * Calculate lamination cost separately
     *
     * Lamination is handled differently as it's machine-based, not option-based
     *
     * @param {Object} laminationResult - Lamination calculation result
     * @param {Object} format - Format calculation result
     * @param {number} amountOfSheetsPrinted - Number of sheets printed
     * @returns {number} Lamination cost
     */
    calculateLaminationCost(laminationResult, format, amountOfSheetsPrinted) {
        if (!laminationResult || laminationResult.status === 422) {
            return 0;
        }

        const runPrice = parseFloat(laminationResult.calculation.run_price) || 0;
        const calculationMethod = laminationResult.calculation.calculation_method;
        const startCost = parseFloat(laminationResult.calculation.start_cost) || 0;
        const optionStartCost = parseFloat(laminationResult.calculation.option_start_cost) || 0;

        let variableCost = 0;

        switch (calculationMethod) {
            case 'sqm':
                const areaSqm = parseFloat(laminationResult.calculation.area_sqm) || 0;
                variableCost = runPrice * areaSqm;
                break;
            case 'lm':
                const formatSize = parseFloat(format.size.lm) || 0;
                variableCost = runPrice * (formatSize * format.quantity);
                break;
            case 'sheet':
                variableCost = runPrice * amountOfSheetsPrinted;
                break;
            case 'qty':
                variableCost = runPrice * format.quantity;
                break;
            default:
                variableCost = 0;
        }

        return startCost + optionStartCost + variableCost;
    }

    /**
     * Filter out items that should not be in options calculation
     *
     * @param {Array} items - All product items
     * @param {Array} excludeCalcRefs - Calc refs to exclude (e.g. ['printing_colors', 'lamination'])
     * @returns {Array} Filtered items
     */
    filterOptionsItems(items, excludeCalcRefs = ['lamination', 'printing_colors', 'printing-colors']) {
        return items.filter(item => {
            return !excludeCalcRefs.includes(item.box.calc_ref);
        });
    }
}

module.exports = OptionsCalculationService;
