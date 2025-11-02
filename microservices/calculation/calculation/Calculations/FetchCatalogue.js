const SupplierCatalogue = require("../Models/SupplierCatalogue");

/**
 * @typedef Promise
 * @property {Error} result Rejection error object
 */
module.exports = class FetchCatalogue {

    /**
     *
     * @param material
     * @param weight
     * @param machine
     * @param request
     * @param format
     * @param supplier_id
     */
    constructor(
        material,
        weight,
        machine,
        request,
        format,
        supplier_id
    ) {
        this.material = material;
        this.weight = weight;
        this.machine = machine[0];
        this.request = request;
        this.format = format;
        this.supplier_id = supplier_id;
        this.catalogue = [];
        this.message = "";
        this.status = 200;
    }

    /**
     *
     * @returns {{result: Error}}
     */
    async get()
    {
        const {quantity, bleed} = this.request;

        this.quantity = parseInt(quantity);
        this.bleed = bleed?parseInt(bleed):0;

        this.catalogue = await SupplierCatalogue.aggregate([{
            '$match': {
                "$and": [
                    {"tenant_id": this.supplier_id},
                    {"material_link": this.material[0]?.value_link},
                    {"grs_link": this.weight[0]?.value_link},
                ]
            }
        }]);

        return this.pricePerSheet();
    }

    /**
     *
     * @returns {{weight_sqm: number, price: number, price_sqm: number}}
     */
    pricePerSheet()
    {
        if(this.machine !== undefined) {
            if(!this.catalogue.length) {
                this.status = 422;
                this.message = "There is no catalogues found for this product.";
                return this;
            }

            if(this.catalogue[0].calc_type === 'kg') {
                return this.calculatePerKg()
            }

        }
        this.status = 422;
        this.message = "Machine not found.";
    }

    /**
     *
     * @returns {{weight_sqm: number, price: number, price_sqm: number}}
     */
    calculatePerKg()
    {
        let catalogue_price = parseInt(this.catalogue[0].price) / 100000;
        let amount_of_sheets_in_kg = 1000 / parseInt(this.catalogue[0].grs);
        let price_sqm = catalogue_price / amount_of_sheets_in_kg;
        let machine_sqm = this.machine.width * this.machine.height /1000000;
        let price_per_sheet = price_sqm * machine_sqm;

        // Calculate the machine format ith paper
        let height_with_bleed = this.format.size.height + Math.pow(this.bleed , 2);
        let width_with_bleed = this.format.size.width + Math.pow(this.bleed , 2);

        // calculate the paper format amount
        let yyv = this.machine.height % height_with_bleed;
        let yxv = this.machine.height % width_with_bleed;

        let xyv = this.machine.width % height_with_bleed;
        let xxv = this.machine.width % width_with_bleed;
        //  calculate floor numbers left cross
        // height calculation
        let calc_hh = Math.floor(this.machine.height / height_with_bleed)
        let calc_hw = Math.floor(this.machine.height / width_with_bleed)
        // calculate floor numbers right cross
        // width calculation
        let calc_wh = Math.floor(this.machine.width / height_with_bleed)
        let calc_ww = Math.floor(this.machine.width / width_with_bleed)
        // Cross values
        let cross_1 = calc_hh * calc_ww;
        let cross_2 = calc_hw * calc_wh;
        // maxi is the amount of prints on one page
        let maxi = Math.max(cross_1, cross_2);

        let landscape = 0;
        let portrait = 0;

        if(yyv >= width_with_bleed) {
            landscape = cross_1 + calc_wh
        }

        if(xxv >= width_with_bleed){
            portrait = cross_2 + calc_wh
        }

        if(yxv >= width_with_bleed) {
            portrait = cross_2 + calc_wh
        }

        if(xyv >= width_with_bleed) {
            portrait = cross_1 + calc_wh
        }

        let mini = Math.min(cross_1, cross_2)

        let position = {}

        if(landscape > maxi) {
            position = {
                "Maximum prints on one sheet": landscape,
                "Landscape": landscape - mini,
                "Portrait": mini,
                "Amount of sheets needed": this.quantity / landscape
            }
        }else if(portrait > maxi) {
            position = {
                "Maximum prints on one sheet": portrait,
                "Portrait": portrait - mini,
                "Landscape": mini,
                "Amount of sheets needed": this.quantity / portrait
            }
        }

        let ps = ""
        if(maxi === cross_1) {
            ps = "Portrait"
        }else if(maxi === cross_2) {
            ps = "Landscape"
        }

        if(this.format.size.cm > this.machine.sqcm) {
            this.message = "The selected format is larger than the the current machine size";
            this.status = 422;
        }

        let start_cost = this.machine.divide_start_cost ?
            parseInt(this.machine.price??0) / maxi:
            parseInt(this.machine.price??0);

        let spoilage = this.machine.divide_start_cost ?
            parseInt(this.machine.spoilage??0) / maxi:
            parseInt(this.machine.spoilage??0);

        return {
            width_with_bleed : width_with_bleed,
            height_with_bleed : height_with_bleed,
            yyv : yyv,
            yxv : yxv,
            xyv : xyv,
            xxv : xxv,
            calc_hh : calc_hh,
            calc_hw : calc_hw,
            calc_wh : calc_wh,
            calc_ww : calc_ww,
            cross_1 : cross_1,
            cross_2 : cross_2,
            maxi : maxi,
            landscape : landscape,
            portrait : portrait,
            mini : mini,
            position : position,
            ps : ps,
            amount_of_sheets_needed: (this.quantity / maxi) + spoilage??0,
            spoilage: this.machine.spoilage??0,
            price_sqm: price_sqm,
            price_per_sheet: price_per_sheet,
            amount_of_sheets_in_kg: amount_of_sheets_in_kg,
            start_cost:  start_cost / 100000,
            machine_rpm: this.machine.rpm,
            machine_name: this.machine.name,
            pm: this.machine.pm,
            message: this.message,
            status: this.status
        }
    }

}
