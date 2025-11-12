const CategoryService = require('./CategoryService');
const ProductService = require('./ProductService');
const MarginService = require('./MarginService');
const FormatService = require('./FormatService');
const CatalogueService = require('./CatalogueService');
const MachineCalculationService = require('./MachineCalculationService');
const PriceCalculationService = require('./PriceCalculationService');
const PriceFormatterService = require('./PriceFormatterService');
const DividedCalculationHandler = require('./DividedCalculationHandler');
const DurationCalculator = require('./DurationCalculator');
const { filterByCalcRef, findDiscountSlot } = require('../Helpers/Helper');

/**
 * CalculationPipeline
 *
 * Main orchestrator for V2 calculation system.
 * Coordinates all services to produce complete price calculation.
 *
 * Replaces the monolithic FetchProduct God Object with organized services.
 */
class CalculationPipeline {
    /**
     * Create new calculation pipeline
     *
     * @param {Object} context - Calculation context
     * @param {string} context.slug - Category slug
     * @param {string} context.supplierId - Supplier/tenant ID
     * @param {Array} context.items - Product items (enriched with IDs)
     * @param {number} context.quantity - Quantity
     * @param {number} context.vat - VAT percentage
     * @param {boolean} context.vatOverride - Override category VAT
     * @param {boolean} context.internal - Internal calculation flag
     * @param {Object} context.request - Full request object
     * @param {Object|null} context.contract - Contract for discounts
     */
    constructor(context) {
        this.context = context;

        // Initialize all services
        this.categoryService = new CategoryService();
        this.productService = new ProductService();
        this.marginService = new MarginService();
        this.formatService = new FormatService();
        this.catalogueService = new CatalogueService();
        this.machineCalculationService = new MachineCalculationService();
        this.priceCalculationService = new PriceCalculationService();
        this.priceFormatterService = new PriceFormatterService();
        this.dividedCalculationHandler = new DividedCalculationHandler();
        this.durationCalculator = new DurationCalculator();

        // Pipeline data (populated during execution)
        this.category = null;
        this.machines = [];
        this.boops = null;
        this.matchedItems = [];
        this.margins = [];
        this.discount = [];
        this.formatResult = null;
        this.catalogue = null;
        this.combinations = [];
        this.finalPrice = null;
        this.isDivided = false;
        this.dividedResult = null;
    }

    /**
     * Execute the full calculation pipeline
     *
     * @returns {Promise<Object>} Complete calculation response
     */
    async execute() {
        try {
            console.log('🚀 V2 Calculation Pipeline - Starting');

            // Step 1: Load category and machines
            await this._loadCategory();
            console.log(`✓ Category loaded: ${this.category.name} (${this.machines.length} machines)`);

            // Step 2: Match products
            await this._matchProducts();
            console.log(`✓ Products matched: ${this.matchedItems.length} items`);

            // Step 3: Fetch margins
            await this._fetchMargins();
            console.log(`✓ Margins fetched: ${this.margins.length > 0 ? 'Yes' : 'No'}`);

            // Step 4: Calculate format
            await this._calculateFormat();
            console.log(`✓ Format calculated: ${this.formatResult.width}x${this.formatResult.height}mm`);

            // Step 5: Fetch catalogue (materials)
            await this._fetchCatalogue();
            console.log(`✓ Materials fetched: ${this.catalogue.results.length} materials`);

            // Step 6: Check if calculation should be divided
            this.isDivided = this.dividedCalculationHandler.shouldDivide(
                this.matchedItems,
                this.boops
            );
            console.log(`✓ Calculation type: ${this.isDivided ? 'Divided' : 'Simple'}`);

            // Step 7: Calculate (divided or simple)
            if (this.isDivided) {
                await this._calculateDivided();
            } else {
                await this._calculateSimple();
            }

            // Step 8: Format response
            const response = await this._formatResponse();
            console.log('✓ Response formatted');

            console.log('✅ V2 Calculation Pipeline - Complete');

            return response;
        } catch (error) {
            console.error('❌ V2 Calculation Pipeline - Error:', error.message);
            throw error;
        }
    }

    /**
     * Step 1: Load category, machines, and boops
     *
     * @private
     */
    async _loadCategory() {
        const result = await this.categoryService.getCategory(
            this.context.slug,
            this.context.supplierId
        );

        this.category = result.category;
        this.machines = result.machines;
        this.boops = result.boops;

        // Find discount slot if contract provided
        if (this.context.contract) {
            this.discount = findDiscountSlot(
                this.context.contract,
                this.category._id.toString(),
                this.context.quantity
            );
        }
    }

    /**
     * Step 2: Match products by IDs
     *
     * @private
     */
    async _matchProducts() {
        const result = await this.productService.getMatchedProducts(
            this.context.items,
            this.context.supplierId,
            this.boops,
            this.category._id
        );

        this.matchedItems = result.items;
    }

    /**
     * Step 3: Fetch margins from margin service
     *
     * @private
     */
    async _fetchMargins() {
        this.margins = await this.marginService.getMargins(
            this.context.supplierId,
            this.context.slug,
            this.context.quantity,
            this.context.internal
        );
    }

    /**
     * Step 4: Calculate format details
     *
     * @private
     */
    async _calculateFormat() {
        const bleed = this.context.request.bleed || this.category.bleed;

        this.formatResult = await this.formatService.calculate(
            this.category,
            this.matchedItems,
            this.context.quantity,
            bleed,
            this.context.request
        );

        if (this.formatResult.status !== 200) {
            throw new Error(this.formatResult.message);
        }
    }

    /**
     * Step 5: Fetch catalogue (materials/paper)
     *
     * @private
     */
    async _fetchCatalogue() {
        this.catalogue = await this.catalogueService.fetchMaterials(
            this.matchedItems,
            this.context.supplierId
        );

        if (this.catalogue.status !== 200) {
            throw new Error(this.catalogue.message);
        }
    }

    /**
     * Calculate divided calculation (cover + content, etc.)
     *
     * @private
     */
    async _calculateDivided() {
        console.log('🔀 Running divided calculation');

        // Store full context for divided handler
        const divContext = {
            ...this.context,
            machines: this.machines,
            category: this.category,
            boops: this.boops,
            matchedItems: this.matchedItems,
            margins: this.margins
        };

        this.dividedResult = await this.dividedCalculationHandler.calculateDivided(
            divContext,
            this.formatResult,
            this.catalogue
        );

        console.log(`✓ Divided calculation complete: ${this.dividedResult.divisions.length} divisions`);
        console.log(`✓ Total price: €${this.dividedResult.combined.total_row_price.toFixed(2)}`);
    }

    /**
     * Calculate simple (non-divided) calculation
     *
     * @private
     */
    async _calculateSimple() {
        console.log('Running simple calculation');

        // Step 6: Run machine calculations
        const bindingMethod = filterByCalcRef(this.matchedItems, 'binding_method');
        const bindingDirection = filterByCalcRef(this.matchedItems, 'binding_direction');
        const endpapers = filterByCalcRef(this.matchedItems, 'endpapers');

        this.combinations = await this.machineCalculationService.runCombinations(
            this.machines,
            this.formatResult,
            this.catalogue,
            this.matchedItems,
            this.context.request,
            this.category,
            {},
            bindingMethod,
            bindingDirection,
            endpapers
        );

        console.log(`✓ Machine combinations: ${this.combinations.length} options`);

        // Step 7: Calculate final prices
        this.finalPrice = this.priceCalculationService.calculatePrices(
            this.combinations,
            this.matchedItems,
            this.formatResult,
            this.category,
            this.margins
        );

        if (this.finalPrice.status !== 200) {
            throw new Error(this.finalPrice.message);
        }

        console.log(`✓ Final price calculated: €${this.finalPrice.cheapest.row_price.toFixed(2)}`);
    }

    /**
     * Step 8: Format complete response
     *
     * @private
     * @returns {Object} Formatted response
     */
    async _formatResponse() {
        if (this.isDivided) {
            return this._formatDividedResponse();
        } else {
            return this._formatSimpleResponse();
        }
    }

    /**
     * Format divided calculation response
     *
     * @private
     * @returns {Object} Formatted divided response
     */
    _formatDividedResponse() {
        const response = this.dividedCalculationHandler.formatDividedResponse(
            this.dividedResult,
            {
                ...this.context,
                matchedItems: this.matchedItems
            },
            this.margins,
            this.category
        );

        // Add V2 metadata
        response.v2_pipeline = true;
        response.calculation_version = '2.0';
        response.connection = this.category.tenant_id;
        response.external_id = this.category.tenant_id;
        response.external_name = this.category.tenant_name;

        return response;
    }

    /**
     * Format simple calculation response
     *
     * @private
     * @returns {Object} Formatted simple response
     */
    _formatSimpleResponse() {
        const cheapest = this.finalPrice.cheapest;

        // Calculate duration if not present
        let duration = cheapest.duration;
        if (!duration && cheapest.machine && cheapest.calculation) {
            duration = this.durationCalculator.calculateDuration(
                { machine: cheapest.machine, results: { calculation: cheapest.calculation } },
                this.formatResult,
                this.context.quantity
            );
        }

        // Format price object using PriceFormatterService
        const formattedPrice = this.priceFormatterService.formatPriceObject({
            category: this.category,
            quantity: this.context.quantity,
            price: cheapest.row_price,
            dlv: cheapest.dlv,
            machine: cheapest.machine,
            margins: this.margins.length ? this.margins[0] : [],
            discount: this.discount,
            requestDlv: this.context.request.dlv,
            internal: this.context.internal,
            vat: parseFloat(this.context.vat),
            vatOverride: this.context.vatOverride
        });

        // Build response structure (matches V1 format but with improvements)
        return {
            type: 'print',
            connection: this.category.tenant_id,
            external: '',
            external_id: this.category.tenant_id,
            external_name: this.category.tenant_name,
            calculation_type: 'full_calculation',
            v2_pipeline: true,
            calculation_version: '2.0',
            items: this.matchedItems,
            product: this.context.items,
            category: this.category,
            margins: this.context.internal && this.margins.length ? this.margins[0] : [],
            divided: false,
            quantity: this.context.quantity,
            calculation: [{
                name: null,
                items: cheapest.objects,
                dlv: cheapest.dlv,
                machine: cheapest.machine,
                color: cheapest.color,
                row_price: cheapest.row_price,
                duration: duration, // Now properly calculated!
                price_list: cheapest.calculation?.price_list,
                details: cheapest.calculation,
                price: formattedPrice,
                error: cheapest.error
            }],
            prices: [formattedPrice]
        };
    }

    /**
     * Get pipeline status (for debugging)
     *
     * @returns {Object} Pipeline status
     */
    getStatus() {
        return {
            category_loaded: this.category !== null,
            machines_count: this.machines.length,
            products_matched: this.matchedItems.length,
            margins_loaded: this.margins.length > 0,
            format_calculated: this.formatResult !== null,
            catalogue_loaded: this.catalogue !== null,
            combinations_generated: this.combinations.length,
            final_price_calculated: this.finalPrice !== null
        };
    }
}

module.exports = CalculationPipeline;
