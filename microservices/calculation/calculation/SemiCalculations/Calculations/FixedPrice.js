const {refactorPriceObject, formatPriceObject, throwError, filterByCalcRef, calculateDeliveryDays,
    findDiscountSlot
} = require("../../Helpers/Helper");
const Format = require("../../Calculations/Config/Format");

/**
 * Class representing a fixed price calculation for a given set of parameters.
 */
module.exports = class FixedPrice {
    /**
     * Constructs an instance of the class with the given parameters.
     *
     * @param {string} supplier_id - The unique identifier for the supplier.
     * @param {array} category - The category of the items.
     * @param {array} items - The list of items.
     * @param {number} quantity - The quantity of items.
     * @param {object} request - The request details.
     * @param {boolean} internal - Indicates if the request is internal.
     * @param {number} vat - The VAT applied to the items.
     * @param {number} margins - The margins for the items.
     * @return {void} This constructor does not return a value.
     */
    constructor(
        supplier_id,
        category,
        items,
        quantity,
        request,
        contract = null,
        internal,
        vat,
        margins,
        vat_override = null,
    ) {
        this.supplier_id = supplier_id;
        this.category = category;
        this.items = items;
        this.quantity = quantity;
        this.request = request;
        this.contract = contract;
        this.internal = internal;
        this.vat = vat;
        this.margins = margins;
        this.vat_override = vat_override;
        // this.prices = []
        this.error = {
            message: "",
            status: 200
        };
    }

    /**
     * Calculates and returns pricing information for the items based on Fixed Price criteria.
     *
     * @return {Object} An object containing the detailed price breakdown for the items.
     * @throws Will throw an error if the format status is not 200.
     */
    get() {
        const discount = findDiscountSlot(this.contract, this.category._id.toString(),this.quantity);
        const maxQty = Math.max(...this.category.production_dlv.map(day => day.max_qty));
        if (this.quantity > maxQty) {
            this.quantity = maxQty;
        }
        let price = 0
        price += this.category.start_cost;

        if (Array.isArray(this.items)) {

            let format =  filterByCalcRef(this.items, 'format');
            // console.log(format);

            /** check if the format exists */
            if (format.length > 0) {
                format = format[0];

                this.format = new Format(
                    this.category,
                    format.option,
                    this.quantity,
                    false,
                    0,
                    0,
                    0,
                    false,
                    [],
                    []
                ).calculate();

                if (this.format.status !== 200) {
                    throwError(this.error, this.format.message);
                    this.error.message = this.format.message;
                    this.error.status = this.format.status;
                    return this;
                }
            }
        }


        for (const item of this.items) {
            const {start_cost = 0, runs = []} = item.option?.runs?.find(run => run.category_id.equals(this.category._id)) || {};

            if (runs.length > 0) {

                price += start_cost;
                let run = runs.find(r => this.quantity >= parseInt(r.from) && this.quantity <= parseInt(r.to));
                if (this.format) {
                    if (item.option.calculation_method == 'sqm') {
                        let quantitySqm = this.quantity * this.format.size.m;
                        run = runs.find(r => quantitySqm >= parseInt(r.from) && quantitySqm <= parseInt(r.to));
                    }

                    if (item.option.calculation_method == 'lm') {
                        let quantityLm = this.quantity * this.format.size.lm;
                        run = runs.find(r => quantityLm >= parseInt(r.from) && quantityLm <= parseInt(r.to));
                    }
                }

                const formatted = this.format ?? null;  // Since semi-calculated categories may not have a format box, in other words it maybe null
                const sizeM = formatted?.size?.m ?? null;

                if (run) {

                    price += this.calcPrice(
                        formatted,
                        item.option.calculation_method,
                        run.price,
                        sizeM,
                        this.quantity
                    );

                } else if (runs.length > 0) { // the quantity not in any run so calc as it's in the last run

                    let lastRun = runs.splice(-1)[0];

                    price += this.calcPrice(
                        formatted,
                        item.option.calculation_method,
                        lastRun.price,
                        sizeM,
                        this.quantity
                    );

                }
            }
        }

        // Prepare delivery days based on the category's production_dlv
        // check if `production_dlv` exists, calculate the delivery days
        const dlv = this.category?.production_dlv ? calculateDeliveryDays(this.quantity, this.category.production_dlv, this.category.production_days, this.request) : [];

        return refactorPriceObject(formatPriceObject(
            this.category,
            this.quantity,
            price / 1000,
            dlv,
            [],
            this.margins?.length ? this.margins[0] : [],
            discount,
            null,
            this.internal,
            this.vat_override ? this.vat: this.category.vat,
            this.vat_override,
            false,
        ), this.supplier_id);
    }


    /**
     * Calculates the total price based on the given parameters.
     */
    calcPrice(
        format,
        calculation_method,
        runPrice,
        m,
        quantity
    ) {
        let price = 0;
        if (format) {
            switch (calculation_method) {
                case 'lm':
                    price += (runPrice * parseFloat(format.size.lm)) * quantity;
                    break;
                case 'sqm':
                    price += (runPrice * parseFloat(m)) * quantity;
                    break;
                default:
                    price += runPrice * quantity;
            }
        } else {
            price += runPrice * quantity;
        }

        return price;
    }
}