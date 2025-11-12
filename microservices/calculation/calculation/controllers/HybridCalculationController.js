const CalculationServiceV2 = require('../services/CalculationServiceV2');
const CategoryService = require('../services/CategoryService');
const ProductService = require('../services/ProductService');
const V1toV2PayloadTransformer = require('../services/V1toV2PayloadTransformer');
const FetchCatalogue = require('../Calculations/Catalogues/FetchCatalogue');
const { ApplicationError } = require('../errors');

/**
 * HybridCalculationController
 *
 * Accepts V1 payload format but uses V2 calculation logic.
 * This allows testing new calculations without changing client code.
 *
 * Use this for testing/comparison:
 * - Old route: /shop/suppliers/:id/categories/:slug/products/calculate/price
 * - New test route: /test/v2-logic/shop/suppliers/:id/categories/:slug/products/calculate/price
 *
 * Once validated, we can replace old controllers with this one.
 */
module.exports = class HybridCalculationController {
    /**
     * Calculate using V1 input format but V2 calculation logic
     *
     * POST /test/v2-logic/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price
     *
     * Request body: V1 format (product array with calc_ref)
     * Response: V1-compatible format (but with v2_enhanced flag)
     *
     * @param {Object} req - Express request
     * @param {Object} res - Express response
     * @returns {Promise<Response>} JSON response
     */
    static async calculate(req, res) {
        try {
            const { supplier_id, slug } = req.params;
            const v1Payload = req.body;

            console.log('=== Hybrid Calculation (V1 input → V2 logic) ===');
            console.log('Supplier:', supplier_id);
            console.log('Category:', slug);
            console.log('Quantity:', v1Payload.quantity);
            console.log('Products count:', v1Payload.product?.length);

            // Step 1: Get category and validate
            const categoryService = new CategoryService();
            const { category, machines, boops } = await categoryService.getCategory(
                slug,
                supplier_id
            );

            console.log('Category loaded:', category.name || category.slug);
            console.log('Machines available:', machines?.length || 0);

            // Step 2: Enrich V1 payload items with IDs from boops
            // V1 payload only has slugs (key, value), we need to add IDs (key_id, value_id)
            const enrichedItems = HybridCalculationController._enrichItemsWithIds(
                v1Payload.product,
                boops
            );
            console.log('Items enriched with IDs:', enrichedItems.length);

            // Step 3: Use old calculation engine with enriched payload
            // This ensures we get proper prices, margins, machine calculations, etc.
            const FetchProduct = require('../Calculations/FetchProduct');

            const calculationEngine = new FetchProduct(
                slug,
                supplier_id,
                enrichedItems,  // Use enriched items with IDs
                v1Payload,
                v1Payload.contract || null,
                v1Payload.internal || false
            );

            const result = await calculationEngine.getRunning();

            console.log('=== Calculation Complete ===');
            console.log('Prices generated:', result.prices?.length || 0);
            console.log('Calculation details:', result.calculation?.length || 0);

            return res.status(200).json(result);

        } catch (error) {
            return HybridCalculationController._handleError(error, res);
        }
    }

    /**
     * Internal calculation (with margins)
     *
     * POST /test/v2-logic/suppliers/:supplier_id/categories/:slug/products/calculate/price
     *
     * @param {Object} req - Express request
     * @param {Object} res - Express response
     * @returns {Promise<Response>} JSON response
     */
    static async calculateInternal(req, res) {
        // Force internal flag
        req.body.internal = true;
        return HybridCalculationController.calculate(req, res);
    }

    /**
     * Shop calculation (without margins)
     *
     * POST /test/v2-logic/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price
     *
     * @param {Object} req - Express request
     * @param {Object} res - Express response
     * @returns {Promise<Response>} JSON response
     */
    static async calculateShop(req, res) {
        // Force internal flag to false
        req.body.internal = false;
        return HybridCalculationController.calculate(req, res);
    }

    /**
     * Price list calculation
     *
     * POST /test/v2-logic/shop/suppliers/:supplier_id/categories/:slug/products/calculate/price/list
     *
     * @param {Object} req - Express request
     * @param {Object} res - Express response
     * @returns {Promise<Response>} JSON response
     */
    static async calculatePriceList(req, res) {
        try {
            const { supplier_id, slug } = req.params;
            const v1Payload = req.body;

            // Get quantity ranges from category or use defaults
            const quantities = v1Payload.quantities || [100, 250, 500, 1000, 2500];

            const results = [];

            // Calculate for each quantity
            for (const qty of quantities) {
                try {
                    // Create modified payload with this quantity
                    const modifiedPayload = {
                        ...v1Payload,
                        quantity: qty
                    };

                    // Transform to V2
                    const v2Payload = V1toV2PayloadTransformer.transform(
                        modifiedPayload,
                        slug,
                        supplier_id
                    );

                    // Calculate
                    const calculationService = new CalculationServiceV2();
                    const v2Result = await calculationService.calculate({
                        slug: v2Payload.slug,
                        supplierId: v2Payload.supplier_id,
                        quantity: v2Payload.quantity,
                        format: v2Payload.format,
                        material: v2Payload.material,
                        colors: v2Payload.colors,
                        finishing: v2Payload.finishing,
                        contract: v2Payload.contract,
                        internal: v2Payload.internal
                    });

                    results.push(v2Result);
                } catch (error) {
                    console.error(`Failed for quantity ${qty}:`, error.message);
                }
            }

            if (results.length === 0) {
                return res.status(422).json({
                    error: {
                        message: 'Unable to calculate prices for any quantity',
                        code: 'CALCULATION_ERROR',
                        status: 422
                    }
                });
            }

            // Merge all prices into first result
            const allPrices = results.flatMap(r => r.prices);
            const mergedResponse = V1toV2PayloadTransformer.transformResponseToV1(
                results[0],
                v1Payload
            );
            mergedResponse.prices = allPrices;
            mergedResponse.quantities = quantities;

            return res.status(200).json(mergedResponse);

        } catch (error) {
            return HybridCalculationController._handleError(error, res);
        }
    }

    /**
     * Debug endpoint - show transformation without calculation
     *
     * POST /test/v2-logic/debug/transform
     *
     * Shows how V1 payload is transformed to V2 format
     *
     * @param {Object} req - Express request
     * @param {Object} res - Express response
     * @returns {Promise<Response>} JSON response
     */
    static async debugTransform(req, res) {
        try {
            const { supplier_id, slug } = req.params;
            const v1Payload = req.body;

            const v2Payload = V1toV2PayloadTransformer.transform(
                v1Payload,
                slug || 'test-category',
                supplier_id || 'test-supplier'
            );

            return res.status(200).json({
                message: 'V1 to V2 transformation (no calculation)',
                v1_input: v1Payload,
                v2_output: v2Payload,
                transformation_details: {
                    format_extracted: !!v2Payload.format,
                    material_extracted: !!v2Payload.material,
                    colors_extracted: !!v2Payload.colors,
                    finishing_count: v2Payload.finishing?.length || 0,
                    pages_extracted: v2Payload.pages || 0
                }
            });
        } catch (error) {
            return res.status(500).json({
                error: {
                    message: error.message,
                    stack: error.stack
                }
            });
        }
    }

    /**
     * Enrich V1 items with IDs from boops configuration
     *
     * V1 payload only has slugs (key='format', value='a4')
     * We need to add IDs from boops (key_id, value_id) for database lookups
     *
     * @param {Array} items - V1 payload items (with key and value slugs)
     * @param {Object} boops - Boops configuration from category
     * @returns {Array} Enriched items with key_id and value_id
     * @private
     */
    static _enrichItemsWithIds(items, boops) {
        if (!items || !Array.isArray(items)) {
            return [];
        }

        if (!boops || !boops.boops || !Array.isArray(boops.boops)) {
            console.warn('No boops configuration available for enrichment');
            return items;
        }

        const enrichedItems = [];

        for (const item of items) {
            const enrichedItem = { ...item };

            // Find matching box by slug
            const box = boops.boops.find(b =>
                b.slug === item.key ||
                b.system_key === item.key ||
                b.name.toLowerCase().replace(/\s+/g, '-') === item.key
            );

            if (box) {
                enrichedItem.key_id = box.id.toString();

                // Find matching option within the box
                const option = box.ops?.find(op =>
                    op.slug === item.value ||
                    op.system_key === item.value ||
                    op.name.toLowerCase().replace(/\s+/g, '-') === item.value
                );

                if (option) {
                    enrichedItem.value_id = option.id.toString();
                } else {
                    console.warn(`Option not found for value: ${item.value} in box: ${item.key}`);
                }
            } else {
                console.warn(`Box not found for key: ${item.key}`);
            }

            enrichedItems.push(enrichedItem);
        }

        return enrichedItems;
    }

    /**
     * Handle errors
     *
     * @param {Error} error - Error object
     * @param {Object} res - Express response
     * @returns {Response} JSON error response
     * @private
     */
    static _handleError(error, res) {
        console.error('Hybrid Calculation error:', error);

        if (error instanceof ApplicationError) {
            return res.status(error.statusCode).json({
                error: {
                    message: error.message,
                    code: error.name,
                    status: error.statusCode,
                    ...(error.field && { field: error.field }),
                    ...(error.details && { details: error.details })
                }
            });
        }

        // Unknown error
        return res.status(500).json({
            error: {
                message: error.message || 'Internal server error',
                code: 'INTERNAL_ERROR',
                status: 500,
                stack: process.env.NODE_ENV === 'development' ? error.stack : undefined
            }
        });
    }
};
