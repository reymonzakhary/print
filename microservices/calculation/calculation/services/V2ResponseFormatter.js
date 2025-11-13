/**
 * V2ResponseFormatter
 *
 * Creates detailed, transparent V2 calculation responses.
 * Shows HOW calculations happened, not just the result.
 *
 * Includes:
 * - Material selection process
 * - Machine selection reasoning
 * - Color breakdown (front/back)
 * - Sheet calculations step-by-step
 * - Lamination details
 * - Cost breakdown at each step
 */
class V2ResponseFormatter {
    /**
     * Format complete V2 response with full calculation details
     *
     * @param {Object} calculationResult - Complete calculation result
     * @param {Object} context - Calculation context
     * @returns {Object} Detailed V2 response
     */
    formatDetailedResponse(calculationResult, context) {
        const { isDivided, dividedResult, finalPrice, formatResult, catalogue, category, margins } = calculationResult;

        if (isDivided) {
            return this._formatDividedDetailedResponse(dividedResult, context, category, margins);
        } else {
            return this._formatSimpleDetailedResponse(finalPrice, context, formatResult, catalogue, category, margins);
        }
    }

    /**
     * Format detailed response for simple (non-divided) calculation
     *
     * @private
     */
    _formatSimpleDetailedResponse(finalPrice, context, formatResult, catalogue, category, margins) {
        const cheapest = finalPrice.cheapest;
        const calculation = cheapest.calculation;
        const machine = cheapest.machine;
        const color = cheapest.color;

        return {
            // Meta
            version: '2.0',
            calculation_type: 'full_calculation',
            divided: false,
            timestamp: new Date().toISOString(),

            // Summary (quick overview)
            summary: {
                total_cost: this._formatMoney(cheapest.row_price),
                cost_per_piece: this._formatMoney(cheapest.row_price / context.quantity),
                quantity: context.quantity,
                production_time: this._formatDuration(cheapest.duration),
                machine_used: machine.name,
                machine_type: machine.type
            },

            // 📄 Product Configuration
            product: {
                category: {
                    name: category.name,
                    slug: category.slug,
                    type: 'print'
                },
                specifications: this._extractSpecifications(context.matchedItems),
                quantity: context.quantity
            },

            // 📐 Format Calculation
            format_calculation: {
                format_selected: {
                    name: formatResult.format.name || 'Custom',
                    dimensions: {
                        width: formatResult.width,
                        height: formatResult.height,
                        unit: 'mm'
                    },
                    bleed: formatResult.bleed,
                    area_sqm: formatResult.format.size?.m || 0
                },
                pages: formatResult.pages,
                sheets_per_product: formatResult.format.sheets || 1
            },

            // 📦 Material Selection
            material_selection: {
                material: this._extractMaterialInfo(catalogue, context.matchedItems),
                paper_specification: {
                    weight: this._extractWeight(context.matchedItems),
                    type: this._extractMaterialType(context.matchedItems),
                    finish: this._extractFinish(context.matchedItems)
                },
                paper_details: catalogue.results[0] ? {
                    supplier: catalogue.results[0].supplier || 'Default',
                    thickness: catalogue.results[0].thickness || 0,
                    density: catalogue.results[0].density || 0,
                    gsm: catalogue.results[0].grs || 0,
                    price_per_kg: this._formatMoney((catalogue.results[0].price || 0) / 100)
                } : null
            },

            // 🖨️ Machine Selection
            machine_selection: {
                machine_chosen: {
                    id: machine._id.toString(),
                    name: machine.name,
                    type: machine.type,
                    print_method: machine.pm || 'digital'
                },
                machine_specifications: {
                    sheet_size: {
                        width: machine.width,
                        height: machine.height,
                        unit: 'mm'
                    },
                    speed: {
                        sheets_per_minute: machine.spm || 0,
                        sheets_per_hour: (machine.spm || 0) * 60
                    },
                    capabilities: {
                        min_gsm: machine.min_gsm || 0,
                        max_gsm: machine.max_gsm || 0,
                        fed: machine.fed || 'sheet'
                    }
                },
                why_this_machine: this._getMachineSelectionReason(machine, formatResult, category)
            },

            // 🎨 Color Configuration
            color_configuration: {
                printing_colors: this._extractPrintingColors(context.matchedItems),
                sides: {
                    front: this._extractFrontColors(color),
                    back: this._extractBackColors(color),
                    total_sides: this._extractTotalSides(context.matchedItems)
                },
                color_details: color ? {
                    rpm: color.rpm || 0,
                    price_per_side: this._formatMoney((color.price || 0) / 100),
                    delivery_options: color.dlv || []
                } : null
            },

            // 📊 Sheet Calculations (step-by-step)
            sheet_calculations: {
                step_1_layout: {
                    description: 'How many products fit on one sheet',
                    machine_sheet_size: {
                        width: machine.width,
                        height: machine.height
                    },
                    product_size_with_bleed: {
                        width: calculation.width_with_bleed || 0,
                        height: calculation.height_with_bleed || 0
                    },
                    products_per_sheet: calculation.maximum_prints_per_sheet || 0,
                    layout_orientation: calculation.ps || 'Unknown'
                },
                step_2_quantity: {
                    description: 'Calculate sheets needed for quantity',
                    products_wanted: context.quantity,
                    products_per_sheet: calculation.maximum_prints_per_sheet || 0,
                    sheets_needed: calculation.amount_of_sheets_needed || 0
                },
                step_3_spoilage: {
                    description: 'Add waste/spoilage for setup and testing',
                    spoilage_percentage: machine.spoilage || 0,
                    sheets_before_spoilage: calculation.amount_of_sheets_needed || 0,
                    spoilage_sheets: (calculation.amount_of_sheets_with_spoilage || 0) - (calculation.amount_of_sheets_needed || 0),
                    sheets_with_spoilage: calculation.amount_of_sheets_with_spoilage || 0
                },
                step_4_printing: {
                    description: 'Total sheets to print',
                    sheets_to_print: calculation.amount_of_sheets_printed || calculation.amount_of_sheets_with_spoilage || 0
                }
            },

            // 💰 Cost Breakdown (detailed)
            cost_breakdown: {
                printing: {
                    description: 'Cost of printing sheets',
                    sheets: calculation.amount_of_sheets_printed || 0,
                    price_per_sheet: this._formatMoney((calculation.price_per_sheet || 0) / 100),
                    total: this._formatMoney((calculation.total_sheet_price || 0) / 100)
                },
                paper: {
                    description: 'Cost of paper/substrate',
                    sheets: calculation.amount_of_sheets_with_spoilage || 0,
                    area_per_sheet_sqm: calculation.sheet_in_sqm || 0,
                    total_area_sqm: (calculation.sheet_in_sqm || 0) * (calculation.amount_of_sheets_with_spoilage || 0),
                    price_per_sqm: this._formatMoney((calculation.price_sqm || 0) / 100),
                    total: this._formatMoney(((calculation.price_per_sheet || 0) * (calculation.amount_of_sheets_with_spoilage || 0)) / 100)
                },
                setup: {
                    description: 'Machine setup cost',
                    setup_time_minutes: machine.setup_time || 0,
                    total: this._formatMoney((calculation.start_cost || 0) / 100)
                },
                color: color ? {
                    description: 'Color printing cost',
                    total: this._formatMoney((color.price || 0) / 100)
                } : null,
                lamination: cheapest.laminate_machine ? this._extractLaminationCost(cheapest) : null,
                options: this._extractOptionsCost(cheapest),
                subtotal: this._formatMoney(cheapest.row_price)
            },

            // ⏱️ Production Timeline
            production_timeline: cheapest.duration ? {
                setup: {
                    time_minutes: cheapest.duration.setup_time || 0,
                    description: 'Machine setup and calibration'
                },
                printing: {
                    time_minutes: cheapest.duration.print_time || 0,
                    sheets: calculation.amount_of_sheets_printed || 0,
                    description: `Printing ${calculation.amount_of_sheets_printed || 0} sheets`
                },
                cooling: cheapest.duration.cooling_time > 0 ? {
                    time_minutes: cheapest.duration.cooling_time,
                    description: 'Cooling/drying time'
                } : null,
                lamination: cheapest.duration.lamination_time > 0 ? {
                    time_minutes: cheapest.duration.lamination_time,
                    sheets: calculation.amount_of_sheets_printed || 0,
                    machine: cheapest.laminate_machine?.name,
                    description: `Laminating ${calculation.amount_of_sheets_printed || 0} sheets`
                } : null,
                finishing: cheapest.duration.finishing_time > 0 ? {
                    time_minutes: cheapest.duration.finishing_time,
                    description: 'Finishing operations'
                } : null,
                total: {
                    time_minutes: cheapest.duration.total_time || 0,
                    time_hours: cheapest.duration.total_hours || 0,
                    estimated_delivery_days: cheapest.duration.estimated_delivery_days || 1
                }
            } : null,

            // 🎯 Final Pricing
            pricing: {
                gross_price: this._formatMoney(cheapest.row_price),
                price_per_piece: this._formatMoney(cheapest.row_price / context.quantity),
                margins_applied: margins.length > 0 && context.internal ? this._formatMargins(margins[0]) : null,
                vat: {
                    percentage: context.vat || 21,
                    amount: this._formatMoney(cheapest.row_price * (context.vat || 21) / 100)
                },
                total_with_vat: this._formatMoney(cheapest.row_price * (1 + (context.vat || 21) / 100)),
                delivery_options: color?.dlv || []
            },

            // 💵 Prices (V1-compatible format with delivery days)
            prices: this._formatPricesArray(cheapest, context, margins),

            // 📋 Price List (quantity ranges)
            price_list: this._formatPriceList(calculation.price_list, context),

            // 📐 Impositioning (sheet layout and print planning)
            impositioning: this._formatImpositioning(calculation, machine, formatResult, context.matchedItems),

            // 🔧 Calculation Details (for debugging)
            calculation_details: {
                calculation_method: category.calculation_method,
                price_list: calculation.price_list || [],
                all_positions: calculation.position || []
            }
        };
    }

    /**
     * Format detailed response for divided calculation
     *
     * @private
     */
    _formatDividedDetailedResponse(dividedResult, context, category, margins) {
        const divisions = dividedResult.divisions;
        const combined = dividedResult.combined;

        return {
            // Meta
            version: '2.0',
            calculation_type: 'divided_calculation',
            divided: true,
            timestamp: new Date().toISOString(),

            // Summary
            summary: {
                total_cost: this._formatMoney(combined.total_row_price),
                cost_per_piece: this._formatMoney(combined.total_row_price / context.quantity),
                quantity: context.quantity,
                production_time: this._formatDuration(combined.duration),
                divisions: divisions.length
            },

            // 📑 Divisions (Cover, Content, etc.)
            divisions: divisions.map(division => this._formatDivisionDetailed(division, context)),

            // 💰 Combined Totals
            totals: {
                all_divisions_cost: this._formatMoney(combined.total_row_price),
                production_time: this._formatDuration(combined.duration),
                breakdown_by_division: divisions.map(d => ({
                    name: d.name,
                    cost: this._formatMoney(d.row_price)
                }))
            },

            // 🎯 Final Pricing
            pricing: {
                gross_price: this._formatMoney(combined.total_row_price),
                price_per_piece: this._formatMoney(combined.total_row_price / context.quantity),
                margins_applied: margins.length > 0 && context.internal ? this._formatMargins(margins[0]) : null,
                vat: {
                    percentage: context.vat || 21,
                    amount: this._formatMoney(combined.total_row_price * (context.vat || 21) / 100)
                },
                total_with_vat: this._formatMoney(combined.total_row_price * (1 + (context.vat || 21) / 100))
            },

            // 💵 Prices (V1-compatible format with delivery days)
            prices: this._formatDividedPricesArray(divisions, combined, context, margins),

            // 📐 Impositioning (per division)
            impositioning: divisions.map(division => ({
                division: division.name,
                layout: this._formatImpositioning(division.calculation, division.machine, { format: division.calculation }, division.items)
            }))
        };
    }

    /**
     * Format single division with full details
     *
     * @private
     */
    _formatDivisionDetailed(division, context) {
        const calculation = division.calculation;
        const machine = division.machine;

        return {
            name: division.name,
            divider: division.divider,

            // Items in this division
            items: division.items.map(item => ({
                key: item.key,
                value: item.value,
                display: item.option?.display_name?.[0]?.display_name || item.option?.name || item.value,
                calc_ref: item.box_calc_ref
            })),

            // Machine used for this division
            machine: {
                name: machine.name,
                type: machine.type,
                specifications: {
                    sheet_size: `${machine.width}x${machine.height}mm`,
                    speed_spm: machine.spm || 0
                }
            },

            // Lamination (if applicable)
            lamination: division.laminate_machine ? {
                machine: {
                    id: division.laminate_machine._id?.toString() || division.laminate_machine.id?.toString(),
                    name: division.laminate_machine.name,
                    type: division.laminate_machine.type || 'lamination',
                    specifications: {
                        width: division.laminate_machine.width || 0,
                        height: division.laminate_machine.height || 0,
                        speed_spm: division.laminate_machine.spm || 0
                    }
                },
                cost: this._formatMoney(division.costs.lamination || 0)
            } : null,

            // Sheet calculations for this division
            sheets: {
                needed: division.sheets.needed,
                with_spoilage: division.sheets.with_spoilage,
                products_per_sheet: division.sheets.products_per_sheet
            },

            // Cost breakdown
            costs: {
                printing: this._formatMoney(division.costs.printing),
                lamination: division.costs.lamination > 0 ? this._formatMoney(division.costs.lamination) : null,
                options: division.costs.options > 0 ? this._formatMoney(division.costs.options) : null,
                subtotal: this._formatMoney(division.costs.subtotal)
            },

            // Timing
            timing: division.duration ? this._formatDuration(division.duration) : null
        };
    }

    // Helper methods
    _formatMoney(amount) {
        return {
            value: parseFloat(amount.toFixed(2)),
            currency: 'EUR',
            formatted: `€${amount.toFixed(2)}`
        };
    }

    _formatDuration(duration) {
        if (!duration) return null;
        return {
            minutes: duration.total_time || 0,
            hours: duration.total_hours || 0,
            estimated_days: duration.estimated_delivery_days || 1,
            formatted: `${duration.total_hours || 0}h (${duration.estimated_delivery_days || 1} days)`
        };
    }

    _extractSpecifications(items) {
        const specs = {};
        for (const item of items) {
            specs[item.key] = {
                value: item.value,
                display: item.option?.display_name?.[0]?.display_name || item.option?.name || item.value
            };
        }
        return specs;
    }

    _extractMaterialInfo(catalogue, items) {
        const materialItem = items.find(i => i.box_calc_ref === 'material');
        return materialItem ? {
            name: materialItem.option?.name || materialItem.value,
            slug: materialItem.value
        } : null;
    }

    _extractWeight(items) {
        const weightItem = items.find(i => i.box_calc_ref === 'weight');
        return weightItem ? weightItem.option?.name || weightItem.value : null;
    }

    _extractMaterialType(items) {
        const materialItem = items.find(i => i.box_calc_ref === 'material');
        return materialItem ? materialItem.option?.name || materialItem.value : null;
    }

    _extractFinish(items) {
        const laminationItem = items.find(i => i.box_calc_ref === 'lamination');
        return laminationItem ? laminationItem.option?.name || laminationItem.value : null;
    }

    _extractPrintingColors(items) {
        const colorsItem = items.find(i => i.box_calc_ref === 'printing_colors');
        return colorsItem ? {
            name: colorsItem.option?.name || colorsItem.value,
            display: colorsItem.option?.display_name?.[0]?.display_name || colorsItem.option?.name
        } : null;
    }

    _extractFrontColors(color) {
        if (!color || !color.run) return 0;
        // Parse from color name (e.g., "4/4" means 4 front, 4 back)
        const colorName = color.run[0]?.name || '';
        const match = colorName.match(/(\d+)\/(\d+)/);
        return match ? parseInt(match[1]) : 4;
    }

    _extractBackColors(color) {
        if (!color || !color.run) return 0;
        const colorName = color.run[0]?.name || '';
        const match = colorName.match(/(\d+)\/(\d+)/);
        return match ? parseInt(match[2]) : 4;
    }

    _extractTotalSides(items) {
        const sidesItem = items.find(i => i.box_calc_ref === 'sides');
        if (sidesItem) {
            const sidesMatch = sidesItem.value.match(/(\d+)/);
            return sidesMatch ? parseInt(sidesMatch[1]) : 2;
        }
        return 2;
    }

    _getMachineSelectionReason(machine, formatResult, category) {
        return `Machine selected based on product size (${formatResult.width}x${formatResult.height}mm), ` +
               `material weight compatibility (${machine.min_gsm}-${machine.max_gsm} gsm), ` +
               `and best cost efficiency for quantity.`;
    }

    _extractLaminationCost(cheapest) {
        // Check if lamination machine is present
        if (!cheapest.laminate_machine) {
            return null;
        }

        const laminateMachine = cheapest.laminate_machine;
        let laminationCost = 0;

        // Try to get cost from options_cost breakdown
        if (cheapest.options_cost && cheapest.options_cost.breakdown) {
            const laminationOption = cheapest.options_cost.breakdown.find(
                opt => opt.type === 'option' && (opt.key === 'lamination' || opt.box_calc_ref === 'lamination')
            );
            if (laminationOption) {
                laminationCost = laminationOption.total_cost || 0;
            }
        }

        // Build detailed lamination section with machine info
        return {
            description: 'Lamination finishing applied',
            machine: {
                id: laminateMachine._id?.toString() || laminateMachine.id?.toString(),
                name: laminateMachine.name,
                type: laminateMachine.type || 'lamination',
                specifications: {
                    width: laminateMachine.width || 0,
                    height: laminateMachine.height || 0,
                    speed_spm: laminateMachine.spm || 0
                }
            },
            cost: this._formatMoney(laminationCost),
            total: this._formatMoney(laminationCost)
        };
    }

    _extractOptionsCost(cheapest) {
        if (!cheapest.options_cost || !cheapest.options_cost.breakdown) {
            return null;
        }

        const options = cheapest.options_cost.breakdown
            .filter(opt => opt.type === 'option')
            .map(opt => ({
                name: opt.name,
                cost: this._formatMoney(opt.total_cost || 0)
            }));

        return options.length > 0 ? {
            description: 'Additional finishing options',
            items: options,
            total: this._formatMoney(cheapest.options_cost.total || 0)
        } : null;
    }

    _formatMargins(margin) {
        return {
            type: margin.type,
            value: margin.value,
            description: margin.type === 'percentage' ? `${margin.value}% margin` : `€${margin.value} fixed margin`
        };
    }

    /**
     * Format prices array in V1-compatible format with delivery days
     *
     * @private
     */
    _formatPricesArray(cheapest, context, margins) {
        const grossPrice = Math.round(cheapest.row_price * 100); // In cents
        const grossPpp = Math.round((cheapest.row_price / context.quantity) * 100);
        const vatRate = context.vat || 21;
        const vatAmount = Math.round(grossPrice * vatRate / 100);

        // Build delivery options with days
        const deliveryOptions = (cheapest.color?.dlv || cheapest.dlv || []).map(dlvOption => ({
            days: dlvOption.days || dlvOption.dlv || 0,
            price: dlvOption.price || 0,
            name: dlvOption.name || `${dlvOption.days || dlvOption.dlv || 0} days`
        }));

        // Generate unique ID for this price combination
        const priceId = this._generatePriceId(cheapest, context.quantity);

        return [{
            id: priceId,
            qty: context.quantity,
            dlv: deliveryOptions,
            pm: cheapest.machine?.pm || 'digital',
            gross_price: grossPrice,
            gross_ppp: grossPpp / 100,
            p: grossPrice,
            ppp: grossPpp / 100,
            selling_price_ex: grossPrice,
            selling_price_inc: grossPrice + vatAmount,
            profit: margins.length > 0 && context.internal ? this._calculateProfit(grossPrice, margins[0]) : null,
            discount: [],
            margins: margins.length > 0 && context.internal ? [this._formatMarginForPrice(margins[0])] : [],
            vat: vatRate,
            vat_p: vatAmount / 100,
            vat_ppp: (vatAmount / context.quantity) / 100,
            delivery_days: cheapest.duration?.estimated_delivery_days || 1
        }];
    }

    /**
     * Format prices array for divided calculations
     *
     * @private
     */
    _formatDividedPricesArray(divisions, combined, context, margins) {
        const grossPrice = Math.round(combined.total_row_price * 100); // In cents
        const grossPpp = Math.round((combined.total_row_price / context.quantity) * 100);
        const vatRate = context.vat || 21;
        const vatAmount = Math.round(grossPrice * vatRate / 100);

        // Get delivery options from first division
        const firstDivision = divisions[0];
        const deliveryOptions = (firstDivision?.machine?.dlv || []).map(dlvOption => ({
            days: dlvOption.days || dlvOption.dlv || 0,
            price: dlvOption.price || 0,
            name: dlvOption.name || `${dlvOption.days || dlvOption.dlv || 0} days`
        }));

        // Generate unique ID for this price combination
        const priceId = this._generatePriceId({
            machine: firstDivision?.machine,
            row_price: combined.total_row_price
        }, context.quantity);

        return [{
            id: priceId,
            qty: context.quantity,
            dlv: deliveryOptions,
            pm: firstDivision?.machine?.pm || 'digital',
            gross_price: grossPrice,
            gross_ppp: grossPpp / 100,
            p: grossPrice,
            ppp: grossPpp / 100,
            selling_price_ex: grossPrice,
            selling_price_inc: grossPrice + vatAmount,
            profit: margins.length > 0 && context.internal ? this._calculateProfit(grossPrice, margins[0]) : null,
            discount: [],
            margins: margins.length > 0 && context.internal ? [this._formatMarginForPrice(margins[0])] : [],
            vat: vatRate,
            vat_p: vatAmount / 100,
            vat_ppp: (vatAmount / context.quantity) / 100,
            delivery_days: combined.duration?.estimated_days || 1,
            divided: true,
            divisions_count: divisions.length
        }];
    }

    /**
     * Format price list for quantity ranges
     *
     * @private
     */
    _formatPriceList(priceList, context) {
        if (!priceList || priceList.length === 0) {
            return null;
        }

        return priceList.map(priceItem => ({
            quantity: priceItem.quantity || priceItem.qty,
            price: priceItem.price,
            price_per_piece: priceItem.price_per_piece || (priceItem.price / priceItem.quantity),
            delivery_days: priceItem.delivery_days || 1,
            formatted: {
                price: this._formatMoney(priceItem.price / 100),
                price_per_piece: this._formatMoney((priceItem.price / priceItem.quantity) / 100)
            }
        }));
    }

    /**
     * Format impositioning (professional print imposition details)
     *
     * @private
     */
    _formatImpositioning(calculation, machine, formatResult, items) {
        const productsPerSheet = calculation.maximum_prints_per_sheet || 0;
        const bindingMethod = this._extractBindingMethod(items);
        const impositionType = this._determineImpositionType(calculation, bindingMethod);

        return {
            // Sheet configuration
            sheet: {
                size: {
                    width: machine.width,
                    height: machine.height,
                    unit: 'mm',
                    area_sqm: (machine.width * machine.height) / 1000000
                },
                type: machine.fed || 'sheet-fed',
                orientation: calculation.sheet_orientation || 'portrait'
            },

            // Product/trim size (with bleed)
            product: {
                trim_size: {
                    width: formatResult?.width || calculation.width || 0,
                    height: formatResult?.height || calculation.height || 0,
                    unit: 'mm'
                },
                size_with_bleed: {
                    width: calculation.width_with_bleed || 0,
                    height: calculation.height_with_bleed || 0,
                    unit: 'mm'
                },
                bleed: formatResult?.bleed || 0,
                orientation: calculation.ps || 'portrait'
            },

            // Imposition type and method
            imposition: {
                type: impositionType,
                method: this._getImpositionMethod(calculation),
                work_style: calculation.work_style || 'sheetwise',
                products_per_sheet: productsPerSheet,
                pages_per_product: formatResult?.pages || 1,
                total_pages_per_sheet: productsPerSheet * (formatResult?.pages || 1)
            },

            // Layout pattern (step-and-repeat)
            layout: {
                pattern: 'step-and-repeat',
                rows: calculation.rows || Math.ceil(Math.sqrt(productsPerSheet)),
                columns: calculation.columns || Math.ceil(productsPerSheet / Math.ceil(Math.sqrt(productsPerSheet))),
                rotation: calculation.rotation || 0,
                orientation: calculation.ps || 'optimized',
                gutter: {
                    horizontal: 3, // Standard 3mm gutter
                    vertical: 3,
                    unit: 'mm'
                }
            },

            // Binding and folding
            binding: {
                method: bindingMethod,
                spine_edge: this._getSpineEdge(bindingMethod),
                binding_direction: this._extractBindingDirection(items),
                folding_pattern: this._getFoldingPattern(formatResult, bindingMethod)
            },

            // Print marks and guides
            print_marks: {
                crop_marks: true,
                bleed_marks: formatResult?.bleed > 0,
                registration_marks: true,
                color_bars: true,
                fold_marks: bindingMethod ? true : false,
                cutting_guides: true
            },

            // Efficiency metrics
            efficiency: {
                sheet_usage_percentage: this._calculateSheetEfficiency(calculation, machine),
                waste_area_sqm: this._calculateWasteArea(calculation, machine),
                material_efficiency_rating: this._getMaterialEfficiencyRating(
                    this._calculateSheetEfficiency(calculation, machine)
                )
            },

            // Visualization and description
            visualization: {
                description: `${productsPerSheet} products arranged in step-and-repeat pattern on ${machine.width}x${machine.height}mm sheet`,
                layout_description: this._getLayoutDescription(calculation, productsPerSheet, bindingMethod),
                optimization_notes: this._getOptimizationNotes(calculation, machine)
            }
        };
    }

    /**
     * Generate unique price ID
     *
     * @private
     */
    _generatePriceId(cheapest, quantity) {
        const crypto = require('crypto');
        const hash = crypto.createHash('sha256');
        hash.update(`${cheapest.machine._id}-${quantity}-${cheapest.row_price}`);
        return hash.digest('hex');
    }

    /**
     * Calculate profit from margins
     *
     * @private
     */
    _calculateProfit(grossPrice, margin) {
        if (!margin) return null;

        if (margin.type === 'percentage') {
            return Math.round(grossPrice * margin.value / 100) / 100;
        } else {
            return margin.value;
        }
    }

    /**
     * Format margin for price object
     *
     * @private
     */
    _formatMarginForPrice(margin) {
        return {
            type: margin.type,
            value: margin.value,
            amount: margin.type === 'percentage' ? null : margin.value
        };
    }

    /**
     * Calculate sheet efficiency percentage
     *
     * @private
     */
    _calculateSheetEfficiency(calculation, machine) {
        const sheetArea = (machine.width * machine.height) / 1000000; // sqm
        const productArea = ((calculation.width_with_bleed || 0) * (calculation.height_with_bleed || 0)) / 1000000; // sqm
        const productsPerSheet = calculation.maximum_prints_per_sheet || 0;
        const usedArea = productArea * productsPerSheet;

        return sheetArea > 0 ? Math.round((usedArea / sheetArea) * 100 * 10) / 10 : 0;
    }

    /**
     * Calculate waste area per sheet
     *
     * @private
     */
    _calculateWasteArea(calculation, machine) {
        const sheetArea = (machine.width * machine.height) / 1000000; // sqm
        const productArea = ((calculation.width_with_bleed || 0) * (calculation.height_with_bleed || 0)) / 1000000; // sqm
        const productsPerSheet = calculation.maximum_prints_per_sheet || 0;
        const usedArea = productArea * productsPerSheet;

        return Math.round((sheetArea - usedArea) * 10000) / 10000; // 4 decimal places
    }

    /**
     * Extract binding method from items
     *
     * @private
     */
    _extractBindingMethod(items) {
        if (!items || !Array.isArray(items)) return null;

        const bindingItem = items.find(i =>
            i.box_calc_ref === 'binding_method' ||
            i.key === 'binding_method' ||
            i.key === 'binding-method'
        );

        return bindingItem?.option?.name || bindingItem?.value || null;
    }

    /**
     * Extract binding direction from items
     *
     * @private
     */
    _extractBindingDirection(items) {
        if (!items || !Array.isArray(items)) return null;

        const directionItem = items.find(i =>
            i.box_calc_ref === 'binding_direction' ||
            i.key === 'binding_direction' ||
            i.key === 'binding-direction'
        );

        return directionItem?.option?.name || directionItem?.value || 'left';
    }

    /**
     * Determine imposition type based on calculation and binding
     *
     * @private
     */
    _determineImpositionType(calculation, bindingMethod) {
        if (bindingMethod) {
            if (bindingMethod.toLowerCase().includes('saddle')) {
                return 'saddle-stitch';
            } else if (bindingMethod.toLowerCase().includes('perfect')) {
                return 'perfect-bound';
            } else if (bindingMethod.toLowerCase().includes('wire')) {
                return 'wire-o';
            } else if (bindingMethod.toLowerCase().includes('spiral')) {
                return 'spiral';
            } else {
                return 'book-imposition';
            }
        }

        // For non-book products
        return 'step-and-repeat';
    }

    /**
     * Get imposition method
     *
     * @private
     */
    _getImpositionMethod(calculation) {
        // Determine work style based on calculation
        if (calculation.work_and_turn) {
            return 'work-and-turn';
        } else if (calculation.work_and_tumble) {
            return 'work-and-tumble';
        } else if (calculation.perfecting) {
            return 'perfecting';
        } else if (calculation.duplex) {
            return 'sheetwise-duplex';
        } else {
            return 'sheetwise';
        }
    }

    /**
     * Get spine edge for binding
     *
     * @private
     */
    _getSpineEdge(bindingMethod) {
        if (!bindingMethod) return null;

        const method = bindingMethod.toLowerCase();

        if (method.includes('left')) {
            return 'left';
        } else if (method.includes('right')) {
            return 'right';
        } else if (method.includes('top')) {
            return 'top';
        } else if (method.includes('bottom')) {
            return 'bottom';
        }

        // Default for most bindings
        return 'left';
    }

    /**
     * Get folding pattern based on format and binding
     *
     * @private
     */
    _getFoldingPattern(formatResult, bindingMethod) {
        if (!bindingMethod) return null;

        const pages = formatResult?.pages || formatResult?.num_pages || 0;

        if (pages <= 4) {
            return 'half-fold';
        } else if (pages <= 8) {
            return 'accordion-fold';
        } else if (pages <= 16) {
            return '8-page-signature';
        } else if (pages <= 32) {
            return '16-page-signature';
        } else {
            return 'multi-signature';
        }
    }

    /**
     * Get material efficiency rating
     *
     * @private
     */
    _getMaterialEfficiencyRating(percentage) {
        if (percentage >= 90) return 'excellent';
        if (percentage >= 80) return 'good';
        if (percentage >= 70) return 'fair';
        if (percentage >= 60) return 'acceptable';
        return 'poor';
    }

    /**
     * Get layout description
     *
     * @private
     */
    _getLayoutDescription(calculation, productsPerSheet, bindingMethod) {
        const rows = calculation.rows || Math.ceil(Math.sqrt(productsPerSheet));
        const columns = calculation.columns || Math.ceil(productsPerSheet / rows);

        let description = `${rows}x${columns} grid layout`;

        if (bindingMethod) {
            description += ` with ${bindingMethod.toLowerCase()} binding preparation`;
        }

        if (calculation.rotation) {
            description += `, rotated ${calculation.rotation}°`;
        }

        return description;
    }

    /**
     * Get optimization notes
     *
     * @private
     */
    _getOptimizationNotes(calculation, machine) {
        const notes = [];

        const efficiency = this._calculateSheetEfficiency(calculation, machine);

        if (efficiency >= 85) {
            notes.push('Excellent material utilization - minimal waste');
        } else if (efficiency < 70) {
            notes.push('Consider alternative sheet size for better efficiency');
        }

        const productsPerSheet = calculation.maximum_prints_per_sheet || 0;
        if (productsPerSheet === 1) {
            notes.push('Single product per sheet - consider smaller sheet size if available');
        } else if (productsPerSheet >= 8) {
            notes.push('High density layout - verify cutting precision');
        }

        if (calculation.width_with_bleed && calculation.height_with_bleed) {
            const productSize = calculation.width_with_bleed * calculation.height_with_bleed;
            const sheetSize = machine.width * machine.height;

            if (productSize / sheetSize < 0.3) {
                notes.push('Small product on large sheet - gang with other jobs for efficiency');
            }
        }

        if (notes.length === 0) {
            notes.push('Standard imposition layout optimized for production');
        }

        return notes;
    }
}

module.exports = V2ResponseFormatter;
