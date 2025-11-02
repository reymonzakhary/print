/**
 * @typedef Promise
 * @property {Error} result Rejection error object
 */
module.exports = class LaminateMachine {

    /**
     *
     * @param catalogue
     * @param machine
     * @param items
     * @param request
     * @param supplier_id
     * @param slug
     * @param category
     */
    constructor(
        catalogue,
        machine,
        items,
        request,
        supplier_id,
        slug,
        category
    ) {
        this.catalogue = catalogue;
        this.machine = machine;
        this.items = items;
        this.request = request;
        let {quantity, bleed} = this.request;
        this.quantity = quantity;
        this.bleed = bleed ? parseInt(bleed) : 0;
        this.supplier_id = supplier_id;
        this.slug = slug;
        this.category = category;
        this.material = [];
        this.weight = [];
        this.format = {}
        this.sheets = {}
        this.dlv = {}
        this.error = {
            message: "",
            status: 200
        };
    }
}
