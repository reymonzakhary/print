/**
 * V1toV2PayloadTransformer
 *
 * Transforms V1 legacy payload format to clean V2 format.
 * This allows us to use new V2 calculators with old V1 API format.
 *
 * V1 format: Array of product objects with calc_ref
 * V2 format: Clean structured objects (format, material, colors, etc.)
 */
class V1toV2PayloadTransformer {
    /**
     * Transform V1 payload to V2 format
     *
     * @param {Object} v1Payload - V1 request payload
     * @param {string} slug - Category slug
     * @param {string} supplierId - Supplier ID
     * @returns {Object} V2 format payload
     */
    static transform(v1Payload, slug, supplierId) {
        const { product, quantity, vat, vat_override, divided } = v1Payload;

        // Extract information from product array based on calc_ref
        const extracted = this._extractFromProductArray(product);

        // Build V2 format
        return {
            slug,
            supplier_id: supplierId,
            quantity: parseInt(quantity),
            format: extracted.format,
            material: extracted.material,
            colors: extracted.colors,
            finishing: extracted.finishing,
            pages: extracted.pages,
            sides: extracted.sides,
            contract: v1Payload.contract || null,
            internal: v1Payload.internal || false,
            vat: parseFloat(vat || 21),
            vat_override: vat_override || false,
            divided: divided || false,
            // Keep original for reference
            _v1_original: {
                product: product,
                suppliers: v1Payload.suppliers
            }
        };
    }

    /**
     * Extract structured data from V1 product array
     *
     * @param {Array} productArray - V1 product array
     * @returns {Object} Extracted data
     * @private
     */
    static _extractFromProductArray(productArray) {
        const result = {
            format: null,
            material: null,
            colors: { front: 0, back: 0 },
            finishing: [],
            pages: 0,
            sides: 1,
            weight: null,
            rawProducts: productArray
        };

        // Group by divider (cover, content, etc.)
        const grouped = this._groupByDivider(productArray);

        // Process each group
        for (const [divider, items] of Object.entries(grouped)) {
            for (const item of items) {
                const calcRef = this._normalizeCalcRef(item.key);

                switch (calcRef) {
                    case 'format':
                        if (!result.format) {
                            result.format = this._extractFormat(item);
                        }
                        break;

                    case 'printing_colors':
                        result.colors = this._extractColors(item);
                        break;

                    case 'weight':
                        result.weight = this._extractWeight(item);
                        if (result.material && !result.material.grs) {
                            result.material.grs = result.weight.value;
                            result.material.gsm = result.weight.value; // Backward compat
                        }
                        break;

                    case 'material':
                        result.material = this._extractMaterial(item);
                        if (result.weight && !result.material.grs) {
                            result.material.grs = result.weight.value;
                            result.material.gsm = result.weight.value; // Backward compat
                        }
                        break;

                    case 'number_of_pages':
                        result.pages = this._extractPages(item);
                        break;

                    case 'number_of_sides':
                        result.sides = this._extractSides(item);
                        break;

                    case 'afwerking':
                    case 'lamination':
                    case 'finishing':
                        result.finishing.push(this._extractFinishing(item));
                        break;

                    // Add more calc_refs as needed
                    case 'binding_method':
                    case 'binding_direction':
                    case 'endpapers':
                        // These are kept in rawProducts for now
                        break;
                }
            }
        }

        return result;
    }

    /**
     * Group products by divider
     *
     * @param {Array} productArray - Product array
     * @returns {Object} Grouped by divider
     * @private
     */
    static _groupByDivider(productArray) {
        const grouped = {};

        for (const item of productArray) {
            const divider = item.divider || 'default';
            if (!grouped[divider]) {
                grouped[divider] = [];
            }
            grouped[divider].push(item);
        }

        return grouped;
    }

    /**
     * Normalize calc_ref (handle variations)
     *
     * @param {string} key - Original key
     * @returns {string} Normalized calc_ref
     * @private
     */
    static _normalizeCalcRef(key) {
        return key.toLowerCase().replace(/-/g, '_');
    }

    /**
     * Extract format from item
     *
     * @param {Object} item - Product item
     * @returns {Object} Format object
     * @private
     */
    static _extractFormat(item) {
        // Try to parse format from value
        const value = item.value.toLowerCase();

        // Known formats
        const formats = {
            'a4': { width: 210, height: 297 },
            'a5': { width: 148, height: 210 },
            'a6': { width: 105, height: 148 },
            'a3': { width: 297, height: 420 },
            'a7': { width: 74, height: 105 },
            'dl': { width: 99, height: 210 },
            'business_card': { width: 85, height: 55 },
            'credit_card': { width: 85, height: 54 }
        };

        // Check if it's a known format
        for (const [name, dimensions] of Object.entries(formats)) {
            if (value.includes(name.replace('_', ''))) {
                return {
                    width: dimensions.width,
                    height: dimensions.height,
                    bleed: 3, // Default bleed
                    name: name.toUpperCase(),
                    value_id: item.value_id,
                    key_id: item.key_id
                };
            }
        }

        // If custom format, try to extract dimensions from value
        // e.g., "custom_150x100" or "150x100mm"
        const dimensionMatch = value.match(/(\d+)\s*x\s*(\d+)/);
        if (dimensionMatch) {
            return {
                width: parseInt(dimensionMatch[1]),
                height: parseInt(dimensionMatch[2]),
                bleed: 3,
                name: 'custom',
                value_id: item.value_id,
                key_id: item.key_id
            };
        }

        // Fallback - return A4
        return {
            width: 210,
            height: 297,
            bleed: 3,
            name: value,
            value_id: item.value_id,
            key_id: item.key_id
        };
    }

    /**
     * Extract colors from item
     *
     * @param {Object} item - Product item
     * @returns {Object} Colors object
     * @private
     */
    static _extractColors(item) {
        const value = item.value.toLowerCase();

        // Parse color notation like "44", "4/4", "4-4", "40", "4/0", etc.

        // Check for "44-full-color" or similar
        if (value.includes('44') || value.includes('4/4') || value.includes('4-4')) {
            return { front: 4, back: 4 }; // CMYK both sides
        }

        if (value.includes('40') || value.includes('4/0') || value.includes('4-0')) {
            return { front: 4, back: 0 }; // CMYK front only
        }

        if (value.includes('11') || value.includes('1/1') || value.includes('1-1')) {
            return { front: 1, back: 1 }; // Black both sides
        }

        if (value.includes('10') || value.includes('1/0') || value.includes('1-0')) {
            return { front: 1, back: 0 }; // Black front only
        }

        // Try to parse as number
        const numberMatch = value.match(/(\d)(\d)/);
        if (numberMatch) {
            return {
                front: parseInt(numberMatch[1]),
                back: parseInt(numberMatch[2])
            };
        }

        // Default to CMYK front only
        return { front: 4, back: 0 };
    }

    /**
     * Extract weight/GSM from item
     *
     * @param {Object} item - Product item
     * @returns {Object} Weight object
     * @private
     */
    static _extractWeight(item) {
        const value = item.value.toLowerCase();

        // Extract number from value like "135-grs", "300gsm", etc.
        const numberMatch = value.match(/(\d+)/);
        const gsm = numberMatch ? parseInt(numberMatch[1]) : 0;

        return {
            value: gsm,
            unit: 'gsm',
            value_id: item.value_id,
            key_id: item.key_id
        };
    }

    /**
     * Extract material from item
     *
     * @param {Object} item - Product item
     * @returns {Object} Material object
     * @private
     */
    static _extractMaterial(item) {
        const value = item.value.toLowerCase();

        // Determine material type
        let type = 'paper_uncoated';

        if (value.includes('glossy') || value.includes('gloss')) {
            type = 'paper_coated_gloss';
        } else if (value.includes('matte') || value.includes('mat')) {
            type = 'paper_coated_matte';
        } else if (value.includes('silk')) {
            type = 'paper_coated_silk';
        } else if (value.includes('coated')) {
            type = 'paper_coated';
        } else if (value.includes('cardboard') || value.includes('board')) {
            type = 'cardboard';
        }

        return {
            type: type,
            name: item.value,
            value_id: item.value_id,
            key_id: item.key_id,
            // GSM (grs) will be added from weight extraction or catalogue
            grs: 0,
            gsm: 0, // Keep for backward compatibility
            // Price will be fetched from catalogue
            price: 0,
            calc_type: 'kg'
        };
    }

    /**
     * Extract pages from item
     *
     * @param {Object} item - Product item
     * @returns {number} Number of pages
     * @private
     */
    static _extractPages(item) {
        const value = item.value.toLowerCase();
        const numberMatch = value.match(/(\d+)/);
        return numberMatch ? parseInt(numberMatch[1]) : 0;
    }

    /**
     * Extract sides from item
     *
     * @param {Object} item - Product item
     * @returns {number} Number of sides
     * @private
     */
    static _extractSides(item) {
        const value = item.value.toLowerCase();
        const numberMatch = value.match(/(\d+)/);
        return numberMatch ? parseInt(numberMatch[1]) : 1;
    }

    /**
     * Extract finishing from item
     *
     * @param {Object} item - Product item
     * @returns {Object} Finishing object
     * @private
     */
    static _extractFinishing(item) {
        const value = item.value.toLowerCase();

        // Determine finishing type
        if (value.includes('lamination') || value.includes('lamina')) {
            let laminationType = 'gloss';
            let sides = 'front';

            if (value.includes('matt') || value.includes('matte')) {
                laminationType = 'matte';
            } else if (value.includes('gloss')) {
                laminationType = 'gloss';
            } else if (value.includes('soft')) {
                laminationType = 'soft-touch';
            }

            if (value.includes('double') || value.includes('both') || value.includes('2')) {
                sides = 'both';
            } else if (value.includes('single') || value.includes('1')) {
                sides = 'front';
            }

            return {
                type: 'lamination',
                lamination_type: laminationType,
                sides: sides,
                value_id: item.value_id,
                key_id: item.key_id
            };
        }

        if (value.includes('fold')) {
            return {
                type: 'fold',
                fold_type: value.includes('complex') ? 'complex' : 'standard',
                fold_count: 1,
                value_id: item.value_id,
                key_id: item.key_id
            };
        }

        if (value.includes('die') || value.includes('cut')) {
            return {
                type: 'die-cut',
                custom_shape: value.includes('custom'),
                value_id: item.value_id,
                key_id: item.key_id
            };
        }

        // Generic finishing
        return {
            type: 'finishing',
            name: item.value,
            value_id: item.value_id,
            key_id: item.key_id
        };
    }

    /**
     * Transform V2 response back to V1 format
     *
     * This ensures backward compatibility for clients expecting V1 format.
     *
     * @param {Object} v2Response - V2 calculation response
     * @param {Object} v1Payload - Original V1 payload
     * @returns {Object} V1 format response
     */
    static transformResponseToV1(v2Response, v1Payload) {
        // V2 response already has most fields V1 clients expect
        // Just ensure it matches V1 structure

        return {
            type: v2Response.type || 'print',
            connection: v2Response.connection,
            external: v2Response.external || '',
            external_id: v2Response.external_id,
            external_name: v2Response.external_name,
            calculation_type: v2Response.calculation_type || 'full_calculation',
            items: v1Payload.product, // Keep original items
            product: v1Payload.product, // Keep original product
            category: v2Response.category,
            margins: v2Response.prices[0]?.margins || [],
            divided: v1Payload.divided || false,
            quantity: v2Response.configuration.quantity,
            calculation: v2Response.machines || [], // Machine results
            prices: v2Response.prices,
            // Add V2 enhancements as optional fields
            v2_enhanced: true,
            v2_configuration: v2Response.configuration,
            v2_machines_calculated: v2Response.machines_calculated
        };
    }
}

module.exports = V1toV2PayloadTransformer;
