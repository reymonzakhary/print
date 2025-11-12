const {generateList, mergeByDynamicKey,isNumber, calculateArea} = require("../../Helpers/Helper.js");
const {throwError, isNumberOrStringNumber} = require("../../Helpers/Helper");
const BindingTypes = require("../Enums/BindingTypes")
/**
 * @typedef Promise
 * @property {Error} result Rejection error object
 */
module.exports = class Format {

    /**
     * default values
     * @type {{a1: {mm: number, in: number, cm: number, yd: number, m: number, ft: number}, a2: {mm: number, in: number, cm: number, yd: number, m: number, ft: number}, a10: {mm: number, in: number, cm: number, yd: number, m: number, ft: number}, a3: {mm: number, in: number, cm: number, yd: number, m: number, ft: number}, a4: {mm: number, in: number, cm: number, yd: number, m: number, ft: number}, a5: {mm: number, in: number, cm: number, yd: number, m: number, ft: number}, a6: {mm: number, in: number, cm: number, yd: number, m: number, ft: number}, a7: {mm: number, in: number, cm: number, yd: number, m: number, ft: number}, a8: {mm: number, in: number, cm: number, yd: number, m: number, ft: number}, a9: {mm: number, in: number, cm: number, yd: number, m: number, ft: number}, a0: {mm: number, in: number, cm: number, yd: number, m: number, ft: number}}}
     */
    #format = {
        'a0': {
            'width': 841,
            'height': 1188,
            'cm': 9999.49,
            'mm': 999949,
            'm': 0.999949,
            'in': 1549.924050,
            'ft': 10.763361,
            'yd': 1.195929
        },
        'a1': {
            'width': 594,
            'height': 841,
            'mm': 499554,
            'cm': 4995.54,
            'm': 0.499554,
            'in': 774.310249,
            'ft': 5.377155,
            'yd': 0.597462
        },
        'a2': {
            'width': 420,
            'height': 594,
            'mm': 249480,
            'cm': 2494.8,
            'm': 0.24948,
            'in': 386.694773,
            'ft': 2.685380,
            'yd': 0.298376
        },
        'a3': {
            'width': 297,
            'height': 420,
            'mm': 124740,
            'cm': 1247.4,
            'm': 0.12474,
            'in': 193.347387,
            'ft': 1.342690,
            'yd': 0.149188
        },
        'a4': {
            'width': 210,
            'height': 297,
            'mm': 62370,
            'cm': 623.7,
            'm': 0.06237,
            'in': 96.673693,
            'ft': 0.671345,
            'yd': 0.074594
        },
        'a5': {
            'width': 148,
            'height': 210,
            'mm': 31080,
            'cm': 310.8,
            'm': 0.03108,
            'in': 48.174096,
            'ft': 0.334542,
            'yd': 0.037171
        },
        'a6': {
            'width': 105,
            'height': 148,
            'mm': 15540,
            'cm': 155.4,
            'm': 0.01554,
            'in': 24.087048,
            'ft': 0.167271,
            'yd': 0.018586
        },
        'a7': {
            'width': 74,
            'height': 105,
            'mm': 7770,
            'cm': 77.7,
            'm': 0.00777,
            'in': 12.043524,
            'ft': 0.083636,
            'yd': 0.009293
        },
        'a8': {
            'width': 52,
            'height': 74,
            'mm': 3848,
            'cm': 38.48,
            'm': 0.003848,
            'in': 5.964412,
            'ft': 0.041420,
            'yd': 0.004602
        },
        'a9': {
            'width': 37,
            'height': 52,
            'mm': 1924,
            'cm': 19.24,
            'm': 0.001924,
            'in': 2.982206,
            'ft': 0.020710,
            'yd': 0.002301
        },
        'a10': {
            'width': 26,
            'height': 37,
            'mm': 962,
            'cm': 9.62,
            'm': 0.000962,
            'in': 1.491103,
            'ft': 0.010355,
            'yd': 0.001151
        }
    }

    /**
     *
     * @type {{mm, height_with_bleed: *, in, width, cm, width_with_bleed: *, yd, m, ft, height}}
     */
    #size = 0

    /**
     * Creates a new instance of the constructor.
     *
     * @param {string} category - The category of the instance.
     * @param {Array} category.ranges - The ranges belonging to the category.
     * @param {Array} category.range_list - The list of ranges in the category.
     * @param {Array} category.limits - The limits of the category.
     * @param {Array} category.free_entry - The free entries associated with the category.
     * @param {number} category.range_around - The range around the category.
     * @param {object} props - The props of the instance.
     * @param {number} props.maximum_width - The maximum width props value.
     * @param {String} props.dimension - The dimension props value.
     * @param {Boolean} props.dynamic - The dynamic props value.
     * @param {Object} props._format - The format object in props.
     * @param {number} quantity - The quantity of the instance.
     * @param {boolean} bleed - Specifies if the instance has bleed.
     * @param {number} quantity_range_start - The start of the quantity range.
     * @param {number} quantity_range_end - The end of the quantity range.
     * @param {number} quantity_incremental_by - The incremental quantity value.
     * @param {Boolean} range_override - Indicates range override status.
     * @param {Array} pages - The array of pages included.
     * @param {Array} cover - The array of cover elements.
     * @param {Array} binding_method - The array of binding methods.
     * @param {Array} binding_direction - The array of binding directions.
     * @param {Array} folding - The array of folding options.
     * @param {Array} endpapers - The array of endpapers.
     * @returns {void}
     */
    constructor(
        category,
        props,
        quantity,
        bleed,
        quantity_range_start,
        quantity_range_end,
        quantity_incremental_by,
        range_override,
        pages,
        cover,
        binding_method,
        binding_direction,
        folding,
        endpapers
    ) {
        this.category = category;
        this.props = props;
        this.quantity = quantity;
        this.bleed = bleed;
        this.quantity_range_start = quantity_range_start;
        this.quantity_range_end = quantity_range_end;
        this.quantity_incremental_by = quantity_incremental_by;
        this.range_override = range_override;
        this.pages = pages ?? [];
        this.cover = cover ?? [];
        this.binding_method = this.getBindingMethod(binding_method)
        this.binding_direction = binding_direction?.length ? binding_direction[0]?.option_calc_ref: {};
        this.endpapers = endpapers;
        this.num_pages = 0;
        this.is_sides = false;
        this.error = {
            message : "",
            status : 200
        }
    }

    getBindingMethod(binding_method) {
        return binding_method?.length ? BindingTypes.find(binding_method[0]?.option_calc_ref) : {};
    }

    /**
     * Calculates the size, quantity, and status of a product.
     *
     * @returns {{message: string, status: number}}
     */
    calculate()
    {
        try {

            this.calculatePages();
            if(!Object.keys(this.props).length) {
                this.error.message = `The '${this.props.name}' was not found.`;
                this.error.status = 422;
                throwError(this.error,  `The '${this.props.name}' was not found.`);
            }

            // TODO 3d dimension
            // if(this.props.dimension !== '2d' ) {
            //     this.error.message = `'${this.props.name}': Only 2D dimension calculation are available at this moment.`;
            //     this.error.status = 422;
            //     throwError(this.error,  `'${this.props.name}': Only 2D dimension calculation are available at this moment.`);
            // }

            if(this.props.dynamic) {
                let method = "calculateFormatFrom" + this.props.unit.charAt(0).toUpperCase() + this.props.unit.slice(1);

                const format = this.props._

                if(format === undefined) {

                    this.error.message = `The '${this.props.name}' Width & height parameter are required with dynamic option enabled.`;
                    this.error.status = 422;
                    throwError(this.error,  `The '${this.props.name}' Width & height parameter are required with dynamic option enabled.`);
                }

                let width = Number(format?.width);
                let height = Number(format?.height);

                if(width === undefined) {
                    this.error.message = `The '${this.props.name}' is dynamic the width key is required.`;
                    this.error.status = 422;
                    throwError(this.error,  `The '${this.props.name}' is dynamic the width key is required.`);
                }

                if(height === undefined) {
                    this.error.message = `The '${this.props.name}' is dynamic the height key is required.`;
                    this.error.status = 422;
                    throwError(this.error,  `The '${this.props.name}' is dynamic the height key is required.`);
                }
                if(width  > this.props.maximum_width || width  < this.props.minimum_width) {
                    this.error.message = "Width should be between "+ this.props.minimum_width + " and "+ this.props.maximum_width;
                    this.error.status = 422;
                    throwError(this.error,  "Width should be between "+ this.props.minimum_width + " and "+ this.props.maximum_width);
                }

                if(height > this.props.maximum_height || height < this.props.minimum_height) {
                    this.error.message = "Height should be between "+ this.props.maximum_height + " and "+ this.props.minimum_height;
                    this.error.status = 422;
                    throwError(this.error,  "Height should be between "+ this.props.maximum_height + " and "+ this.props.minimum_height);
                }

                try {
                    if(typeof this[method] === "function") {
                        this.#size = this[method](width, height)
                    } else {
                        throwError(this.error, `The '${this.props.name}' option is missing a unit definition, or 'width' and 'height' are not provided.`);
                    }
                } catch (error) {
                    throwError(this.error, `The '${this.props.name}' option is missing a unit definition, or 'width' and 'height' are not provided.`);
                }

            } else if(this.#format[this.props.slug] === undefined){
                let method = "calculateFormatFrom" + this.props.unit.charAt(0).toUpperCase() + this.props.unit.slice(1);
                let width = this.props?.width;
                let height = this.props?.height;

                if(width === undefined|| width === null || width === 0) {
                    this.error.message = `The '${this.props.name}' option is missing the 'width' value.`;
                    this.error.status = 422;
                    throwError(this.error,  `The '${this.props.name}' option is missing the 'width' value.`);
                }

                if(height === undefined|| height === null || height === 0) {
                    this.error.message = `The '${this.props.name}' option is missing the 'height' value.`;
                    this.error.status = 422;
                    throwError(this.error,  `The '${this.props.name}' option is missing the 'height' value.`);
                }

                try {
                    if(typeof this[method] === "function") {
                        this.#size = this[method](width, height)
                    } else {
                        throwError(this.error, `The '${this.props.name}' option is missing a unit definition, or 'width' and 'height' are not provided.`);
                    }
                } catch (error) {
                    throwError(this.error, `The '${this.props.name}' option is missing a unit definition, or 'width' and 'height' are not provided.`);
                }
            } else {
                this.#size = this.defaultFormat(this.#format[this.props.slug])
            }

            let divided = this.is_sides? 2: 4;
            return {
                bleed: this.bleed,
                quantity: this.num_pages? ((this.num_pages/divided)*this.quantity):this.quantity,
                o_qty: this.quantity,
                pages: this.num_pages,
                range: this.defaultRange(),
                size: this.#size,
                message: this.error.message,
                status: this.error.status
            }
        } catch (e) {
            throwError(this.error,  e.message);
        }
    }


    calculateFormatFromM(
        width,
        height,
    )
    {
        const sqcm =  width * height;


        let net_width = width * 1000;
        let calc_width = net_width;

        switch (this.is_sides) {
            case true:
                calc_width = ((this.num_pages / parseInt(this.binding_method.outside_divider??2)) * net_width);
                break;
            case false:
                let inside_divider = this.binding_method.inside_divider === undefined ?2:this.binding_method.inside_divider;
                calc_width = this.num_pages? net_width * parseInt(inside_divider):net_width
        }

        return {
            'is_sides' : this.is_sides,
            'pages' : this.num_pages,
            'binding_method' : this.binding_method,
            'binding_direction' : this.binding_direction,
            'default_width_with_bleed' : (width * 1000) + (this.bleed * 2),
            'height_with_bleed' : (height * 1000) + (this.bleed * 2),
            'width_with_bleed' : calc_width + (this.bleed * 2),
            'default_lm' : 2 *  (((width * 1000) / 1000) + ((height * 2) / 1000)),
            'lm' : 2 *  ((calc_width / 1000) + height),
            'width': calc_width,
            'default_width': width * 1000,
            'height': height * 1000,
            'cm': calculateArea(calc_width, height * 1000).cm,
            'mm': calculateArea(calc_width, height * 1000).mm,
            'm': calculateArea(calc_width, height * 1000).m,
            'in': calculateArea(calc_width, height * 1000).in,
            'ft': calculateArea(calc_width, height * 1000).ft,
            'yd': calculateArea(calc_width, height * 1000).yd
        }
    }

    /**
     * Calculates the format of cm
     *
     * @param {number} width - The width in centimeters
     * @param {number} height - The height in centimeters
     * @returns {object} - The format values in different units
     *  - mm : The format in millimeters
     *  - in : The format in inches
     *  - cm : The format in centimeters
     *  - yd : The format in yards
     *  - m : The format in meters
     *  - ft : The format in feet
     */
    calculateFormatFromCm(
        width,
        height,
    )
    {
        const sqcm =  width * height;


        let net_width = width * 10;
        let calc_width = net_width;

        switch (this.is_sides) {
            case true:
                calc_width = ((this.num_pages / parseInt(this.binding_method.outside_divider??2)) * net_width);
                break;
            case false:
                let inside_divider = this.binding_method.inside_divider === undefined ?2:this.binding_method.inside_divider;
                calc_width = this.num_pages? net_width * parseInt(inside_divider):net_width
        }

        return {
            'is_sides' : this.is_sides,
            'pages' : this.num_pages,
            'binding_method' : this.binding_method,
            'binding_direction' : this.binding_direction,
            'default_width_with_bleed' : (width * 10) + (this.bleed * 2),
            'height_with_bleed' : (height * 10) + (this.bleed * 2),
            'width_with_bleed' : calc_width + (this.bleed * 2),
            'default_lm' : 2 *  (((width * 10) / 1000) + ((height * 2) / 1000)),
            'lm' : 2 *  ((calc_width / 1000) + (height / 100)),
            'width': calc_width,
            'default_width': width * 10,
            'height': height * 10,
            'cm': calculateArea(calc_width, height * 10).cm,
            'mm': calculateArea(calc_width, height * 10).mm,
            'm': calculateArea(calc_width, height * 10).m,
            'in': calculateArea(calc_width, height * 10).in,
            'ft': calculateArea(calc_width, height * 10).ft,
            'yd': calculateArea(calc_width, height * 10).yd
        }
    }

    /**
     * Calculates the format of mm based on given width and height.
     * @param {number} width - The width of the object in mm.
     * @param {number} height - The height of the object in mm.
     * @returns {Object} The format in different units.
     * @property {number} mm - The format in millimeters.
     * @property {number} in - The format in inches.
     * @property {number} cm - The format in centimeters.
     * @property {number} yd - The format in yards.
     * @property {number} m - The format in meters.
     * @property {number} ft - The format in feet.
     */
    calculateFormatFromMm(
        width,
        height
    )
    {
        const sqmm =  width * height;
        const sqcm =  sqmm / 100;

        let net_width = width;
        let calc_width = net_width;

        switch (this.is_sides) {
            case true:
                calc_width = ((this.num_pages / parseInt(this.binding_method.outside_divider??2)) * net_width);
                break;
            case false:
                let inside_divider = this.binding_method.inside_divider === undefined ?2:this.binding_method.inside_divider;
                calc_width = this.num_pages? net_width * parseInt(inside_divider):net_width;
        }

        return {
            'is_sides' : this.is_sides,
            'pages' : this.num_pages,
            'binding_method' : this.binding_method,
            'binding_direction' : this.binding_direction,
            'default_width_with_bleed' : width + (this.bleed * 2),
            'height_with_bleed' : height + (this.bleed * 2),
            'width_with_bleed' : calc_width + (this.bleed * 2),
            'default_lm' : 2 *  ((width / 1000) + (height / 1000)),
            'lm' : 2 *  ((calc_width / 1000) + (height / 1000)),
            'width': calc_width,
            'default_width': width,
            'height': height,
            'cm': calculateArea(calc_width, height).cm,
            'mm': calculateArea(calc_width, height).mm,
            'm': calculateArea(calc_width, height).m,
            'in': calculateArea(calc_width, height).in,
            'ft': calculateArea(calc_width, height).ft,
            'yd': calculateArea(calc_width, height).yd

            // 'cm': sqcm,
            // 'mm': sqmm * 100,
            // 'm': sqcm / 10000,
            // 'in': sqcm * 0.155,
            // 'ft': sqcm * 0.000010764,
            // 'yd': sqcm * 0.000119599
        }
    }


    /**
     * This method returns an object with various properties based on the provided format object.
     *
     * @param {object} format - The format object containing the dimensions of the format.
     * @param {number} format.height - The height of the format.
     * @param {number} format.width - The width of the format.
     * @param {number} format.cm - The dimensions in centimeters.
     * @param {number} format.mm - The dimensions in millimeters.
     * @param {number} format.m - The dimensions in meters.
     * @param {number} format.in - The dimensions in inches.
     * @param {number} format.ft - The dimensions in feet.
     * @param {number} format.yd - The dimensions in yards.
     * @returns {object} - An object with properties based on the provided format object.
     * - height_with_bleed: The height of the format with added bleed on both sides.
     * - width_with_bleed: The width of the format with added bleed on both sides.
     * - lm: Twice the sum of the width and height divided by 1000, in meters.
     * - width: The width of the format.
     * - height: The height of the format.
     * - cm: The dimensions in centimeters.
     * - mm: The dimensions in millimeters.
     * - m: The dimensions in meters.
     * - in: The dimensions in inches.
     * - ft: The dimensions in feet.
     * - yd: The dimensions in yards.
     */
    defaultFormat(
        format
    )
    {
        let calc_width = format.width;

        switch (this.is_sides) {
            case true:
                calc_width = ((this.num_pages / parseInt(this.binding_method.outside_divider??2)) * calc_width);
                break;
            case false:
                let inside_divider = this.binding_method.inside_divider === undefined ?2:this.binding_method.inside_divider;
                calc_width = this.num_pages? calc_width * parseInt(inside_divider) :calc_width
        }
        return {
            'is_sides' : this.is_sides,
            'pages' : this.num_pages,
            'binding_method' : this.binding_method,
            'binding_direction' : this.binding_direction,
            'default_width_with_bleed' : format.width + (this.bleed * 2),
            'height_with_bleed' : format.height + (this.bleed * 2),
            'width_with_bleed' : calc_width + (this.bleed * 2),
            'default_lm' : 2 *  ((format.width / 1000) + (format.height / 1000)),
            'lm' : 2 *  ((calc_width / 1000) + (format.height / 1000)),
            'default_width': format.width,
            'width': calc_width,
            'height': format.height,
            'cm': calculateArea(calc_width, format.height).cm,
            'mm': calculateArea(calc_width, format.height).mm,
            'm': calculateArea(calc_width, format.height).m,
            'in': calculateArea(calc_width, format.height).in,
            'ft': calculateArea(calc_width, format.height).ft,
            'yd': calculateArea(calc_width, format.height).yd
        }
    }

    /**
     * Returns the default range for printing methods.
     *
     * @returns {Array} An array of objects representing the default range for printing methods.
     * Each object has the following properties:
     *   - pm: A string representing the printing method.
     *   - quantity_range_start: The starting quantity range for the printing method.
     *   - quantity_range_end: The ending quantity range for the printing method.
     *   - quantity_incremental_by: The incremental quantity for the printing method.
     *   - range_list: An array representing the range of quantities.
     */
    defaultRange()
    {
        let limit = 10;
        let range = [{
            pm: 'all',
            quantity_range_start: this.quantity_range_start,
            quantity_range_end: this.quantity_range_end,
            quantity_incremental_by: this.quantity_incremental_by,
            range_list: generateList(this.quantity_range_start, this.quantity_range_end, this.quantity_incremental_by, limit)
        }];

        if(this.range_override) {
            range.forEach(item => {
                const { range_list } = item;
                const index = range_list.indexOf(this.quantity);
                if (index !== -1) {
                    const before = range_list.slice(Math.max(0, index - 2), index);
                    const after = range_list.slice(index + 1, index + 3);
                    item.range_list = [...before, this.quantity, ...after];
                } else {
                    item.range_list = [];
                }
            });

            return range;
        }

        if(this.category.ranges?.length) {
            range = [];
            for (const method of this.category.ranges) {
                range.push({
                    pm: method.slug,
                    quantity_range_start: method.from,
                    quantity_range_end: method.to,
                    quantity_incremental_by: method.incremental_by,
                    range_list: generateList(method.from, method.to, method.incremental_by)
                })
            }

            return mergeByDynamicKey(
                range,
                'pm',
                this.category.limits,
                30,
                this.quantity,
                this.category.range_around,
                this.category.free_entry,
                this.category.range_list
            );
        }

        return range;
    }

    /**
     * Calculates the total number of pages based on the provided configuration.
     * If the first page is dynamic, it uses the specified number of pages.
     * If the first page is not dynamic, it extracts the number from the option name.
     * If an invalid number of pages is provided, sets the status to 422 and returns a message.
     *
     * @returns {Format} - The current instance with updated number of pages or error message.
     */
    calculatePages()
    {

        let pages = 0;
        this.num_pages = 0;
        if(this.cover.length > 0) {
            this.is_sides = true;
            if(this.cover[0].dynamic) {
                pages = this.cover[0].option._.sides;
                if(!isNumberOrStringNumber(pages)) {
                    this.error.message = "Sides parameter is required with dynamic option, and should be a integer.";
                    this.error.status = 422;
                    throwError(this.error,  "Sides parameter is required with dynamic option, and should be a integer.");
                }

                this.num_pages = pages
            }else{
                this.num_pages = parseInt(this.cover[0].option.name.match(/\d+/) ? this.cover[0].option.name.match(/\d+/)[0] : '0', 10)
            }
        } else if ( this.pages.length > 0) {
            if(this.pages[0].dynamic) {
                pages = parseInt(this.pages[0].option._.pages);
                if(!isNumber(pages)) {
                    this.error.message = "Pages parameter is required with dynamic option, and should be a integer.";
                    this.error.status = 422;
                    throwError(this.error,  "Pages parameter is required with dynamic option, and should be a integer.");
                    return this;

                }

                this.num_pages = pages
            }else{
                this.num_pages = parseInt(this.pages[0].option.name.match(/\d+/) ? this.pages[0].option.name.match(/\d+/)[0] : '0', 10)
            }

        }
    }
}
