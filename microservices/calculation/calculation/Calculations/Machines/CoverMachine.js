const FetchProductionTime = require("../FetchProductionTime");
/**
 * @typedef Promise
 * @property {Error} result Rejection error object
 */
module.exports = class CoverMachine {

    /**
     *
     * @param slug
     * @param supplier_id
     * @param machine
     * @param items
     * @param request
     * @param category
     */
    constructor(
        machine,
        items,
        request,
        supplier_id,
        slug,
        category
    ) {
        this.machine = machine;
        this.items = items;
        this.request = request;
        let {quantity} = this.request;
        this.quantity = quantity;
        this.supplier_id = supplier_id;
        this.slug = slug;
        this.category = category;
        this.material = [];
        this.weight = [];
        this.format = {}
        // product cover
        this.coverWeight = [];
        this.coverFormat = {}
        this.coverColor = {}
        this.coverMaterial = {}

        this.sheets = {}
        this.dlv = {}
        this.error = {
            message: "",
            status: 200
        };
    }

    async run()
    {
        /** @internal material */
        this.coverMaterial = this.items.filter(function(product) {
            return product.key === 'cover-material';
        });

        if(!this.coverMaterial.length) {
            this.error.message = "The cover material parameter not specified.";
            this.error.status = 422
            return this.error
        }

        /** @internal weight */
        this.coverWeight = this.items.filter(function(product) {
            return product.key === 'cover-weight';
        });

        if(!this.coverWeight.length) {
            this.error.message = "The cover weight parameter not specified.";
            this.error.status = 422
            return this.error
        }

        if(parseInt(this.coverWeight[0].value) > this.machine.max_gsm || parseInt(this.coverWeight[0].value) < this.machine.min_gsm) {
            this.error.message = `The cover weight is not compatible ${this.coverWeight[0].value} with the machine ${this.machine.name}.`;
            this.error.status = 422
            return this;
        }

        /** @internal weight */
        this.coverColor = this.items.filter(function(product) {
            return product.key === 'cover-color';
        });

        if(!this.coverWeight.length) {
            this.error.message = "The cover color parameter not specified.";
            this.error.status = 422
            return this.error
        }

        let options = this.items.filter(function(product) {
            return product.key !== 'cover-material' || product.key !== 'cover-weight' || product.key !== 'cover-color' ;
        });

        let duration = (new FetchProductionTime([this.machine], this.coverColor, this.request)).get()

        return {
            type: "covering",
            results: {
                machine: this.machine,
                format: this.format,
                calculation: {}, //(new Calculations).calculatePrintingMachine(),
                color: this.coverColor,
                options: options,
                duration: duration
            }
        }
    }

}
