const SupplierOption = require("../Models/SupplierOption");

/**
 * @typedef Promise
 * @property {Error} result Rejection error object
 */
module.exports = class FetchColor {

    constructor(
        color,
        machine,
        format
    ) {
        this.color = color;
        this.machine = machine;
        this.format = format;
        this.message = "";
        this.status = 200
    }


    /**
     * Retrieves the necessary data for a given quantity.
     *
     * @async
     * @returns {Object} The data for the given quantity.
     */
    async get() {
        /**
         * @type {Aggregate<Array<any>>|any}
         */
        let option = await SupplierOption.aggregate([
            {
                '$match': {
                    "_id": this.color[0].option_id,
                    "sheet_runs.machine": this.machine._id

                }
            }, {
                "$project": {
                    'rpm': 1,
                    'start_cost': 1,
                    "sheet_runs": 1,
                    "slug": 1
                }
            }
        ])

        if (!option.length) {
            return {
                dlv: [],
                price: 0,
                rpm: 0,
                message: "Printing color is empty, add it to the selected machine first.",
                status: 422
            }
        }

        const range_list = this.format.range.find(x => x.pm === this.machine.pm)?.range_list;
        const sheet_runs = option[0].sheet_runs.filter(m => String(m.machine) === String(this.machine._id))[0];
        const quantity = this.format.quantity;
        let price_list=  [];
        // later this should be error if not found
        if (typeof range_list !== "undefined") {
            price_list = range_list.map(this.calculatePriceList.bind(this, sheet_runs));
            price_list = price_list.filter(function (el) {
                return el != null;
            });
        }

        let run = sheet_runs.runs.filter(function (r) {
            return quantity >= parseInt(r.from) && quantity <= parseInt(r.to)
        })

        if (!run.length) {
            this.message = "Runs are not available with the specified quantity.";
            this.status = 422;
        }

        return {
            run: run,
            runs: sheet_runs.runs,
            dlv: sheet_runs.dlv_production,
            price: run[0]?.price / 100000,
            price_list: price_list,
            rpm: option[0].rpm,
            message: this.message,
            status: this.status
        }
    }

    /**
     * Calculates the price list based on the given sheet runs and quantity.
     *
     * @param {Object} sheet_runs - The sheet runs object containing the price ranges.
     * @param {number} q - The quantity for which the price list is to be calculated.
     * @returns {{qty: number, runs: Array}} - The calculated price list containing quantity and matching runs.
     */
    calculatePriceList(
        sheet_runs,
        q
    ) {
        let run = sheet_runs.runs.filter(function (r) {
            return q >= parseInt(r.from) && q <= parseInt(r.to)
        })[0];

        if(run && run.price) {
            return {
                qty: q,
                runs: run,
                price: run.price / 100000
            };
        }
    }
}
