
/**
 * @typedef Promise
 * @property {Error} result Rejection error object
 */
module.exports = class FetchEndpaper {

    /**
     *
     * @param {array} endpaper - The endpaper value for the constructor
     * @param {array} category - The category value for the constructor
     * @param {array} format - The category value for the constructor
     * @return {void}
     */
    constructor(
        endpaper,
        category,
        format
    ) {
        this.endpaper = endpaper;
        this.category = category;
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
    get() {

        if (!this.endpaper.length) {
            return {
                message: "No end paper available",
                status: 422
            }
        }

        let runs = [];
        let w = [];
        let h = [];
        let run = [];
        let quantity = [];

        if(this.endpaper.length > 0 && this.format.size.pages > 0) {
            const method = this.format.size.binding_method;
            if(this.format.size.is_sides === method.endpapers_calculation.is_sides) {
                quantity = method.endpapers_calculation.qty * this.format.o_qty;
                w = 0;
                h = 0;
                switch (this.format.size.binding_direction.value) {
                    case 'left':
                        w = this.format.size.width_with_bleed * method.endpapers_calculation.divided_by;
                        h = this.format.size.height_with_bleed
                        break;
                    case 'top':
                        h = this.format.size.width_with_bleed;
                        w = this.format.size.height_with_bleed * method.endpapers_calculation.divided_by;
                        break;
                }
                runs = this.endpaper[0].option.runs?.filter(m => String(m.category_id) === String(this.category[0]._id))[0];

                // later this should be error if not found
                if(!runs?.runs.length) {
                    this.message = "Runs are not available with the specified quantity.";
                    this.status = 422;
                }
                run = runs.runs.filter(r => quantity >= parseInt(r.from) && quantity <= parseInt(r.to))

                if (!run.length) {
                    this.message = "Runs are not available with the specified quantity.";
                    this.status = 422;
                }

            }

        }

        return {
            run: run,
            quantity: quantity,
            endpaper: this.endpaper[0],
            width: w,
            height: h,
            dlv: runs.dlv_production??[],
            price: run.length? run[0]?.price / 100000: 0,
            price_list: [],
            rpm: this.endpaper[0].rpm??[],
            message: this.message,
            status: this.status
        }
    }
}
