const {calculateFit} = require("../../Helpers/Helper");
/**
 * @typedef Promise
 * @property {Error} result Rejection error object
 */
module.exports = class PrintMachine {

    /**
     *
     * @param machine
     * @param {objectId} machine._id
     * @param {string} machine.name
     * @param {string} machine.ean
     * @param {number} machine.price
     * @param {number} machine.spoilage
     * @param {number} machine.height
     * @param {number} machine.width
     * @param {number} machine.margin_top
     * @param {number} machine.margin_bottom
     * @param {number} machine.margin_right
     * @param {number} machine.margin_left
     * @param {number} machine.trim_area_exclude_y
     * @param {number} machine.trim_area_exclude_x
     * @param {number} machine.trim_area
     * @param {boolean} machine.divide_start_cost
     * @param {number} machine.spm
     * @param {number} machine.mpm
     * @param {string} machine.pm
     * @param {string} machine.fed
     * @param catalogues
     * @param format
     * @param color
     * @param content
     * @param endpaper
     * @param request
     */
    constructor(
        machine,
        catalogues,
        format,
        color,
        content = {},
        endpaper = {},
        request,
    ) {
        this.machine = machine;
        this.catalogues = catalogues;
        this.format = format
        this.request = request;
        this.content = content;
        this.endpaper = endpaper;
        this.color = color;
        this.error = {
            message: "",
            status: 200
        };
    }

    /**
     * Calculates the fit of a given width and height on an A4 sheet, taking into account spoilage.
     *
     * @param {number} width - The width of the object to be fitted.
     * @param {number} height - The height of the object to be fitted.
     * @param {number} spoilage - The percentage of spoilage allowed during the fitting process.
     *
     * @return {Array} - An array of objects representing the fit options. Each object contains the following properties:
     *   - orientation {string} - The orientation of the fit (e.g. "pure portrait").
     *   - amount_of_sheet_needed {number} - The amount of sheets needed for the fit, including spoilage.
     *   - totalFit {number} - The total fit count.
     *   - landscapeCount {number} - The number of fits in landscape orientation.
     *   - portraitCount {number} - The number of fits in portrait orientation.
     */
    calculateFit(
        width,
        height,
        spoilage
    ) {
        const a4Width = this.format.size.width_with_bleed;
        const a4Height = this.format.size.height_with_bleed;

        // Fit in pure portrait orientation
        const fitPortraitWidth = Math.floor(width / a4Width);
        const fitPortraitHeight = Math.floor(height / a4Height);
        const totalFitPortrait = fitPortraitWidth * fitPortraitHeight;

        // Fit in pure landscape orientation
        const fitLandscapeWidth = Math.floor(width / a4Height);
        const fitLandscapeHeight = Math.floor(height / a4Width);
        const totalFitLandscape = fitLandscapeWidth * fitLandscapeHeight;

        // Mixed orientations: portrait along width, landscape along remaining height
        const fitMixed1Width = Math.floor(width / a4Width);
        const remainingHeight1 = height - fitMixed1Width * a4Height;
        const fitMixed1HeightLandscape = Math.floor(remainingHeight1 / a4Width);
        const totalFitMixed1Portrait = fitPortraitHeight * fitMixed1Width;
        const totalFitMixed1Landscape = fitMixed1HeightLandscape * fitMixed1Width;
        const totalFitMixed1 = totalFitMixed1Portrait + totalFitMixed1Landscape;

        // Mixed orientations: landscape along width, portrait along remaining height
        const fitMixed2Width = Math.floor(width / a4Height);
        const remainingHeight2 = height - fitMixed2Width * a4Width;
        const fitMixed2HeightPortrait = Math.floor(remainingHeight2 / a4Height);
        const totalFitMixed2Landscape = fitLandscapeHeight * fitMixed2Width;
        const totalFitMixed2Portrait = fitMixed2HeightPortrait * fitMixed2Width;
        const totalFitMixed2 = totalFitMixed2Landscape + totalFitMixed2Portrait;

        const results = [
            {
                amount_of_sheet_needed: Math.ceil((this.format.quantity / totalFitPortrait) + spoilage),
                amount_of_sheets_printed: Math.ceil(this.format.quantity / totalFitPortrait),
                exact_used_amount_and_area_of_sheet: (this.format.quantity / totalFitPortrait) + spoilage,
                orientation: 'pure portrait',
                portraitCount: totalFitPortrait,
                landscapeCount: 0,
                totalFit: totalFitPortrait
            },
            {
                amount_of_sheet_needed: Math.ceil((this.format.quantity / totalFitLandscape) + spoilage),
                amount_of_sheets_printed: Math.ceil(this.format.quantity / totalFitLandscape),
                exact_used_amount_and_area_of_sheet: (this.format.quantity / totalFitLandscape) + spoilage,
                orientation: 'pure landscape',
                portraitCount: 0,
                landscapeCount: totalFitLandscape,
                totalFit: totalFitLandscape
            },
            {
                amount_of_sheet_needed: Math.ceil((this.format.quantity / (totalFitMixed1Portrait + totalFitMixed1Landscape)) + spoilage),
                amount_of_sheets_printed: Math.ceil(this.format.quantity / (totalFitMixed1Portrait + totalFitMixed1Landscape)),
                exact_used_amount_and_area_of_sheet: (this.format.quantity / (totalFitMixed1Portrait + totalFitMixed1Landscape)) + spoilage,
                orientation: 'mixed: portrait width, landscape height',
                portraitCount: totalFitMixed1Portrait,
                landscapeCount: totalFitMixed1Landscape,
                totalFit: totalFitMixed1
            },
            {
                amount_of_sheet_needed: Math.ceil((this.format.quantity / (totalFitMixed2Portrait + totalFitMixed2Landscape)) + spoilage),
                amount_of_sheets_printed: Math.ceil(this.format.quantity / (totalFitMixed2Portrait + totalFitMixed2Landscape)),
                exact_used_amount_and_area_of_sheet: (this.format.quantity / (totalFitMixed2Portrait + totalFitMixed2Landscape)) + spoilage,
                orientation: 'mixed: landscape width, portrait height',
                portraitCount: totalFitMixed2Portrait,
                landscapeCount: totalFitMixed2Landscape,
                totalFit: totalFitMixed2
            }
        ];

        return results.sort((a, b) => a.totalFit - b.totalFit);

    }

    /**
     * Calculates various values based on given parameters.
     *
     * @returns {Object} - Object containing calculated values.
     */
    calculate() {

        let machine_sqm = this.machine.width * this.machine.height / 1000000;
        let catalogue = [];
        for (const catalogueElement of this.catalogues) {
            switch (this.machine.fed) {
                case "sheet":
                    if (!catalogueElement.sheet) {
                        this.error.message = "The selected material is not applicable with the selected machine, machine accept sheets only.";
                        this.error.status = 422;
                        continue;
                    }

                    const {
                        width_with_bleed,
                        catalogue_check, rotate_format,
                        machine_check, rotate_catalogue
                    } = calculateFit(
                        this.format,
                        catalogueElement,
                        this.machine,
                        this.content
                    );

                    if (!catalogue_check) {
                        this.error.message = `The selected format ( w:'${this.format.size.width_with_bleed}' & h:${this.format.size.height_with_bleed} ) \
                        is larger than the available catalogue sheet: ('${catalogueElement.material}' '${catalogueElement.grs}') size ( w:'${catalogueElement.width}' & h:'${catalogueElement.height}').`;
                        this.error.status = 422;
                        continue;
                    }

                    if (!machine_check) {
                        this.error.message = `The selected material ('${catalogueElement.material}' '${catalogueElement.grs}') \
                        size ( w:'${catalogueElement.width}' & h:'${catalogueElement.height}') is larger then the selected machine '${this.machine.name}' 
                        size ( w: '${this.machine.width}' & h:'${this.machine.height}').`;
                        this.error.status = 422;
                        continue;
                    }

                    // add the thickness
                    catalogue.push(
                        this.#prepareCalculationSheetObject(
                            catalogueElement,
                            rotate_format,
                            rotate_catalogue,
                            width_with_bleed
                        )
                    );

                    break;
                case "roll":

                    if (catalogueElement.sheet) {
                        this.error.message = "The selected material is not applicable with the selected machine, machine accept rolls only.";
                        this.error.status = 422;
                        continue;
                    }

                    if (catalogueElement.width > this.machine.width) {
                        this.error.message = "The selected material is larger then the selected machine.";
                        this.error.status = 422;
                        continue;
                    }


                    catalogue.push(
                        this.#prepareCalculationRollObject(
                            catalogueElement
                        )
                    );
                    break;
            }
        }

        if (catalogue.length === 0) {
            return {
                message: this.error.message ?? "There are no catalogues available for this machine.",
                status: 422
            }
        }
        this.error.message = "";
        this.error.status = 200;

        catalogue = catalogue.filter(item => item.status !== 422);

        if (catalogue.length > 1) {
            catalogue = catalogue.filter(item => item.total_sheet_price === Math.min(...catalogue.map(item => item.total_sheet_price)))[0]
        } else if (catalogue.length === 1) {
            catalogue = catalogue[0]
        }

        let position = this.calculateFit(this.machine.width, this.machine.height, catalogue.spoilage)

        if (!catalogue.maxi) {
            this.error.message = "The selected format is larger than the available catalogue size sheet.";
            this.error.status = 422;
        }

        return {
            /** binding */
            binding_method: this.format.size.binding_method,
            binding_direction: this.format.size.binding_direction,
            /** endpapers */
            endpaper: this.endpaper,
            endpaper_quantity: this.endpaper.quantity,
            endpaper_amount_of_sheets: catalogue.endpaper_amount_of_sheets,
            endpaper_amount_of_sheets_with_spoilage : catalogue.endpaper_amount_of_sheets_with_spoilage,
            endpaper_total_sheet_price :  isNaN(catalogue.endpaper_total_sheet_price)? 0: catalogue.endpaper_total_sheet_price ,
            /** paper format */
            format_width: this.format.size.width,
            format_height: this.format.size.height,
            width_with_bleed: this.format.size.width_with_bleed,
            height_with_bleed: this.format.size.height_with_bleed,
            height_with_trim_area_and_bleed: catalogue.format_height,
            width_with_trim_area_and_bleed: catalogue.format_width,
            rotate_format: catalogue.rotate_format,
            rotate_catalogue: catalogue.rotate_catalogue,
            material_used: catalogue.material,
            wight_used: catalogue.grs,
            /** print position and amount on the sheet */
            maximum_prints_per_sheet: catalogue.maxi,
            ps: catalogue.ps,
            position: position,
            printable_area_height: catalogue.printable_area_height,
            printable_area_width: catalogue.printable_area_width,
            /** machine info */
            machine_id: this.machine._id,
            machine_name: this.machine.name,
            machine_ean: this.machine.ean,
            machine_sqm: machine_sqm,
            machine_spoilage: this.machine.spoilage,
            machine_spm: this.machine.spm,
            machine_mpm: this.machine.mpm,
            pm: this.machine.pm,
            fed: this.machine.fed,
            /** catalogue info */
            catalogue_supplier: catalogue.supplier,
            catalogue_art_nr: catalogue.art_nr,
            catalogue_ean: catalogue.ean,
            catalogue_width: catalogue.width,
            catalogue_height: catalogue.height,
            catalogue_length: catalogue.length,
            catalogue_density: catalogue.density,
            catalogue_thickness: catalogue.thickness,
            catalogue_price: catalogue.price,
            catalogue_calc_type: catalogue.calc_type,
            lm_in_sqm: catalogue.lm_in_sqm,
            roll_in_sqm: catalogue.roll_in_sqm,
            sheet_in_sqm: catalogue.sheet_in_sqm,
            amount_sqm_sheets_in_kg: catalogue.amount_of_sqm_in_kg,
            /** production info */
            amount_of_sheets_needed: Math.ceil(catalogue.sheets_amount_with_spoilage),
            exact_used_amount_and_area_of_sheet: catalogue.sheets_amount_with_spoilage,
            amount_of_sheets_printed: Math.ceil(catalogue.sheets_amount),

            amount_of_lm: Math.ceil(catalogue.amount_of_lm_with_spoilage),
            exact_used_amount_and_area_of_rol: catalogue.amount_of_lm_with_spoilage,
            amount_of_lm_printed: Math.ceil(catalogue.amount_of_lm),
            amount_of_role_needed: Math.ceil(catalogue.amount_of_role_needed),
            /** pricing */
            start_cost: catalogue.start_cost,
            price_sqm: catalogue.sqm_price,
            price_per_sheet: catalogue.sheet_price,
            price_per_lm: catalogue.lm_price,
            total_sheet_price: catalogue.total_sheet_price,
            price_list: catalogue.price_list,
            color: this.color,

            message: this.error.message,
            status: this.error.status
        }
    }


    /**
     * Check if catalogue item compatible with machine.
     *
     * @param {Object} item - Catalogue information.
     * @param {string} type - Machine fed type.
     *
     * @returns {boolean} Returns true if the catalogue item is compatible with the machine type, otherwise false.
     */
    #isItemCompatible(item, type) {
        return (type === "sheet") ? item.sheet : !item.sheet;
    }

    /**
     * Get the maximum prints on a page and its orientation.
     *
     * @param {number} cross1 - First cross value for layout.
     * @param {number} cross2 - Second cross value for layout.
     *
     * @returns {Object} An object contains maximum value and corresponding page orientation
     */
    #getMaxPrintsAndOrientation(cross1, cross2) {
        let maxCross = Math.max(cross1, cross2);
        let orientation = "";
        if (maxCross === cross1) {
            orientation = "Portrait"
        } else if (maxCross === cross2) {
            orientation = "Landscape"
        }
        return {maxi: maxCross, ps: orientation};
    }

    /**
     * Get cost details against machine's cost and spoilage.
     *
     * @param {number} maxi - The maximum value to be used in calculation.
     * @param {Object} machine - Machine details.
     *
     * @returns {Object} An object contains computation of cost details
     */
    #getCostDetails(maxi, machine) {
        let startCost = machine.divide_start_cost ?
            machine.price / maxi :
            machine.price;
        let spoilageCost = machine.divide_start_cost ?
            machine.spoilage / maxi :
            machine.spoilage;
        return {startCost: startCost, spoilageCost: spoilageCost};
    }

    /**
     * Prepares the calculation object for a roll of paper.
     *
     * @param {Object} catalogueElement - The catalogue element representing the paper.
     * @returns {Object} - The updated catalogue element with the calculation results.
     */
    #prepareCalculationRollObject(
        catalogueElement
    ) {
        this.error.message = "";
        this.error.status = 200;
        let amount_of_lm = 0;
        let amount_of_lm_with_spoilage = 0;

        let {printable_area_height, printable_area_width} = this.calculatePrintableArea(
            catalogueElement.height,
            catalogueElement.width
        )

        let printable_area_height_in_lm = printable_area_height / 1000;


        let {format_height, format_width} = this.calculateFormatSize();

        // Calculate cross left paper size maximum on the sheet/roll
        let {ps, maxi} = this.calculateFloorCross(
            1000, printable_area_width, format_height, format_width
        )

        /**
         * The `spoilage` variable represents the amount of spoilage in a product.
         *
         * @type {number}
         */
        let {spoilage, start_cost} = this.getMachineCost(maxi);

        amount_of_lm = this.format.quantity / maxi;
        amount_of_lm_with_spoilage = amount_of_lm + spoilage;

        let {
            amount_of_sheets,
            amount_of_sheets_with_spoilage,
            price_list,
            total_sheet_price,
        } = this.calculatePriceList(
            maxi,
            spoilage,
            start_cost,
            catalogueElement.lm_price,
            true
        );

        /**
         * calculate endpaper if needed
         * @type {{ps: string, maxi: number}}
         */

        let floorEndpaper = this.calculateFloorCross(
            1000, printable_area_width, this.endpaper.height, this.endpaper.width
        );
        let endpaperMachine = this.getMachineCost(floorEndpaper.maxi);
        let endpaperPriceList = this.calculatePriceList(
            floorEndpaper.maxi,
            endpaperMachine.spoilage,
            endpaperMachine.start_cost,
            catalogueElement.sheet_price,
            false,
            this.endpaper.quantity,
            this.endpaper?.price??0
        );

        catalogueElement['maxi'] = maxi;

        // endpapers
        catalogueElement['endpaper'] = this.endpaper;
        catalogueElement['endpaper_quantity'] = this.endpaper.quantity;
        catalogueElement['endpaper_amount_of_sheets'] = endpaperPriceList.amount_of_sheets
        catalogueElement['endpaper_amount_of_sheets_with_spoilage'] = endpaperPriceList.amount_of_sheets_with_spoilage
        catalogueElement['endpaper_total_sheet_price'] = isNaN(endpaperPriceList.total_sheet_price)? 0: endpaperPriceList.total_sheet_price

        catalogueElement['ps'] = ps;
        catalogueElement['printable_area_width'] = printable_area_width;
        catalogueElement['printable_area_height'] = printable_area_height;
        catalogueElement['format_height'] = format_height;
        catalogueElement['format_width'] = format_width;

        catalogueElement['sheets_amount'] = amount_of_sheets;
        catalogueElement['start_cost'] = start_cost;
        catalogueElement['spoilage'] = spoilage;
        catalogueElement['sheets_amount_with_spoilage'] = amount_of_sheets_with_spoilage;

        catalogueElement['amount_of_lm'] = amount_of_lm;
        catalogueElement['amount_of_role_needed'] = Math.ceil(amount_of_lm_with_spoilage) / printable_area_height_in_lm;
        catalogueElement['amount_of_lm_with_spoilage'] = amount_of_lm_with_spoilage;

        catalogueElement['total_sheet_price'] = total_sheet_price;
        catalogueElement['price_list'] = price_list;
        catalogueElement['rotate_format'] = false;
        catalogueElement['rotate_catalogue'] = false;
        catalogueElement['message'] = "";
        catalogueElement['status'] = 200;

        return catalogueElement;
    }

    /**
     * Prepares the calculation sheet object for the given catalogue element.
     *
     * @param {Object} catalogueElement - The catalogue element for which the calculation sheet object is prepared.
     * @param {boolean} [rotate_format=false] - Indicates whether the format should be rotated.
     * @param {boolean} [rotate_catalogue=false] - Indicates whether the catalogue should be rotated.
     * @param {Number} width_with_bleed - The width with bleed for calculation.
     *
     * @returns {Object} - The prepared calculation sheet object.
     */
    #prepareCalculationSheetObject(
        catalogueElement,
        rotate_format = false,
        rotate_catalogue = false,
        width_with_bleed
    ) {
        this.error.message = "";
        this.error.status = 200;

        let {printable_area_height, printable_area_width} = this.calculatePrintableArea(
            catalogueElement.height,
            catalogueElement.width,
            rotate_catalogue
        )

        let {format_height, format_width} = this.calculateFormatSize(width_with_bleed);
        // Calculate cross left paper size maximum on the sheet/roll
        let {ps, maxi} = this.calculateFloorCross(
            printable_area_height, printable_area_width, format_height, format_width
        )

        if (maxi === 0) {
            this.error.message = "Item format didn't fit on the printable area.";
            this.error.status = 422;
        }

        let {spoilage, start_cost} = this.getMachineCost(maxi);

        let {
            amount_of_sheets,
            amount_of_sheets_with_spoilage,
            price_list,
            total_sheet_price
        } = this.calculatePriceList(
            maxi,
            spoilage,
            start_cost,
            catalogueElement.sheet_price
        );

        /**
         * calculate endpaper if needed
         * @type {{ps: string, maxi: number}}
         */

        let floorEndpaper = this.calculateFloorCross(
            printable_area_height, printable_area_width, this.endpaper.height, this.endpaper.width
        );
        if (floorEndpaper.maxi === 0) {
            this.error.message = "Item format didn't fit on the printable area.";
            this.error.status = 422;
        }

        let endpaperMachine = this.getMachineCost(floorEndpaper.maxi);
        if(endpaperMachine.start_cost === Infinity) {
            this.error.message = "Item format didn't fit on the printable area.";
            this.error.status = 422;
        }
        let endpaperPriceList = this.calculatePriceList(
            floorEndpaper.maxi,
            endpaperMachine.spoilage,
            endpaperMachine.start_cost,
            catalogueElement.sheet_price,
            false,
            this.endpaper.quantity,
            this.endpaper?.price??0
        );

        catalogueElement['maxi'] = maxi;
        // endpapers
        catalogueElement['endpaper'] = this.endpaper;
        catalogueElement['endpaper_quantity'] = this.endpaper.quantity;
        catalogueElement['endpaper_amount_of_sheets'] = endpaperPriceList.amount_of_sheets
        catalogueElement['endpaper_amount_of_sheets_with_spoilage'] = endpaperPriceList.amount_of_sheets_with_spoilage
        catalogueElement['endpaper_total_sheet_price'] = isNaN(endpaperPriceList.total_sheet_price)? 0: endpaperPriceList.total_sheet_price

        catalogueElement['ps'] = ps;
        catalogueElement['printable_area_width'] = printable_area_width;
        catalogueElement['printable_area_height'] = printable_area_height;
        catalogueElement['format_height'] = format_height;
        catalogueElement['format_width'] = format_width;

        catalogueElement['sheets_amount'] = amount_of_sheets;
        catalogueElement['start_cost'] = start_cost;
        catalogueElement['spoilage'] = spoilage;
        catalogueElement['sheets_amount_with_spoilage'] = amount_of_sheets_with_spoilage;
        catalogueElement['total_sheet_price'] = total_sheet_price;
        catalogueElement['price_list'] = price_list;
        catalogueElement['rotate_format'] = rotate_format;
        catalogueElement['rotate_catalogue'] = rotate_catalogue;
        catalogueElement['message'] = this.error.message;
        catalogueElement['status'] = this.error.status;
        return catalogueElement;
    }

    /**
     * Calculates the start cost and spoilage for a machine based on the machine's price and a maximum value.
     *
     * @param {number} maxi - The maximum value used in the calculation.
     * @returns {{start_cost: number, spoilage: number}} - An object containing the calculated start cost and spoilage.
     */
    getMachineCost(
        maxi
    ) {
        /**
         * Calculates the start cost based on the machine's price and a maximum value.
         *
         * @param {number} maxi - The maximum value used in the calculation.
         *
         * @returns {number} The calculated start cost.
         */
        let start_cost = this.machine.divide_start_cost ?
            (this.machine.price / maxi) / 100000 :
            this.machine.price / 100000;

        let spoilage = this.machine.divide_start_cost ?
            this.machine.spoilage / maxi :
            this.machine.spoilage;

        return {spoilage, start_cost};
    }

    /**
     * Calculates the number of prints that can fit on a given printable area.
     *
     * @param {number} printable_area_height - The height of the printable area.
     * @param {number} printable_area_width - The width of the printable area.
     * @param {number} format_height - The height of the print format.
     * @param {number} format_width - The width of the print format.
     *
     * @returns {{ps: string, maxi: number}} - The orientation and the maximum number of prints that can fit.
     */
    calculateFloorCross(
        printable_area_height,
        printable_area_width,
        format_height,
        format_width
    ) {
        //  calculate floor numbers left cross
        // height calculation
        let calc_hh = Math.floor(printable_area_height / format_height)
        let calc_hw = Math.floor(printable_area_height / format_width)
        // calculate floor numbers right cross
        // width calculation
        let calc_wh = Math.floor(printable_area_width / format_height)
        let calc_ww = Math.floor(printable_area_width / format_width)
        // Cross values
        let cross_1 = calc_hh * calc_ww;
        let cross_2 = calc_hw * calc_wh;
        // maxi is the amount of prints on one page
        let maxi = Math.max(cross_1, cross_2);

        let ps = ""
        if (maxi === cross_1) {
            ps = "Portrait"
        } else if (maxi === cross_2) {
            ps = "Landscape"
        }
        return {ps, maxi};
    }

    /**
     * Calculates the printable area based on the given catalogue element height and width.
     *
     * @param {number} catalogueElementHeight - The height of the catalogue element.
     * @param {number} catalogueElementWidth - The width of the catalogue element.
     * @param {boolean} [rotate_catalogue=false] - Whether the catalogue element is rotated.
     * @returns {Object} - An object containing the printable area width and height.
     *                    The printable_area_width property represents the width of the printable area.
     *                    The printable_area_height property represents the height of the printable area.
     */
    calculatePrintableArea(
        catalogueElementHeight,
        catalogueElementWidth,
        rotate_catalogue = false
    ) {

        let printable_area_height, printable_area_width;

        // Determine dimensions based on whether the catalogue is rotated
        const height = rotate_catalogue ? catalogueElementWidth : catalogueElementHeight;
        const width = rotate_catalogue ? catalogueElementHeight : catalogueElementWidth;

        // Calculate height considering margins and optional trim area exclusion
        printable_area_height = height - (
            this.machine.margin_top + this.machine.margin_bottom +
            (this.machine.trim_area_exclude_y ? -this.machine.trim_area : 0)
        );

        // Calculate width considering margins and optional trim area exclusion
        printable_area_width = width - (
            this.machine.margin_left + this.machine.margin_right +
            (this.machine.trim_area_exclude_x ? -this.machine.trim_area : 0)
        );

        return {printable_area_height, printable_area_width};
    }

    /**
     * Calculates the format size by adding the machine trim area to the height and width with bleed.
     *
     * @returns {Object} An object containing the calculated format width and height.
     *                  - format_width: The calculated format width.
     *                  - format_height: The calculated format height.
     */
    calculateFormatSize(
        width_with_bleed = 0
    ) {
        width_with_bleed = width_with_bleed !== 0 ?
            width_with_bleed: this.format.size.width_with_bleed
        let format_height = this.format.size.height_with_bleed + this.machine.trim_area;
        let format_width = width_with_bleed + this.machine.trim_area;
        return {format_height, format_width};
    }

    /**
     * Calculates the price list for a given set of parameters.
     *
     * @param {number} maxi - The maximum quantity per sheet.
     * @param {number} spoilage - The spoilage factor.
     * @param {number} start_cost - The starting cost.
     * @param {number} sheet_price - The price per sheet.
     * @param {boolean} [round=false] - Whether to round up the number of sheets with spoilage.
     * @param {number} [quantity=0] - The quantity of sheets.
     * @param {number} [price=0] - The sheet price.
     *
     * @returns {object} An object containing the following properties:
     *  - amount_of_sheets {number} The amount of sheets without spoilage.
     *  - amount_of_sheets_with_spoilage {number} The amount of sheets with spoilage.
     *  - price_list {array} The list of prices per sheet.
     *  - total_sheet_price {number} The total price of all sheets.
     */
    calculatePriceList(
        maxi,
        spoilage,
        start_cost,
        sheet_price,
        round = false,
        quantity = 0,
        price = 0
    ) {
        let amount_of_sheets = 0;
        let amount_of_sheets_with_spoilage = 0;
        let price_list = [];
        let list = price?[]:this.color.price_list;

        quantity = quantity? quantity : this.format.quantity;
        price = price?price:this.color.price;

        list.map(function (l) {
                amount_of_sheets = l.qty / maxi;
                amount_of_sheets_with_spoilage = round ? Math.ceil(amount_of_sheets + spoilage) : amount_of_sheets + spoilage;
                let price = l.runs.price / 100000;
                price_list.push({
                    amount_of_sheets: amount_of_sheets,
                    amount_of_sheets_with_spoilage: amount_of_sheets_with_spoilage,
                    total_sheet_price: start_cost +
                        (sheet_price * amount_of_sheets_with_spoilage) +
                        (price * amount_of_sheets_with_spoilage),
                    price: price,
                    qty: l.qty
                })
            }
        );

        amount_of_sheets = quantity / maxi;
        amount_of_sheets_with_spoilage = round ? Math.ceil(amount_of_sheets + spoilage) : amount_of_sheets + spoilage;
        let total_sheet_price = start_cost +
            (sheet_price * amount_of_sheets_with_spoilage) +
            (price * amount_of_sheets_with_spoilage);

        return {
            amount_of_sheets,
            amount_of_sheets_with_spoilage,
            price_list,
            total_sheet_price
        }
    }

}
