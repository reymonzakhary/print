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

            // Group results by type (printing, lamination, finishing)
            const groups = this._groupByType(results);

            // Validate printing machines exist
            if (!groups.hasOwnProperty('printing')) {
                if (results?.status === 422 || results?.length === 0) {
                    throw new Error(
                        results?.message ? results?.message : 'There is no printing machine found.'
                    );
                }
            }

            // Create combinations (printing + lamination + finishing)
            const combos = combinations(groups);

            return combos;
        } catch (error) {
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
