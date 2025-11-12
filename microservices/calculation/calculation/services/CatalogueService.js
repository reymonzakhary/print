const FetchCatalogue = require('../Calculations/Catalogues/FetchCatalogue');
const { filterByCalcRef } = require('../Helpers/Helper');

/**
 * CatalogueService
 *
 * Handles material/paper catalogue lookups.
 * Wraps FetchCatalogue in a clean service interface.
 */
class CatalogueService {
    /**
     * Fetch materials from catalogue based on product items
     *
     * @param {Array} items - Product items array
     * @param {string} supplierId - Supplier/tenant ID
     * @returns {Promise<Object>} Catalogue results
     */
    async fetchMaterials(items, supplierId) {
        try {
            // Extract material and weight from items
            const material = filterByCalcRef(items, 'material');
            const weight = filterByCalcRef(items, 'weight');

            // Validate weight exists
            if (!weight.length) {
                throw new Error('The weight parameter not specified.');
            }

            // Fetch catalogue
            const catalogue = await new FetchCatalogue(
                material,
                weight,
                supplierId
            ).get();

            // Check for errors
            if (catalogue.error.status === 422) {
                throw new Error(catalogue.error.message);
            }

            return {
                status: 200,
                results: catalogue.results,
                material: material,
                weight: weight
            };
        } catch (error) {
            return {
                status: 422,
                message: error.message,
                results: [],
                material: [],
                weight: []
            };
        }
    }

    /**
     * Fetch materials for content/cover calculation
     *
     * Used in divided calculations where content pages differ from cover
     *
     * @param {Array} contentItems - Content items (e.g. pages, material, weight)
     * @param {string} supplierId - Supplier/tenant ID
     * @returns {Promise<Object>} Catalogue results for content
     */
    async fetchContentMaterials(contentItems, supplierId) {
        try {
            const material = filterByCalcRef(contentItems, 'material');
            const weight = filterByCalcRef(contentItems, 'weight');

            if (!weight.length || !material.length) {
                return {
                    pass: false,
                    grs: 0,
                    density: 0,
                    thickness: 0,
                    pages: 0
                };
            }

            const catalogue = await new FetchCatalogue(
                material,
                weight,
                supplierId
            ).get();

            if (catalogue.results.length) {
                const pages = filterByCalcRef(contentItems, 'pages');
                const pagesCount = this._calculatePages(pages);

                return {
                    pass: true,
                    grs: catalogue.results[0].grs,
                    density: catalogue.results[0].density,
                    thickness: catalogue.results[0].thickness === Infinity ? 0 : catalogue.results[0].thickness,
                    pages: pagesCount
                };
            }

            return {
                pass: false,
                grs: 0,
                density: 0,
                thickness: 0,
                pages: 0
            };
        } catch (error) {
            console.error('Error fetching content materials:', error.message);
            return {
                pass: false,
                grs: 0,
                density: 0,
                thickness: 0,
                pages: 0
            };
        }
    }

    /**
     * Calculate number of pages from pages items
     *
     * @param {Array} pagesItems - Pages items
     * @returns {number} Number of pages
     * @private
     */
    _calculatePages(pagesItems) {
        if (!pagesItems.length) {
            return 0;
        }

        const pagesOption = pagesItems[0].option;

        if (pagesOption.dynamic && pagesOption._) {
            return parseInt(pagesOption._.pages || 0);
        }

        return parseInt(pagesOption.pages || 0);
    }
}

module.exports = CatalogueService;
