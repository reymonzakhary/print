const {filterByCalcRef, throwError, refactorPriceObject, formatPriceObject, calculateDeliveryDays,
    findDiscountSlot
} = require("../../Helpers/Helper");
const Format = require("../../Calculations/Config/Format");

module.exports = class SlidingScale {
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
        this.vat_override = vat_override;
        this.margins = margins;

        this.error = {
            message: "",
            status: 200
        };
    }

    /**
     * Computes and returns the total price based on the category start cost, selected item options, and the quantity.
     * The calculation method can include special formats and different price calculations such as square meters (sqm).
     *
     * @return {Object} An object representing the formatted price, including any necessary details such as VAT and margins.
     * @throws Will throw an error if format calculation fails or is invalid.
     */
    get() {

        const discount =  findDiscountSlot(this.contract, this.category._id.toString(),this.quantity);
        const maxQty = Math.max(...this.category.production_dlv.map(day => day.max_qty));
        if (this.quantity > maxQty) {
            this.quantity = maxQty;
        }
        let price = this.category.start_cost || 0;

        if (Array.isArray(this.items)) {

            let format =  filterByCalcRef(this.items, 'format');

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
                    [],
                    [],
                    [],
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

                let remainingQuantity = this.quantity;
                if (this.format) {
                    if (item.option.calculation_method == 'sqm') {
                        remainingQuantity = this.quantity * this.format.size.m
                    }

                    if (item.option.calculation_method == 'lm') {
                        remainingQuantity = this.quantity * this.format.size.lm;
                    }
                }
                if (runs.length > 0) {

                    const lastRunIndex = runs.length - 1;

                    for (let i = 0; i < runs.length; i++) {
                        const run = runs[i];

                        const maxInRun = run.to - run.from + 1;

                        if (remainingQuantity <= 0) break;

                        if (remainingQuantity <= maxInRun) {
                            if (this.format) {

                                switch (item.option.calculation_method) {
                                    case 'lm':
                                        price += run.price * remainingQuantity;
                                        remainingQuantity = 0;
                                        break;
                                    case 'sqm':
                                        price += remainingQuantity * run.price;
                                        remainingQuantity = 0;
                                        break;
                                    default:
                                        price += remainingQuantity * run.price;
                                        remainingQuantity = 0;
                                        break;
                                }

                            } else {
                                price += remainingQuantity * run.price;
                                remainingQuantity = 0;
                                break;
                            }

                        } else {
                            // Take full amount for the current run

                            if (this.format) {

                                switch (item.option.calculation_method) {
                                    case 'lm':
                                        price += run.price * maxInRun;
                                        remainingQuantity -= maxInRun;
                                        break;
                                    case 'sqm':
                                        price += maxInRun * run.price;
                                        remainingQuantity -= maxInRun;
                                        break;
                                    default:
                                        price += maxInRun * run.price;
                                        remainingQuantity -= maxInRun;
                                }

                            } else {
                                price += maxInRun * run.price;
                                remainingQuantity -= maxInRun;
                            }
                        }

                        // If this is the last run and there's still remaining quantity
                        if (i === lastRunIndex && remainingQuantity > 0) {

                            if (this.format) {

                                switch (item.option.calculation_method) {
                                    case 'lm':
                                        price += run.price * remainingQuantity;
                                        remainingQuantity = 0;
                                        break;
                                    case 'sqm':
                                        price += remainingQuantity * run.price;
                                        remainingQuantity = 0;
                                        break;
                                    default:
                                        price += remainingQuantity * run.price;
                                        remainingQuantity = 0;
                                        break;
                                }

                            } else {
                                price += remainingQuantity * run.price;
                                remainingQuantity = 0;
                            }

                        }
                    }
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
            this.vat_override? this.vat: this.category.vat,
            this.vat_override,
            false,
        ), this.supplier_id);
    }
}