const CategoryService = require('./CategoryService');
const ProductService = require('./ProductService');
const MarginService = require('./MarginService');
const DiscountService = require('./DiscountService');
const PriceFormatterService = require('./PriceFormatterService');

const DigitalPrintingCalculator = require('./calculators/DigitalPrintingCalculator');
const LaminationCalculator = require('./calculators/LaminationCalculator');
const FinishingCalculator = require('./calculators/FinishingCalculator');

const { ValidationError, CalculationError } = require('../errors');

/**
 * CalculationServiceV2
 *
 * Enhanced calculation service for V2 API.
 * Supports multiple printing methods with accurate, machine-specific calculations.
 *
 * Improvements over V1:
 * - Dedicated calculators for each machine type
 * - Accurate digital printing calculations
 * - Better cost breakdown
 * - Support for all finishing types
 * - Extensible architecture for new machine types
 */
class CalculationServiceV2 {
    constructor(services = {}) {
        this.categoryService = services.categoryService || new CategoryService();
        this.productService = services.productService || new ProductService();
        this.marginService = services.marginService || new MarginService();
        this.discountService = services.discountService || new DiscountService();
        this.priceFormatter = services.priceFormatter || new PriceFormatterService();
    }

    /**
     * Calculate product price with V2 enhanced logic
     *
     * @param {Object} params - Calculation parameters
     * @returns {Promise<Object>} Calculation result
     */
    async calculate(params) {
        const {
            slug,
            supplierId,
            quantity,
            format,
            material,
            colors,
            finishing,
            contract,
            internal
        } = params;

        // Validate inputs
        this._validateInputs(params);

        try {
            // Step 1: Get category and machines
            const { category, machines } = await this.categoryService.getCategory(
                slug,
                supplierId
            );

            // Step 2: Get discount (if contract provided)
            const discount = this.discountService.getDiscount(
                contract,
                category._id.toString(),
                quantity
            );

            // Step 3: Run calculations for each compatible machine
            const machineResults = await this._calculateAllMachines(
                machines,
                format,
                material,
                quantity,
                colors,
                finishing
            );

            if (machineResults.length === 0) {
                throw new CalculationError('No machines can handle this job');
            }

            // Step 4: Get margins
            const margins = await this.marginService.getMargins(
                supplierId,
                category._id.toString(),
                quantity,
                internal
            );

            // Step 5: Format prices for each result
            const prices = this._buildPrices(
                machineResults,
                margins,
                discount,
                quantity,
                category,
                internal
            );

            // Step 6: Build response
            return this._buildResponse({
                category,
                quantity,
                format,
                material,
                colors,
                finishing,
                machineResults,
                prices,
                supplierId,
                internal
            });

        } catch (error) {
            if (error.statusCode) {
                throw error;
            }

            throw new CalculationError(
                `V2 Calculation failed: ${error.message}`,
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
        const { slug, supplierId, quantity, format } = params;

        if (!slug) {
            throw new ValidationError('Category slug is required');
        }

        if (!supplierId) {
            throw new ValidationError('Supplier ID is required');
        }

        if (!quantity || quantity <= 0) {
            throw new ValidationError('Valid quantity is required');
        }

        if (!format || !format.width || !format.height) {
            throw new ValidationError('Valid format with width and height is required');
        }
    }

    /**
     * Calculate for all compatible machines
     *
     * @param {Array} machines - Available machines
     * @param {Object} format - Format details
     * @param {Object} material - Material details
     * @param {number} quantity - Quantity
     * @param {Object} colors - Color configuration
     * @param {Array} finishing - Finishing operations
     * @returns {Promise<Array>} Machine calculation results
     * @private
     */
    async _calculateAllMachines(machines, format, material, quantity, colors, finishing) {
        const results = [];

        for (const machine of machines) {
            try {
                let calculation;

                // Route to appropriate calculator based on machine type
                if (machine.type === 'printing' || machine.type === 'digital') {
                    // Digital printing calculator
                    const calculator = new DigitalPrintingCalculator(
                        machine,
                        format,
                        quantity,
                        material ? [material] : [],
                        { colors, finishing }
                    );
                    calculation = calculator.calculate();
                } else if (machine.type === 'lamination') {
                    // Lamination calculator
                    const laminationType = finishing?.lamination?.type || 'gloss';
                    const calculator = new LaminationCalculator(
                        machine,
                        format,
                        quantity,
                        laminationType,
                        { sides: finishing?.lamination?.sides || 'front' }
                    );
                    calculation = calculator.calculate();
                } else if (machine.type === 'finishing') {
                    // Finishing calculator (die-cut, fold, etc.)
                    const finishingType = finishing?.type || 'die-cut';
                    const calculator = new FinishingCalculator(
                        machine,
                        quantity,
                        finishingType,
                        finishing?.options || {}
                    );
                    calculation = calculator.calculate();
                }

                if (calculation && calculation.status === 200) {
                    results.push({
                        machine,
                        calculation
                    });
                }
            } catch (error) {
                // Log error but continue with other machines
                console.error(`Machine ${machine.name} calculation failed:`, error.message);
            }
        }

        return results;
    }

    /**
     * Build price objects from machine results
     *
     * @param {Array} machineResults - Machine calculation results
     * @param {Array} margins - Margins to apply
     * @param {Object} discount - Discount to apply
     * @param {number} quantity - Quantity
     * @param {Object} category - Category object
     * @param {boolean} internal - Is internal calculation
     * @returns {Array} Formatted prices
     * @private
     */
    _buildPrices(machineResults, margins, discount, quantity, category, internal) {
        const prices = [];

        for (const result of machineResults) {
            const { machine, calculation } = result;

            // Base cost from calculation
            const baseCost = calculation.costs.subtotal;

            // Apply discount
            const afterDiscount = discount
                ? this.discountService.applyDiscount(baseCost, discount)
                : { discounted_price: baseCost, discount_amount: 0 };

            // Apply margins
            const afterMargin = this.marginService.applyMargins(
                afterDiscount.discounted_price,
                margins,
                internal
            );

            // Get delivery options from category
            const deliveryOptions = category.production_dlv || [
                { days: 3, title: 'Standard' }
            ];

            // Create price for each delivery option
            for (const dlv of deliveryOptions) {
                const priceParams = {
                    category,
                    quantity,
                    price: afterMargin.selling_price,
                    dlv,
                    machine,
                    margins: internal ? margins : [],
                    discount: discount || [],
                    requestDlv: null,
                    internal,
                    vat: category.vat || 21,
                    vatOverride: false
                };

                const formattedPrice = this.priceFormatter.formatPriceObject(priceParams);

                // Add V2-specific fields
                formattedPrice.v2_breakdown = {
                    machine: {
                        id: machine._id,
                        name: machine.name,
                        type: machine.type
                    },
                    base_cost: baseCost,
                    discount_amount: afterDiscount.discount_amount,
                    cost_after_discount: afterDiscount.discounted_price,
                    margin_amount: afterMargin.profit || 0,
                    timing: calculation.timing,
                    sheets: calculation.sheets || {},
                    method: calculation.method
                };

                prices.push(formattedPrice);
            }
        }

        return prices;
    }

    /**
     * Build final response
     *
     * @param {Object} data - Response data
     * @returns {Object} Formatted response
     * @private
     */
    _buildResponse(data) {
        const {
            category,
            quantity,
            format,
            material,
            colors,
            finishing,
            machineResults,
            prices,
            supplierId,
            internal
        } = data;

        return {
            api_version: 'v2',
            type: 'print',
            connection: supplierId,
            external_id: supplierId,
            external_name: category.tenant_name || supplierId,
            calculation_type: 'full_calculation_v2',
            category: {
                _id: category._id,
                slug: category.slug,
                name: category.name || category.slug
            },
            configuration: {
                quantity,
                format,
                material,
                colors,
                finishing
            },
            machines_calculated: machineResults.length,
            machines: machineResults.map(r => ({
                id: r.machine._id,
                name: r.machine.name,
                type: r.machine.type,
                method: r.calculation.method
            })),
            prices: prices,
            internal: internal
        };
    }
}

module.exports = CalculationServiceV2;
