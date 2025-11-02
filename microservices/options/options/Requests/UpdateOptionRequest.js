const mongoose = require('mongoose');
const ObjectId = mongoose.Types.ObjectId;

module.exports = class UpdateOptionRequest {

    /**
     * Prepare additional field - only include if calc_ref has a value
     * @param {Object} requestAdditional - Additional from request
     * @param {Object} existingAdditional - Existing additional from option
     * @returns {Object} - Cleaned additional object
     */
    prepareAdditional(requestAdditional, existingAdditional = {}) {
        if (!requestAdditional) {
            return existingAdditional;
        }

        // If calc_ref is null, undefined, or empty string, return empty object
        if (!requestAdditional.calc_ref) {
            return {};
        }

        // Return with both calc_ref and calc_ref_type
        return {
            calc_ref: requestAdditional.calc_ref,
            calc_ref_type: requestAdditional.calc_ref_type || existingAdditional.calc_ref_type || ''
        };
    }

    /**
     * Prepare configure additional field
     * @param {Object} requestAdditional - Additional from request
     * @param {Object} existingAdditional - Existing additional from configure
     * @returns {Object} - Cleaned additional object
     */
    prepareConfigureAdditional(requestAdditional, existingAdditional = {}) {
        if (!requestAdditional) {
            return existingAdditional;
        }

        // If calc_ref is null, undefined, or empty string, return empty object
        if (!requestAdditional.calc_ref) {
            return {};
        }

        // Return with both calc_ref and calc_ref_type
        return {
            calc_ref: requestAdditional.calc_ref,
            calc_ref_type: requestAdditional.calc_ref_type || existingAdditional.calc_ref_type || ''
        };
    }

    /**
     * Prepare run items with all required fields
     * @param {Array} runItems - Array of run items from request
     * @returns {Array} - Array of properly formatted run items
     */
    prepareRunItems(runItems) {
        if (!Array.isArray(runItems)) {
            return [];
        }

        return runItems.map(item => ({
            from: item.from ?? 1,
            to: item.to ?? 1,
            price: item.price ?? 0,
            pm: item.pm ?? 'all',
            dlv_production: item.dlv_production ?? []
        }));
    }

    /**
     * Update runs information for a specific category
     * @param {Array} runs - Current runs array from option
     * @param {Array|null} newRuns - New runs data from request
     * @param {string} categoryId - The category ID to update
     * @param {number|null} startCost - New start cost value
     * @returns {Array} - Updated runs array
     */
    updateRuns(runs, newRuns, categoryId, startCost) {
        const categoryExists = runs.some(run => run.category_id.toString() === categoryId.toString());

        if (!categoryExists && newRuns !== undefined && newRuns !== null) {
            // Add new category runs if it doesn't exist
            return [
                ...runs,
                {
                    category_id: new ObjectId(categoryId),
                    start_cost: startCost ?? 0,
                    runs: this.prepareRunItems(newRuns)
                }
            ];
        }

        // Update existing category runs
        return runs.map(run => {
            if (run.category_id.toString() === categoryId.toString()) {
                return {
                    category_id: run.category_id,
                    start_cost: startCost !== undefined && startCost !== null ? startCost : run.start_cost,
                    runs: newRuns !== undefined && newRuns !== null ? this.prepareRunItems(newRuns) : run.runs
                };
            }
            return run;
        });
    }

    /**
     * Update configure information for a specific category
     * @param {Array} configures - Current configure array from option
     * @param {Object} request - Request object containing new configure data
     * @param {string} categoryId - The category ID to update
     * @returns {Array} - Updated configure array
     */
    updateConfigure(configures, request, categoryId) {
        const categoryExists = configures.some(config => config.category_id.toString() === categoryId.toString());

        const newConfigureData = {
            incremental_by: request.incremental_by ?? 0,
            dimension: request.dimension ?? '2d',
            dynamic: request.dynamic ?? false,
            dynamic_keys: request.dynamic_keys ?? [],
            start_on: request.start_on ?? 0,
            end_on: request.end_on ?? 0,
            generate: request.generate ?? false,
            dynamic_type: request.dynamic_type ?? 'integer',
            unit: request.unit ?? 'mm',
            width: request.width ?? 0,
            maximum_width: request.maximum_width ?? 0,
            minimum_width: request.minimum_width ?? 0,
            height: request.height ?? 0,
            maximum_height: request.maximum_height ?? 0,
            minimum_height: request.minimum_height ?? 0,
            length: request.length ?? 0,
            maximum_length: request.maximum_length ?? 0,
            minimum_length: request.minimum_length ?? 0,
            start_cost: request.start_cost ?? 0,
            calculation_method: request.calculation_method ?? 'qty',
            additional: this.prepareConfigureAdditional(request.additional, {})
        };

        if (!categoryExists) {
            // Add new category configure if it doesn't exist
            return [
                ...configures,
                {
                    category_id: new ObjectId(categoryId),
                    configure: newConfigureData
                }
            ];
        }

        // Update existing category configure
        return configures.map(config => {
            if (config.category_id.toString() === categoryId.toString()) {
                return {
                    category_id: config.category_id,
                    configure: {
                        incremental_by: request.incremental_by ?? config.configure.incremental_by,
                        dimension: request.dimension ?? config.configure.dimension,
                        dynamic: request.dynamic ?? config.configure.dynamic,
                        dynamic_keys: request.dynamic_keys ?? config.configure.dynamic_keys,
                        start_on: request.start_on ?? config.configure.start_on,
                        end_on: request.end_on ?? config.configure.end_on,
                        generate: request.generate ?? config.configure.generate,
                        dynamic_type: request.dynamic_type ?? config.configure.dynamic_type,
                        unit: request.unit ?? config.configure.unit,
                        width: request.width ?? config.configure.width,
                        maximum_width: request.maximum_width ?? config.configure.maximum_width,
                        minimum_width: request.minimum_width ?? config.configure.minimum_width,
                        height: request.height ?? config.configure.height,
                        maximum_height: request.maximum_height ?? config.configure.maximum_height,
                        minimum_height: request.minimum_height ?? config.configure.minimum_height,
                        length: request.length ?? config.configure.length,
                        maximum_length: request.maximum_length ?? config.configure.maximum_length,
                        minimum_length: request.minimum_length ?? config.configure.minimum_length,
                        start_cost: request.start_cost ?? config.configure.start_cost,
                        calculation_method: request.calculation_method ?? config.configure.calculation_method,
                        additional: this.prepareConfigureAdditional(
                            request.additional,
                            config.configure.additional
                        )
                    }
                };
            }
            return config;
        });
    }

    /**
     * Prepare the option object for update
     * @param {Object} option - The current option from database
     * @param {Object} request - The request data
     * @param {Array} displayNames - The display names array
     * @param {string} categoryId - The category ID being updated
     * @returns {Object} - Prepared object for update
     */
    prepare(option, request, displayNames, categoryId) {
        return {
            sort: request.sort ?? option.sort,
            display_name: displayNames,
            description: request.description ?? option.description,
            information: request.information ?? option.information,
            media: request.media ?? option.media,
            input_type: request.input_type ?? option.input_type,
            extended_fields: request.extended_fields ?? option.extended_fields,
            sku: request.sku ?? option.sku,
            parent: request.parent ?? option.parent,

            // Update runs for specific category (handles both update and insert)
            runs: this.updateRuns(option.runs, request.runs, categoryId, request.start_cost),

            // Update configure for specific category (handles both update and insert)
            configure: this.updateConfigure(option.configure, request, categoryId),

            // Handle additional field properly - clean existing data
            additional: request.additional !== undefined
                ? this.prepareAdditional(request.additional, option.additional)
                : this.cleanAdditional(option.additional),

            // Always ensure linked is ObjectId or null
            linked: request.linked
                ? new ObjectId(request.linked)
                : (option.linked ? new ObjectId(option.linked) : null),

            incremental_by: request.incremental_by ?? option.incremental_by,
            has_children: request.has_children ?? option.has_children,
            shareable: request.shareable ?? option.shareable,
            published: request.published ?? option.published,
            rpm: request.rpm ?? option.rpm,
            sheet_runs: option.sheet_runs,
            boxes: request.boxes ?? option.boxes,

            // Root level dimension fields (kept for backward compatibility)
            dimension: request.dimension ?? option.dimension,
            dynamic: request.dynamic ?? option.dynamic,
            dynamic_keys: request.dynamic_keys ?? option.dynamic_keys,
            start_on: request.start_on ?? option.start_on,
            end_on: request.end_on ?? option.end_on,
            generate: request.generate ?? option.generate,
            dynamic_type: request.dynamic_type ?? option.dynamic_type,
            unit: request.unit ?? option.unit,
            width: request.width ?? option.width,
            maximum_width: request.maximum_width ?? option.maximum_width,
            minimum_width: request.minimum_width ?? option.minimum_width,
            height: request.height ?? option.height,
            maximum_height: request.maximum_height ?? option.maximum_height,
            minimum_height: request.minimum_height ?? option.minimum_height,
            length: request.length ?? option.length,
            maximum_length: request.maximum_length ?? option.maximum_length,
            minimum_length: request.minimum_length ?? option.minimum_length,
            start_cost: request.start_cost ?? option.start_cost,
            dynamic_object: request.dynamic_object ?? option.dynamic_object,
            calculation_method: request.calculation_method ?? option.calculation_method
        };
    }

    /**
     * Get the configure data for a specific category from the updated option
     * @param {Array} configures - The configure array
     * @param {string} categoryId - The category ID
     * @returns {Object|Array} - The configure data for the category or empty array
     */
    getConfigureForCategory(configures, categoryId) {
        const categoryConfig = configures.find(
            config => config.category_id.toString() === categoryId.toString()
        );
        return categoryConfig ? categoryConfig.configure : [];
    }

    /**
     * Get the runs data for a specific category
     * @param {Array} runs - The runs array
     * @param {string} categoryId - The category ID
     * @returns {Object} - The runs data for the category
     */
    getRunsForCategory(runs, categoryId) {
        const categoryRuns = runs.find(
            run => run.category_id.toString() === categoryId.toString()
        );

        if (!categoryRuns) {
            return {
                start_cost: 0,
                runs: []
            };
        }

        return {
            start_cost: categoryRuns.start_cost,
            runs: categoryRuns.runs
        };
    }

    /**
     * Flatten option to show only current category's configure and runs
     * @param {Object} option - The full option object
     * @param {string} categoryId - The category ID
     * @returns {Object} - Flattened option with category-specific data
     */
    flattenForCategory(option, categoryId) {
        const configureData = this.getConfigureForCategory(option.configure, categoryId);
        const runsData = this.getRunsForCategory(option.runs, categoryId);

        // Create flattened option object
        return {
            _id: option._id,
            tenant_name: option.tenant_name,
            tenant_id: option.tenant_id,
            name: option.name,
            display_name: option.display_name,
            slug: option.slug,
            system_key: option.system_key,
            description: option.description,
            information: option.information,
            media: option.media,
            sort: option.sort,
            published: option.published,
            has_children: option.has_children,
            input_type: option.input_type,
            extended_fields: option.extended_fields,
            linked: option.linked,
            shareable: option.shareable,
            sku: option.sku,
            parent: option.parent,
            rpm: option.rpm,
            sheet_runs: option.sheet_runs,
            boxes: option.boxes,
            additional: option.additional,
            created_at: option.created_at,

            // Flattened category-specific runs
            start_cost: runsData.start_cost ?? 0,
            runs: runsData.runs ?? [],

            // Flattened category-specific configure (spread all configure fields to root)
            incremental_by: configureData.incremental_by ?? 0,
            dimension: configureData.dimension ?? '2d',
            dynamic: configureData.dynamic ?? false,
            dynamic_keys: configureData.dynamic_keys,
            start_on: configureData.start_on ?? 0,
            end_on: configureData.end_on ?? 0,
            generate: configureData.generate ?? false,
            dynamic_type: configureData.dynamic_type ?? '',
            unit: configureData.unit ?? 'mm',
            width: configureData.width,
            maximum_width: configureData.maximum_width,
            minimum_width: configureData.minimum_width,
            height: configureData.height,
            maximum_height: configureData.maximum_height,
            minimum_height: configureData.minimum_height,
            length: configureData.length,
            maximum_length: configureData.maximum_length,
            minimum_length: configureData.minimum_length,
            calculation_method: configureData.calculation_method ?? 'qty',
            dynamic_object: option.dynamic_object
        };
    }

    /**
     * Clean up additional field - convert { calc_ref: null } to {}
     * @param {Object} additional - The additional object to clean
     * @returns {Object} - Cleaned additional object
     */
    cleanAdditional(additional) {
        if (!additional || typeof additional !== 'object') {
            return {};
        }

        // If calc_ref is null, undefined, or empty, return empty object
        if (!additional.calc_ref) {
            return {};
        }

        // Return with both calc_ref and calc_ref_type
        return {
            calc_ref: additional.calc_ref,
            calc_ref_type: additional.calc_ref_type || ''
        };
    }
}