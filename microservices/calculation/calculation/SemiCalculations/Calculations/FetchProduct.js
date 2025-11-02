const SupplierBox = require("../../Models/SupplierBox");
const SupplierOption = require("../../Models/SupplierOption");
const Axios = require("axios");
const FetchCategory = require("../../Calculations/FetchCategory");
const MarginService = 'http://margin:3333/'
const {getDividerByKey, throwError, getUniqueIds } = require('../../Helpers/Helper')
const FixedPrice = require("./FixedPrice");
const SlidingScale = require("./SlidingScale");

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
     * @param {boolean} [internal=false] - Flag indicating if the product is internal.
     * @return {void}
     */
    constructor(
        slug,
        supplier_id,
        product,
        request,
        contract,
        internal = false
    ) {
        this.slug = slug;
        this.supplier_id = supplier_id;
        this.items = product;
        this.category = [];
        this.product = [];
        this.margin = [];
        this.object = [];
        this.status = 200;
        this.request = request;
        this.internal = internal;
        this.contract = contract;
        this.boops = {};
        this.error = {
            message: "",
            status: 200
        };
        let {quantity, vat} = this.request;
        this.vat = vat;
        this.vat_override = this.request.vat_override ?? null;
        this.quantity = parseInt(quantity);
    }

    /**
     * Retrieves supplier category information based on specified criteria.
     *
     * @returns {{ result: Error }} An object containing the category information if successful or an error object if not.
     */
    async getCategory() {
        try {
            const {category} = await ((new FetchCategory(this.slug, this.supplier_id)).getCategory());
            this.category = category;
            if (this.category.length === 0) {
                throwError(this.error, "Category not found.");
                return;
            }

            this.boops = this.category.boops[0];
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
            const filtered = getUniqueIds(this.items, this.boops.boops);

            /** @type {Array<SupplierBox>||Aggregate}*/
            const boxes = await SupplierBox.aggregate([
                {
                    $match: {
                        $and: [
                            {"tenant_id": this.supplier_id},
                            {"_id": {$in: filtered.boxes}}
                        ]
                    }
                }, {
                    $project: {
                        _id: 1,
                        name: 1,
                        slug: 1,
                        system_key: 1,
                        display_name: 1,
                        incremental: 1,
                        sqm: 1,
                        linked: 1,
                        start_cost: 1,
                        calc_ref: 1,
                        appendage: 1
                    }
                }
            ]);


            /**
             * @method filter()
             * @type {*[]|Aggregate<Array<any>>} SupplierOption
             */
            const op = await SupplierOption.aggregate([
                {
                    $match: {
                        $and: [
                            {"tenant_id": this.supplier_id},
                            {"_id": {$in: filtered.options}}
                        ]
                    }
                }, {
                    $project: {
                        _id: 1,
                        name: 1,
                        slug: 1,
                        system_key: 1,
                        display_name: 1,
                        // incremental_by: 1,
                        linked: 1,
                        // dimension: 1,
                        // dynamic: 1,
                        // unit: 1,
                        // width: 1,
                        // maximum_width: 1,
                        // minimum_width: 1,
                        // height: 1,
                        // maximum_height: 1,
                        // minimum_height: 1,
                        // length: 1,
                        // maximum_length: 1,
                        // minimum_length: 1,
                        // start_cost: 1,
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
                const filteredConfig = option.configure?.find(c => c.category_id.toString() === filteredCategoryId);

                if (filteredConfig) {
                    // Merge configure properties into the option object
                    Object.assign(option, filteredConfig.configure);
                }

                // remove the configure array from the result
                delete option.configure;
                options.push(option)
            }

            /**
             * @property options.filter
             * @property boxes.filter
             *
             */
            for (let item of this.items) {
                let b = boxes.filter(b => b.slug === item.key)
                /** check if box exists */
                if(b.length === 0 ) {
                    throwError(this.error,  'We couldn\'t find the selected box ' + item.key + ' in system.');
                }

                let o = options.filter(b => b.slug === item.value)
                /** check if option exists */
                if(o.length === 0 ) {
                    throwError(this.error,  'The selected option ' + item.value + ' isn\'t available any more.');
                }
                let option = o[0];

                if (o[0].dynamic) {
                    option = Object.assign(o[0], {"_": item._})
                }

                this.object.push({
                    "key_link" : b[0].linked,
                    "divider" :item.divider??
                        getDividerByKey(this.boops.boops, item.key ),
                    "appendage" : b[0].appendage,
                    "dynamic": o[0].dynamic,
                    "key" : item.key,
                    "value_link" : o[0].linked,
                    "value" :item.value,
                    "option_id" : o[0]._id,
                    "box" : b[0],
                    "option" : option
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
        let margins
        let general
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



    calculate()
    {
        try {
            let prices = [];
            if (this.category.calculation_method.filter(o=>o.active).some(o=>o.slug === 'fixed-price')) {

                prices = new FixedPrice(
                    this.supplier_id,
                    this.category,
                    this.object,
                    this.quantity,
                    this.request,
                    this.contract,
                    this.internal,
                    this.vat,
                    this.margins,
                    this.vat_override,
                ).get()

            } else {

                prices = new SlidingScale(
                    this.supplier_id,
                    this.category,
                    this.object,
                    this.quantity,
                    this.request,
                    this.contract,
                    this.internal,
                    this.vat,
                    this.margins,
                    this.vat_override,
                ).get()

            }

            let { divided } = this.request;
            divided = divided ?? this.boops.divided

            return {
                "type": "print",
                "connection": this.category.tenant_id,
                "external": "",
                "external_id": this.category.tenant_id,
                "external_name": this.category.tenant_name,
                "calculation_type": "semi_calculation",
                "items": this.object,
                "product": this.items,
                "category": this.category,
                "margins": this.internal && this.margins?.length ? this.margins[0] : [],
                "divided": divided,
                "quantity": this.quantity,
                "calculation": [],
                "prices": prices
            };

        } catch (e) {
            throwError(this.error, e.message);
        }
    }


    /**
     *
     * @returns {{result: Error}}
     */
    async getRunning() {
        await (this.getCategory());
        await (this.getProduct());
        await (this.getMargin());
        return this.error.status === 422 ? {error: this.error} : await (this.calculate());
    }


}
