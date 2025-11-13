const Machines = require('../Calculations/Machines');
const { combinations } = require('../Helpers/Helper');

/**
 * MachineCalculationService
 *
 * Orchestrates machine calculations and combinations.
 * Handles running calculations across multiple machines and creating optimal combinations.
 */
class MachineCalculationService {
    /**
     * Run machine calculations and create combinations
     *
     * @param {Array} machines - Array of machine objects
     * @param {Object} format - Format calculation result
     * @param {Object} catalogue - Catalogue results
     * @param {Array} items - Product items
     * @param {Object} request - Request object
     * @param {Object} category - Category object
     * @param {Object} content - Content configuration (for divided calculations)
     * @param {Array} bindingMethod - Binding method items
     * @param {Array} bindingDirection - Binding direction items
     * @param {Array} endpapers - Endpapers items
     * @returns {Promise<Array>} Array of machine combinations
     */
    async runCombinations(
        machines,
        format,
        catalogue,
        items,
        request,
        category,
        content = {},
        bindingMethod = [],
        bindingDirection = [],
        endpapers = []
    ) {
        try {
            // Extract material and weight for Machines calculator
            const material = catalogue.material;
            const weight = catalogue.weight;

            console.log('  → Running machine combinations:', {
                format_width: format.width,
                format_height: format.height,
                format_has_format: !!format.format,
                material: material?.value || material,
                weight: weight?.value || weight,
                catalogue_results_count: catalogue.results?.length,
                machines_count: machines?.length
            });

            // Run Machines calculator (legacy)
            const results = await new Machines(
                format.format,
                material,
                weight,
                catalogue.results,
                machines,
                category.slug,
                category.tenant_id,
                items,
                request,
                category,
                content,
                bindingMethod,
                bindingDirection,
                endpapers
            ).prepare();

            console.log('  → Machines.prepare() returned:', {
                results_type: typeof results,
                results_is_array: Array.isArray(results),
                results_length: results?.length,
                results_status: results?.status,
                first_result_type: results?.[0]?.type
            });

            // Group results by type (printing, lamination, finishing)
            const groups = this._groupByType(results);

            console.log('  → Grouped by type:', {
                has_printing: !!groups.printing,
                has_lamination: !!groups.lamination,
                has_finishing: !!groups.finishing,
                printing_count: groups.printing?.length,
                lamination_count: groups.lamination?.length
            });

            // Validate printing machines exist
            if (!groups.hasOwnProperty('printing')) {
                if (results?.status === 422 || results?.length === 0) {
                    throw new Error(
                        results?.message ? results?.message : 'There is no printing machine found.'
                    );
                }
            }

            // Create combinations (printing + lamination + finishing)
            console.log('  → Creating combinations from groups');
            const combos = combinations(groups);

            console.log('  ✓ Machine combinations created:', combos?.length);

            return combos;
        } catch (error) {
            console.error('  ❌ Machine calculation error:', {
                message: error.message,
                stack: error.stack?.split('\n').slice(0, 3).join('\n')
            });
            throw new Error(`Machine calculation failed: ${error.message}`);
        }
    }

    /**
     * Group machine results by type
     *
     * @param {Array} results - Machine calculation results
     * @returns {Object} Grouped results
     * @private
     */
    _groupByType(results) {
        const map = {};

        results.forEach((item) => {
            const key = item.type;
            const collection = map[key];

            if (!collection) {
                map[key] = [item];
            } else {
                collection.push(item);
            }
        });

        return map;
    }

    /**
     * Get machine by ID
     *
     * @param {Array} machines - Machines array
     * @param {string} machineId - Machine ID
     * @returns {Object|null} Machine object or null
     */
    getMachineById(machines, machineId) {
        return machines.find(m => m._id.toString() === machineId.toString()) || null;
    }

    /**
     * Get machines by type
     *
     * @param {Array} machines - Machines array
     * @param {string} type - Machine type (printing, lamination, finishing)
     * @returns {Array} Filtered machines
     */
    getMachinesByType(machines, type) {
        return machines.filter(m => m.type === type);
    }
}

module.exports = MachineCalculationService;
