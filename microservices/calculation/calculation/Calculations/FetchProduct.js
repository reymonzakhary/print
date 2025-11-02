
const SupplierCategory = require("../Models/SupplierCategory");
const SupplierBox = require("../Models/SupplierBox");
const SupplierOption = require("../Models/SupplierOption");
const Axios = require("axios");

const FetchCatalogue = require("./Catalogues/FetchCatalogue");
const FetchCategory = require("./FetchCategory");
const Machines = require("./Machines");
const Format = require("./Config/Format");
const MarginService = 'http://margin:3333/'

const {
    combinations,
    getDividerByKey, formatPriceObject, mergePriceObject,
    refactorPriceObject, groupByDividerWithCalcRefCopy, throwError, getUniqueIds, getUniqueIdsFromDirectIds,
    fetchDataKey, filterByCalcRef, calculatePages, getDefaultFormat,findDiscountSlot
} = require('../Helpers/Helper')


/**
 * @typedef Promise
 * @property {Error} result Rejection error object
 */
module.exports = class FetchProduct {

    /**
     * Creates a new object representing a product constructor.
     *
     * @param {string} slug - The slug of the product.
     * @param {string} supplier_id - The ID of the product's supplier.
     * @param {object} product - The product details.
     * @param {object} request - The request object associated with the product.
     * @param {null|array} contract - The request customer to this product.
     * @param {boolean} [internal=false] - Flag indicating if the product is internal.
     * @return {void}
     */
    constructor(
        slug,
        supplier_id,
        product,
        request,
        contract = null,
        internal = false
    ) {
        this.contract = contract;
        this.slug = slug;
        this.supplier_id = supplier_id;
        this.items = product;
        this.category = [];
        this.machine = [];
        this.product = [];
        this.margin = [];
        this.discount = [];
        this.object = [];
        this.runs = [];
        this.status = 200;
        this.material = {};
        this.color = {};
        this.options = {};
        this.duration = {};
        this.dlv = [];
        this.weight = {};
        this.format = {};

        this.binding_method = {}
        this.binding_direction = {}
        this.binding_material = {}
        this.binding_color = {}

        this.folding = {};

        this.catalogue = {};
        this.request = request;
        this.combinations = [];
        this.multiple = false;
        this.internal = internal;
        this.boops = {};
        this.vat_override = this.request.vat_override;
        this.content = {
            pass: false,
            grs: 0,
            density: 0,
            thickness: 0,
            pages: 0
        };
        let { quantity, vat } = this.request;
        this.vat = vat;
        this.quantity = quantity ? parseInt(quantity) : 0;
        this.error = {
            message: "",
            status: 200
        };
    }

    /**
     * Retrieves supplier category information based on specified criteria.
     *
     * @returns {{ result: Error }} An object containing the category information if successful or an error object if not.
     */
    async getCategory() {
        try {
            ({category: this.category = null} =
                await (new FetchCategory(this.slug, this.supplier_id).getCategory()) ?? {category: null});
            if (!this.category) throwError(this.error, "Category not found.");

            this.machine = this.category.machine;
            this.boops = this.category.boops[0];
            if (!this.machine.length) {
                throwError(this.error, "Machine not found.");
            }


            this.discount = findDiscountSlot(this.contract,this.category._id.toString(), this.quantity)

        } catch (e) {
            throwError(this.error, e.message);
        }
    }

    /**
     * Retrieves product information based on the given criteria.
     *
     * @returns {{result: Error}} Returns an object containing the result of the operation or an error.
     */
    async getProduct() {
        if (this.error.status === 422) {
            return this;
        }

        try {

            /** @type{{boxes, options}} */
            const filtered = getUniqueIdsFromDirectIds(this.items);


            /** @type {Array<SupplierBox>||Aggregate}*/
            // IDs are already ObjectIds from helper
            const boxIds = filtered.boxes;
            
            const boxes = await SupplierBox.aggregate([
                {
                    $match: {
                        $and: [
                            { "tenant_id": this.supplier_id },
                            { "_id": { $in: boxIds } }
                        ]
                    }
                }, {
                    $project: {
                        _id: 1,
                        name: 1,
                        display_name: 1,
                        slug: 1,
                        system_key: 1,
                        incremental: 1,
                        sqm: 1,
                        linked: 1,
                        start_cost: 1,
                        calc_ref: 1,
                        appendage: 1
                    }
                }
            ]);
            
            // IDs are already ObjectIds from helper
            const optionIds = filtered.options;

            
            const op = await SupplierOption.aggregate([
                {
                    $match: {
                        $and: [
                            { "tenant_id": this.supplier_id },
                            { "_id": { $in: optionIds } }
                        ]
                    }
                }, {
                    $project: {
                        _id: 1,
                        name: 1,
                        display_name: 1,
                        slug: 1,
                        system_key: 1,
                        linked: 1,
                        rpm: 1,
                        sheet_runs: 1,
                        runs: 1,
                        additional: 1,
                        configure: 1
                    }
                }
            ]);
            


            let options = []

            const filteredCategoryId = this.category._id.toString();

            for (const option of op) {
                // Find the relevant configure object
                if (option.configure) {
                    const filteredConfig = option.configure.find(c => c.category_id.toString() === filteredCategoryId);
                    if (filteredConfig) {
                        // Merge configure properties into the option object
                        Object.assign(option, filteredConfig.configure);
                    }

                    // Remove the configure array from the result
                    delete option.configure;
                }

                options.push(option)
            }

            for (let item of this.items) {
                // Since we're using direct IDs, match by ID instead of slug
                let b = boxes.filter(box => box._id.toString() === item.key_id)
                /** check if box exists */
                if (b.length === 0) {
                    throwError(this.error, 'We couldn\'t find the selected box ' + item.key_id + ' in system.');
                }

                let o = options.filter(opt => opt._id.toString() === item.value_id)
                /** check if option exists */
                if (o.length === 0) {
                    throwError(this.error, 'The selected option ' + item.value_id + ' isn\'t available any more.');
                }
                let option = o[0];

                if (o[0].dynamic) {
                    option = Object.assign(o[0], { "_": item._ })
                }

                this.object.push({
                    "key_link": b[0].linked,
                    "divider": item.divider ??
                        getDividerByKey(this.boops.boops, item.key),
                    "appendage": b[0].appendage,
                    "dynamic": o[0].dynamic,
                    "key": item.key,
                    "value_link": o[0].linked,
                    "value": item.value,
                    "option_id": o[0]._id,
                    "box_calc_ref": b[0].calc_ref,
                    "option_calc_ref": option.additional?.calc_ref ? option.additional.calc_ref : null,
                    "box": b[0],
                    "option": option
                })
            }

            return this;

        } catch (e) {
            throwError(this.error, e.message ?? "Product not found.");
        }
    }

    /**
     * Retrieves the margin based on the supplied quantity from the specified API endpoint.
     *
     * @returns {Object} Object containing the result of the operation:
     * - result: Error (if an error occurred during the margin retrieval process)
     */
    async getMargin() {
        let margins;
        let general;
        try {
            await Axios.get(`${MarginService}margins/tenants/${this.supplier_id}/categories/${this.slug}`).then(res => {
                margins = res.data.length ? res.data.filter(margin => margin.status) : []
            })

            if (margins.length) {
                this.margins = margins[0].slots.filter(
                    m => (this.quantity >= parseInt(m.from) && this.quantity <= parseInt(m.to)) || (this.quantity >= parseInt(m.from) && parseInt(m.to) === -1)
                )
            }

            if (!this.margins?.length) {
                await Axios.get(`${MarginService}margins/suppliers/${this.supplier_id}/general`).then(res => {
                    general = res.data.length ? res.data.filter(margin => margin.status) : []
                })
                if (general.length) {
                    this.margins = general[0].slots.filter(
                        m => (this.quantity >= parseInt(m.from) && this.quantity <= parseInt(m.to)) || (this.quantity >= parseInt(m.from) && parseInt(m.to) === -1)
                    )
                }
            }
            return this;
        } catch (e) {
            throwError(this.error, this.error.message || e.message);
        }
    }

    getDiscount() {
        // Empty method - implementation to be added
    }

    /**
     * Perform matching operation based on the value of the `divided` property from the request.
     * If the `divided` property is truthy, calls `prepareDivided` method asynchronously and returns the result.
     * If the `divided` property is falsy, calls `prepareObject` method asynchronously and returns the result.
     *
     * @returns {Object} An object with the property `result` that can be an instance of Error.
     */
    async matcher() {
        let { divided } = this.request;
        divided = divided ?? this.boops.divided
        this.binding_method = filterByCalcRef(this.object, 'binding_method')
        this.binding_direction = filterByCalcRef(this.object, 'binding_direction')
        this.binding_material = filterByCalcRef(this.object, 'binding_material')
        this.binding_color = filterByCalcRef(this.object, 'binding_color')

        this.endpapers = filterByCalcRef(this.object, 'endpapers')
        if (divided) {
            return (await (this.prepareDivided()))
        } else {
            return (await (this.prepareObject()));
        }
    }

    /**
     * Prepares divided calculation results based on the provided object.
     * @returns {{result: Error}} Returns the prepared divided calculation results in the form of an object.
     */
    async prepareDivided() {
        try {
            const grouped = groupByDividerWithCalcRefCopy(this.object, ['format', 'printing-colors', 'printing_colors']);

            const grouped_results = [];
            for (const key in grouped) {
                if (grouped.hasOwnProperty(key)) {
                    const objects = grouped[key].data;
                    
                    // This check should be placed before checking for (grouped[key].calculateable)
                    const uncalculatable = Object.fromEntries(
                        Object.entries(grouped).filter(([k, value]) => !value.calculateable)
                    );

                    if (grouped[key].calculateable) {

                        let exclude = Object.fromEntries(
                            Object.entries(grouped).filter(([k, value]) => k !== key && value.calculateable)
                        );

                        // Check if objects is an array before iterating
                        if (Array.isArray(objects)) {

                            grouped_results.push({
                                [key]: this.getLewPrice(
                                    await (this.preCalculation(objects, filterByCalcRef(fetchDataKey(exclude), 'pages', ['material', 'weight'])))
                                    , objects
                                    , this.format
                                )
                            })
                        } else {
                            if (Object.keys(uncalculatable).length > 0) {
                                // Build error messages for each uncalculatable group
                                const errorMessages = Object.entries(uncalculatable).map(([divider, group]) => {
                                    const missing = group.missings && group.missings.length > 0
                                        ? group.missings.join(", ")
                                        : "unknown fields";
                                    return `Divider '${divider}' is missing required fields: ${missing}`;
                                });

                                // Throw all errors combined into one message
                                throwError(this.error, errorMessages.join(" | "));
                            }
                            // throwError(this.error, `Error: 'data' is not an array for group '${key}'.`);
                        }
                    }





                }
            }

            let output = {
                "type": "print",
                "connection": this.category.tenant_id,
                "external": "",
                "external_id": this.category.tenant_id,
                "external_name": this.category.tenant_name,
                "calculation_type": "full_calculation",

                "items": this.object,
                "product": this.items,
                "category": this.category,
                "margins": this.internal && this.margins?.length ? this.margins[0] : [],
                "divided": true,
                "quantity": this.quantity,
                "calculation": [],
                "prices": []
            };


            let prices = [];
            for (const key in grouped_results) {
                if (grouped_results.hasOwnProperty(key)) {
                    for (const k in grouped_results[key]) {
                        if (grouped_results[key].hasOwnProperty(k)) {
                            let price = formatPriceObject(
                                this.category,
                                this.quantity,
                                grouped_results[key][k].price,
                                grouped_results[key][k].dlv,
                                grouped_results[key][k].machine,
                                this.margins?.length ? this.margins[0] : [],
                                this.discount,
                                this.request.dlv,
                                this.internal,
                                parseFloat(this.vat),
                                this.vat_override,
                                true,
                                Object.values(grouped).filter(item => item.calculateable).length
                            );
                            output.calculation.push({
                                "name": k,
                                "items": grouped_results[key][k].items,
                                "dlv": grouped_results[key][k].dlv,
                                "machine": grouped_results[key][k].machine,
                                "color": grouped_results[key][k].color,
                                "row_price": grouped_results[key][k].row_price,
                                "duration": grouped_results[key][k].duration,
                                "price_list": grouped_results[key][k].calculation.price_list,
                                "details": grouped_results[key][k].calculation,
                                "price": price,
                                "error": grouped_results[key][k].error,
                            })

                            prices.push(price)
                        }
                    }
                }
            }

            output.prices = mergePriceObject(prices, this.supplier_id)
            return output;
        } catch (e) {
            throwError(this.error, e.message);
        }
    }

    /**
     * Prepare the object for further processing. This method performs various calculations
     * and transformations on the object properties and returns a formatted object.
     *
     * @returns {Object} The formatted object with specific properties such as type, connection, tenant_id, etc.
     *                  in the structure required for downstream processing.
     *                  In case of an error with status code 422, the original object is returned containing the error information.
     */
    async prepareObject() {
        try {
            this.combinations = await (this.preCalculation(this.object))

            const leastExpensiveOption = this.getLess();

            return {
                "type": "print",
                "connection": this.category.tenant_id,
                "external": "",
                "external_id": this.category.tenant_id,
                "external_name": this.category.tenant_name,
                "calculation_type": "full_calculation",
                "items": this.object,
                "product": this.items,
                "category": this.category,
                "margins": this.internal && this.margins?.length ? this.margins[0] : [],
                "divided": false,
                "quantity": this.quantity,
                "calculation": [{
                    "name": null,
                    "items": leastExpensiveOption.objects,
                    "dlv": leastExpensiveOption.dlv,
                    "machine": leastExpensiveOption.machine,
                    "color": leastExpensiveOption.color,
                    "row_price": leastExpensiveOption.row_price,
                    "duration": leastExpensiveOption.duration,
                    "price_list": leastExpensiveOption.calculation.price_list,
                    "details": leastExpensiveOption.calculation,
                    "price": formatPriceObject(
                        this.category,
                        this.quantity,
                        leastExpensiveOption.price,
                        leastExpensiveOption.dlv,
                        leastExpensiveOption.machine,
                        this.margins?.length ? this.margins[0] : [],
                        this.discount,
                        this.request.dlv,
                        this.internal,
                        parseFloat(this.vat),
                        this.vat_override
                    ),
                    "error": leastExpensiveOption.error,
                }],
                "prices": refactorPriceObject(formatPriceObject(
                    this.category,
                    this.quantity,
                    leastExpensiveOption.price,
                    leastExpensiveOption.dlv,
                    leastExpensiveOption.machine,
                    this.margins?.length ? this.margins[0] : [],
                    this.discount,
                    this.request.dlv,
                    this.internal,
                    parseFloat(this.vat),
                    this.vat_override
                ), this.supplier_id)
            };
        } catch (e) {
            throwError(this.error, e.message);
        }
    }

    /**
     * Perform pre-calculation based on the provided objects.
     *
     * @param {Array} objects - The array of objects to perform pre-calculation on.
     * @param content
     * @returns {Object} - An object with a 'result' property containing the calculated result or an Error object if pre-calculation fails.
     */
    async preCalculation(
        objects,
        content = []
    ) {
        try {
            // get the extra values from the request
            let {
                quantity,
                bleed,
                quantity_range_start,
                quantity_range_end,
                quantity_incremental_by,
                range_override
            } = this.request;
            quantity = quantity ? parseInt(quantity) : 0;
            bleed = bleed ? parseInt(bleed) : this.category?.bleed;
            quantity_range_start = quantity_range_start ? parseInt(quantity_range_start) : 0;
            quantity_range_end = quantity_range_end ? parseInt(quantity_range_end) : 0;
            quantity_incremental_by = quantity_incremental_by ? parseInt(quantity_incremental_by) : 0


            if (Array.isArray(objects)) {

                /** @internal format */
                let format = getDefaultFormat(filterByCalcRef(objects, 'format'));

                /** @internal pages */
                let pages = filterByCalcRef(objects, 'pages');
                /** @internal cover */
                let cover = filterByCalcRef(objects, 'cover');
                /** @internal sides */
                let sides = filterByCalcRef(objects, 'sides');
                /** @internal folding */
                let folding = filterByCalcRef(objects, 'folding');

                /** check if the format exists */
                if (!format.length) {
                    throwError(this.error, "The format parameter not specified.");
                }

                this.format = new Format(
                    this.category,
                    format[0].option,
                    quantity,
                    bleed,
                    quantity_range_start,
                    quantity_range_end,
                    quantity_incremental_by,
                    range_override,
                    pages,
                    cover,
                    this.binding_method,
                    this.binding_direction,
                    folding,
                    this.endpapers,
                    sides
                ).calculate();

                if (this.format.status !== 200) {
                    throwError(this.error, this.format.message);
                    this.error.message = this.format.message;
                    this.error.status = this.format.status;
                    return this;
                }

                /** @internal material */
                this.material = objects.filter(function (product) {
                    return product.box.calc_ref === 'material';
                });

                /** @internal weight */
                this.weight = objects.filter(function (product) {
                    return product.box.calc_ref === 'weight';
                });

                /** check if the wight exists */
                if (!this.weight.length) {
                    throwError(this.error, "The weight parameter not specified.");
                }

                /** @internal catalogue */
                this.catalogue = await (new FetchCatalogue(this.material, this.weight, this.supplier_id).get())

                if (this.catalogue.error.status === 422) {
                    throwError(this.error, this.catalogue.error.message);
                }

                if (content.length) {
                    const bypass_pages = filterByCalcRef(content, 'pages');
                    const bypass_material = filterByCalcRef(content, 'material');
                    const bypass_weight = filterByCalcRef(content, 'weight');

                    /** @internal catalogue */
                    const catalogue = await (new FetchCatalogue(bypass_material, bypass_weight, this.supplier_id).get())

                    if (catalogue.results.length) {
                        this.content = {
                            pass: true,
                            grs: catalogue.results[0].grs,
                            density: catalogue.results[0].density,
                            thickness: catalogue.results[0].thickness === Infinity ? 0 : catalogue.results[0].thickness,
                            pages: calculatePages(bypass_pages)
                        };
                    }
                }

                /**
                 * Represents the results variable.
                 * @type {Promise}
                 * @property {Machines} format - The format property.
                 * @property {Machines} material - The material property.
                 * @property {Machines} weight - The weight property.
                 * @property {Machines} catalogue.results - The catalogue results property.
                 * @property {Machines} machine - The machine property.
                 * @property {Machines} slug - The slug property.
                 * @property {Machines} supplier_id - The supplier ID property.
                 * @property {Machines} object - The object property.
                 * @property {Machines} request - The request property.
                 * @property {Machines} category - The category property.
                 * @method {prepare} prepare - The prepare method to prepare the results.
                 */
                let results = await (new Machines(
                    this.format, this.material, this.weight,
                    this.catalogue.results,
                    this.machine,
                    this.slug, this.supplier_id,
                    objects, this.request,
                    this.category,
                    this.content,
                    this.binding_method,
                    this.binding_direction,
                    this.endpapers
                ).prepare())

                let groups = this.groupBy(results, ({ type }) => type)

                if (!groups.hasOwnProperty('printing')) {
                    if (results?.status === 422 || results?.length === 0) {
                        throwError(this.error, results?.message ? results?.message : "There is no printing machine found.");
                    }
                }

                return combinations(groups);
            }
        } catch (e) {
            throwError(this.error, e.message);
        }
    }

    /**
     * Groups items in a list based on a provided key getter function.
     *
     * @param {{result: Error}} list - The list of items to be grouped.
     * @param {Function} keyGetter - A function that is used to extract the key from each item in the list.
     *                               This function should accept an item as the argument and return the key value.
     * @returns {Object} - An object that contains the grouped items where the keys are the extracted keys and the values are arrays of grouped items.
     *                    If no items match a specific key, the corresponding value in the object will be an empty array.
     */
    groupBy(list, keyGetter) {
        try {
            const map = {};
            list.forEach((item) => {
                const key = keyGetter(item);
                const collection = map[key];
                if (!collection) {
                    map[key] = [item];
                } else {
                    collection.push(item);
                }
            });
            return map;
        } catch (e) {
            throwError(this.error, e.message);
        }
    }

    /**
     * Retrieves the item with the lowest cost from an array of combinations.
     *
     * @returns {Object} The item with the lowest cost.
     */
    getLess() {
        try {
            const cost = [];
            for (let combination of this.combinations) {
                let obj = {
                    category: this.category,
                    items: this.items,
                    objects: this.object,
                    margins: this.margins?.length ? this.margins[0] : [],
                    row_price: 0
                };

                let amount_of_sheet = 0;
                let amount_of_sheets_printed = 0;
                let options = this.object;

                for (let key in combination) {
                    obj.category = this.category
                    obj.items = this.items;
                    obj.objects = this.object;
                    obj.margins = this.margins?.length ? this.margins[0] : [];

                    if (key === 'printing') {
                        amount_of_sheet = parseFloat(combination[key].results.calculation.amount_of_sheets_needed) || 0;
                        amount_of_sheets_printed = parseFloat(combination[key].results.calculation.amount_of_sheets_printed) || 0;

                        if (combination[key].results.color.status !== 422) {
                            obj.dlv = combination[key].results.calculation.color.dlv;
                        }

                        obj.machine = combination[key].results.machine;
                        obj.color = combination[key].results.calculation.color;
                        obj.duration = combination[key].results.duration;
                        obj.calculation = combination[key].results.calculation;
                        // Initialize row_price with printing cost
                        obj.row_price = parseFloat(combination[key].results.calculation.total_sheet_price) || 0;

                    }
                    else if (key === 'lamination') {
                        obj.laminate_machine = combination[key].results.machine;

                        if (combination[key].results.status === 422) {
                            this.error.message = combination[key].results.message;
                            this.error.status = combination[key].results.status;
                            continue;
                        }

                        options = this.object.filter(function(product) {
                            return product.box.calc_ref !== 'lamination';
                        });
                        // Convert run_price to number
                        const laminationRunPrice = parseFloat(combination[key].results.calculation.run_price) || 0;
                        const calculationMethod = combination[key].results.calculation.calculation_method;
                        const laminationStartCost = parseFloat(combination[key].results.calculation.start_cost);
                        const laminationOptionStartCost = parseFloat(combination[key].results.calculation.option_start_cost);
                        // Calculate additional cost based on method
                        if (calculationMethod === "sqm") {
                            const areaSqm = parseFloat(combination[key].results.calculation.area_sqm) || 0;
                            obj.row_price = parseFloat(obj.row_price) + (laminationRunPrice * areaSqm);
                        }
                        else if (calculationMethod === "lm") {
                            const formatSize = parseFloat(this.format.size.lm) || 0;
                            obj.row_price = parseFloat(obj.row_price) + (laminationRunPrice * (formatSize * this.format.quantity));
                        }
                        else if (calculationMethod === "sheet") {
                            obj.row_price = parseFloat(obj.row_price) + (laminationRunPrice * amount_of_sheets_printed);
                        }
                        else if (calculationMethod === "qty") {
                            obj.row_price = parseFloat(obj.row_price) + (laminationRunPrice * this.format.quantity);
                        }
                        obj.row_price += laminationStartCost;
                        obj.row_price += laminationOptionStartCost;

                    }

                    // Add category start cost if available
                    const startCost = this.category?.start_cost
                        ? parseFloat(this.category.start_cost) / 100000
                        : 0;

                    obj.row_price = parseFloat(obj.row_price) + parseFloat(startCost);
                }


                /** @internal  calculate the extra options */
                options.map(opt => {
                    let option = opt.option.runs.filter(run => run.category_id?.toString() === this.category._id.toString())[0];

                    let run = option?.runs.filter((run) =>
                        this.format.quantity >= parseInt(run.from) && this.format.quantity <= parseInt(run.to)
                    )

                    if (!run?.length) {
                        run = option?.runs.slice(-1);
                    }

                    let start_cost = parseFloat(option?.start_cost ?? 0) / 100000;
                    let run_price = parseFloat(run ? run[0]?.price ?? 0 : 0) / 100000;

                    obj.row_price += start_cost;
                    switch (opt.option.calculation_method) {
                        case "sqm":
                            obj.row_price += run_price * (this.format.quantity * this.format.size.m)
                            break;
                        case "lm":
                            obj.row_price += run_price * (this.format.size.lm * this.format.quantity)
                            break;
                        case "sheet":
                            obj.row_price += run_price * amount_of_sheets_printed
                            break;
                        case "qty":
                            obj.row_price += run_price * this.format.quantity
                            break;
                        default:
                            obj.row_price += run_price * this.format.quantity
                        // qty;
                    }
                })
                obj.price = Number(obj.row_price.toFixed(2).replace('.', ''));
                obj.error = this.error
                cost.push(obj)

            }
            return cost.filter(item => item.row_price === Math.min(...cost.map(item => item.row_price)))[0]
        } catch (e) {
            throwError(this.error, e.message);
        }
    }

    getLewPrice(
        combinations,
        items,
        format
    ) {
        try {
            const cost = [];
            for (let combination of combinations) {
                let obj = {};
                let amount_of_sheet = 0;
                let amount_of_sheets_printed = 0;
                let options = this.object;
                obj.objects = this.object;


                for (let key in combination) {
                    // obj.category = this.category
                    obj.items = items;
                    // obj.margins = this.margins?.length? this.margins[0]: [];
                    if (key === 'printing') {

                        amount_of_sheet = parseFloat(combination[key].results.calculation.amount_of_sheets_needed) || 0;
                        amount_of_sheets_printed = parseFloat(combination[key].results.calculation.amount_of_sheets_printed) || 0;

                        if (combination[key].results.color.status !== 422) {
                            obj.dlv = combination[key].results.calculation.color.dlv;
                        }

                        obj.machine = combination[key].results.machine;
                        obj.color = combination[key].results.calculation.color;
                        obj.duration = combination[key].results.duration;
                        obj.calculation = combination[key].results.calculation;
                        // Initialize row_price with printing cost
                        obj.row_price =  parseFloat(combination[key].results.calculation.total_sheet_price) +
                            parseFloat(combination[key].results.calculation.endpaper_total_sheet_price);

                    } else if (key === 'lamination') {
                        obj.laminate_machine = combination[key].results.machine;

                        if (combination[key].results.status === 422) {
                            continue;
                        }

                        options = this.object.filter(function(product) {
                            return product.box.calc_ref !== 'lamination';
                        });
                        // Convert run_price to number
                        const laminationRunPrice = parseFloat(combination[key].results.calculation.run_price) || 0;
                        const calculationMethod = combination[key].results.calculation.calculation_method;
                        const laminationStartCost = parseFloat(combination[key].results.calculation.start_cost);
                        const laminationOptionStartCost = parseFloat(combination[key].results.calculation.option_start_cost);
                        // Calculate additional cost based on method
                        if (calculationMethod === "sqm") {
                            const areaSqm = parseFloat(combination[key].results.calculation.area_sqm) || 0;
                            obj.row_price = parseFloat(obj.row_price) + (laminationRunPrice * areaSqm);
                        }
                        else if (calculationMethod === "lm") {
                            const formatSize = parseFloat(this.format.size.lm) || 0;
                            obj.row_price = parseFloat(obj.row_price) + (laminationRunPrice * (formatSize * format.quantity));
                        }
                        else if (calculationMethod === "sheet") {
                            obj.row_price = parseFloat(obj.row_price) + (laminationRunPrice * amount_of_sheets_printed);
                        }
                        else if (calculationMethod === "qty") {
                            obj.row_price = parseFloat(obj.row_price) + (laminationRunPrice * format.quantity);
                        }
                        obj.row_price += laminationStartCost;
                        obj.row_price += laminationOptionStartCost;
                    }

                    // Add category start cost if available
                    const startCost = this.category?.start_cost
                        ? parseFloat(this.category.start_cost) / 100000
                        : 0;

                    obj.row_price = parseFloat(obj.row_price) + parseFloat(startCost);
                }


                /** @internal  calculate the extra options */
                options.map(opt => {
                    let option = opt.option.runs.filter(run => run.category_id?.toString() === this.category._id.toString())[0];
                    let run = option?.runs.filter((run) =>
                        this.format.quantity >= parseInt(run.from) && this.format.quantity <= parseInt(run.to)
                    )

                    // if (!run?.length) {
                    //     run = option?.runs.slice(-1);
                    // }

                    let start_cost = parseFloat(option?.start_cost ?? 0) / 100000;
                    let run_price = parseFloat(run ? run[0]?.price ?? 0 : 0) / 100000;

                    obj.row_price += start_cost;
                    switch (opt.option.calculation_method) {
                        case "sqm":
                            obj.row_price += run_price * (this.format.quantity * this.format.size.m)
                            break;
                        case "lm":
                            obj.row_price += run_price * (this.format.size.lm * this.format.quantity)
                            break;
                        case "sheet":
                            obj.row_price += run_price * amount_of_sheets_printed
                            break;
                        case "qty":
                            obj.row_price += run_price * this.format.quantity
                            break;
                        default:
                            obj.row_price += run_price * this.format.quantity
                        // qty;
                    }
                })
                obj.price = Number(obj.row_price.toFixed(2).replace('.', ''));
                obj.error = this.error
                cost.push(obj)

            }
            return cost.filter(item => item.row_price === Math.min(...cost.map(item => item.row_price)))[0]
        } catch (e) {
            throwError(this.error, e.message);
        }
    }

    /**
     *
     * @returns {{result: Error}}
     */
    async getRunning() {
        /* call category */
        await (this.getCategory());
        await (this.getProduct());
        await (this.getMargin());
        return this.error.status === 422 ? {error: this.error} : this.matcher();
    }


}
