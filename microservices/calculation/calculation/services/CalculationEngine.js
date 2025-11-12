const CategoryService = require('./CategoryService');
const ProductService = require('./ProductService');
const MarginService = require('./MarginService');
const DiscountService = require('./DiscountService');
const PriceFormatterService = require('./PriceFormatterService');

const FetchCatalogue = require('../Calculations/Catalogues/FetchCatalogue');
const Machines = require('../Calculations/Machines');
const Format = require('../Calculations/Config/Format');

const { ValidationError, CalculationError } = require('../errors');

/**
 * CalculationEngine
 *
 * Main orchestrator for product calculations.
 * Replaces the God Object FetchProduct class with a clean service-based architecture.
 *
 * This class coordinates:
 * - Category fetching and validation
 * - Product matching (boxes + options)
 * - Material/catalogue lookups
 * - Machine calculations
 * - Margin and discount application
 * - Price formatting
 */
class CalculationEngine {
    constructor(services = {}) {
        // Inject services (allows for testing with mocks)
        this.categoryService = services.categoryService || new CategoryService();
        this.productService = services.productService || new ProductService();
        this.marginService = services.marginService || new MarginService();
        this.discountService = services.discountService || new DiscountService();
        this.priceFormatter = services.priceFormatter || new PriceFormatterService();
    }

    /**
     * Run complete calculation
     *
     * @param {Object} params - Calculation parameters
     * @returns {Promise<Object>} Calculation result
     */
    async calculate(params) {
        const {
            slug,
            supplierId,
            productItems,
            quantity,
            contract,
            internal,
            vat,
            vatOverride,
            requestDlv
        } = params;

        // Validate inputs
        this._validateInputs(params);

        try {
            // Step 1: Fetch and validate category
            const { category, machines, boops } = await this.categoryService.getCategory(
                slug,
                supplierId
            );

            // Step 2: Get discount from contract
            const discount = this.discountService.getDiscount(
                contract,
                category._id.toString(),
                quantity
            );

            // Step 3: Match products (boxes + options)
            const matchedProducts = await this.productService.getMatchedProducts(
                productItems,
                supplierId,
                boops,
                category._id.toString()
            );

            // Step 4: Extract key product attributes
            const format = this.productService.getFormat(matchedProducts);
            const material = this.productService.getMaterial(matchedProducts);
            const weight = this.productService.getWeight(matchedProducts);
            const colors = this.productService.getPrintingColors(matchedProducts);

            if (!format) {
                throw new ValidationError('Format is required for calculation');
            }

            // Step 5: Fetch catalogues (materials)
            const catalogues = await this._fetchCatalogues(
                supplierId,
                material,
                weight,
                matchedProducts
            );

            // Step 6: Calculate format dimensions
            const formatConfig = await this._calculateFormat(
                format,
                quantity,
                matchedProducts,
                category
            );

            // Step 7: Run machine calculations
            const machineResults = await this._runMachineCalculations(
                machines,
                formatConfig,
                material,
                weight,
                catalogues,
                slug,
                supplierId,
                matchedProducts,
                params,
                category,
                colors
            );

            // Step 8: Fetch margins
            const margins = await this.marginService.getMargins(
                supplierId,
                category._id.toString(),
                quantity,
                internal
            );

            // Step 9: Format prices
            const prices = this._formatPrices(
                machineResults,
                category,
                quantity,
                margins,
                discount,
                requestDlv,
                internal,
                vat,
                vatOverride
            );

            // Step 10: Build response
            return this._buildResponse({
                category,
                matchedProducts,
                productItems,
                quantity,
                machineResults,
                prices,
                margins,
                discount,
                supplierId,
                internal
            });

        } catch (error) {
            // Re-throw known errors
            if (error.statusCode) {
                throw error;
            }

            // Wrap unknown errors
            throw new CalculationError(
                `Calculation failed: ${error.message}`,
                error
            );
        }
    }

    /**
     * Validate calculation inputs
     *
     * @param {Object} params - Input parameters
     * @throws {ValidationError} If validation fails
     * @private
     */
    _validateInputs(params) {
        const { slug, supplierId, productItems, quantity } = params;

        if (!slug) {
            throw new ValidationError('Category slug is required');
        }

        if (!supplierId) {
            throw new ValidationError('Supplier ID is required');
        }

        if (!productItems || !Array.isArray(productItems) || productItems.length === 0) {
            throw new ValidationError('Product items are required');
        }

        if (!quantity || quantity <= 0) {
            throw new ValidationError('Valid quantity is required');
        }
    }

    /**
     * Fetch catalogues (materials)
     *
     * @param {string} supplierId - Supplier ID
     * @param {Object} material - Material object
     * @param {Object} weight - Weight object
     * @param {Array} products - Matched products
     * @returns {Promise<Array>} Catalogues
     * @private
     */
    async _fetchCatalogues(supplierId, material, weight, products) {
        // Use existing FetchCatalogue class (to be refactored later)
        const fetchCatalogue = new FetchCatalogue(
            supplierId,
            material,
            weight,
            products
        );

        const result = await fetchCatalogue.get();

        if (result.status === 422) {
            throw new ValidationError(result.message);
        }

        return result;
    }

    /**
     * Calculate format configuration
     *
     * @param {Object} format - Format object
     * @param {number} quantity - Quantity
     * @param {Array} products - Matched products
     * @param {Object} category - Category object
     * @returns {Promise<Object>} Format configuration
     * @private
     */
    async _calculateFormat(format, quantity, products, category) {
        // Use existing Format class (to be refactored later)
        const formatCalc = new Format(format, quantity, products, category);
        const formatConfig = await formatCalc.get();

        if (formatConfig.status === 422) {
            throw new ValidationError(formatConfig.message);
        }

        return formatConfig;
    }

    /**
     * Run machine calculations
     *
     * @param {Array} machines - Available machines
     * @param {Object} formatConfig - Format configuration
     * @param {Object} material - Material object
     * @param {Object} weight - Weight object
     * @param {Array} catalogues - Catalogues
     * @param {string} slug - Category slug
     * @param {string} supplierId - Supplier ID
     * @param {Array} products - Matched products
     * @param {Object} params - Original parameters
     * @param {Object} category - Category object
     * @param {Object} colors - Printing colors
     * @returns {Promise<Array>} Machine calculation results
     * @private
     */
    async _runMachineCalculations(
        machines,
        formatConfig,
        material,
        weight,
        catalogues,
        slug,
        supplierId,
        products,
        params,
        category,
        colors
    ) {
        // Extract binding and folding info
        const bindingMethod = products.find(p => p.box_calc_ref === 'binding_method');
        const bindingDirection = products.find(p => p.box_calc_ref === 'binding_direction');
        const endpapers = products.find(p => p.box_calc_ref === 'endpapers');

        // Content calculation
        const content = await this._calculateContent(products, formatConfig);

        // Use existing Machines class (to be refactored later)
        const machineCalc = new Machines(
            formatConfig,
            material,
            weight,
            catalogues,
            machines,
            slug,
            supplierId,
            products,
            params,
            category,
            content,
            bindingMethod?.option || {},
            bindingDirection?.option || {},
            endpapers?.option || {}
        );

        const results = await machineCalc.prepare();

        if (results.status === 422) {
            throw new CalculationError(results.message);
        }

        return results;
    }

    /**
     * Calculate content (pages, thickness, etc.)
     *
     * @param {Array} products - Matched products
     * @param {Object} formatConfig - Format configuration
     * @returns {Object} Content information
     * @private
     */
    async _calculateContent(products, formatConfig) {
        const pagesProduct = products.find(p => p.box_calc_ref === 'pages');
        const coverProduct = products.find(p => p.box_calc_ref === 'cover');

        return {
            pass: !!coverProduct,
            pages: pagesProduct?.option?.value || 0,
            grs: 0,
            density: 0,
            thickness: 0
        };
    }

    /**
     * Format prices from machine results
     *
     * @param {Array} machineResults - Machine calculation results
     * @param {Object} category - Category object
     * @param {number} quantity - Quantity
     * @param {Array} margins - Margins
     * @param {Object} discount - Discount
     * @param {Object} requestDlv - Requested delivery
     * @param {boolean} internal - Internal calculation
     * @param {number} vat - VAT percentage
     * @param {boolean} vatOverride - Override category VAT
     * @returns {Array} Formatted prices
     * @private
     */
    _formatPrices(
        machineResults,
        category,
        quantity,
        margins,
        discount,
        requestDlv,
        internal,
        vat,
        vatOverride
    ) {
        const prices = [];

        // Extract delivery options from category
        const deliveryOptions = category.production_dlv || [];

        for (const result of machineResults) {
            if (result.results && result.results.calculation) {
                const calculation = result.results.calculation;
                const machine = result.results.machine;

                // Generate price for each delivery option
                for (const dlv of deliveryOptions) {
                    const priceParams = {
                        category,
                        quantity,
                        price: calculation.price || 0,
                        dlv,
                        machine,
                        margins,
                        discount,
                        requestDlv,
                        internal,
                        vat,
                        vatOverride
                    };

                    const formattedPrice = this.priceFormatter.formatPriceObject(priceParams);
                    prices.push(formattedPrice);
                }
            }
        }

        return prices;
    }

    /**
     * Build final response object
     *
     * @param {Object} data - Response data
     * @returns {Object} Formatted response
     * @private
     */
    _buildResponse(data) {
        const {
            category,
            matchedProducts,
            productItems,
            quantity,
            machineResults,
            prices,
            margins,
            discount,
            supplierId,
            internal
        } = data;

        return {
            type: 'print',
            connection: supplierId,
            external: '',
            external_id: supplierId,
            external_name: category.tenant_name || supplierId,
            calculation_type: 'full_calculation',
            items: matchedProducts,
            product: productItems,
            category: {
                _id: category._id,
                slug: category.slug,
                name: category.name || category.slug
            },
            margins: internal ? margins : [],
            divided: false,
            quantity: quantity,
            calculation: machineResults,
            prices: prices
        };
    }
}

module.exports = CalculationEngine;
