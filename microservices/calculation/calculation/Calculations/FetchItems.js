/**
 * @typedef Promise
 * @property {Error} result Rejection error object
 */
const {getAllKeysFromArrayObject, extractAllValuesFromArrayObject} = require("../Helpers/Helper");
const SupplierBox = require("../Models/SupplierBox");
const Box = require("../Models/Box");
const Option = require("../Models/Option");
const SupplierOption = require("../Models/SupplierOption");

/**
 * Exports the module containing functions and variables to be used in other modules.
 */
module.exports = class FetchItems {

    /**
     * Creates a new instance of the Constructor class.
     *
     * @param {number} supplier_id - The ID of the supplier.
     * @param {object} product - The product information.
     * @param {object} calculation - The product information.
     */
    constructor(
        supplier_id,
        product,
        calculation
    ) {
        this.supplier_id = supplier_id;
        this.items = product;
        this.calculation = calculation;
        this.object = [];
        this.error = {
            message: "",
            status: 200
        };
    }

    /**
     * Retrieves items based on provided keys and values, filtering and aggregating boxes and options from the database.
     *
     * @returns {Object} An object containing items and error information.
     */
    async getItems()
    {
        try {
            /** @type{Array} */
            const filtered_boxes = getAllKeysFromArrayObject(this.items);

            /** @type {{result: Error}}*/
            const boxes = await (this.getBoxes(filtered_boxes))
            /**
             * Removes the '_format' key from the 'this.items' object and returns a new object
             * @returns {Object} - The filtered object with the '_format' key removed
             */
            let items = extractAllValuesFromArrayObject(this.items);

            /** @property boxes.length*/
            if(boxes.length === 0 || boxes.length !== filtered_boxes.length ) {
                this.error.message = "One or all boxes are not available, please try again later."
                this.error.status = 422
                return this;
            }

            /**
             * @method filter()
             * @type {{result: Error}} SupplierOption
             */
            const options = await (this.getOption(items))
            /** @property options.length */
            if(options.length === 0 || options.length !== Object.keys(items).length ) {
                this.error.message = "One or all options are not available, please try again later."
                this.error.status = 422
                return this;
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
                    this.error.message = 'We couldn\'t find the selected box ' + item.key + ' in system.';
                    this.error.status = 422
                    return this;
                }
                let o = options.filter(b => b.slug === item.value)
                /** check if option exists */
                if(o.length === 0 ) {
                    this.error.message = 'The selected option ' + item.value + ' isn\'t available any more.';
                    this.error.status = 422
                    return this;
                }
                let option = o[0];

                if(o[0].dynamic) {
                    option = Object.assign(o[0], {"_": item._})
                }
                this.object.push({
                    "key_link" : b[0].linked,
                    "divider" :item.divider??false,
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
            return {
                items: this.object,
                error: this.error
            };

        } catch (e) {
            this.error.message = e.message ?? "Product not found."
            this.error.status = 422
            return this;
        }
    }


    async getBoxes(
        filtered_boxes
    )
    {
        if(this.calculation === 'open_product') {
            return Box.aggregate([
                {
                    $match: {
                        $and: [
                            {"slug": {$in: filtered_boxes}}//[ ...Object.keys(this.items)]}}
                        ]
                    }
                }, {
                    $project: {
                        _id: 1,
                        name: 1,
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
            ])
        }

        /** @type {Array<SupplierBox>||Aggregate}*/
        return SupplierBox.aggregate([
            {
                $match: {
                    $and: [
                        {"tenant_id": this.supplier_id},
                        {"slug": {$in: filtered_boxes}}//[ ...Object.keys(this.items)]}}
                    ]
                }
            }, {
                $project: {
                    _id: 1,
                    name: 1,
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
    }

    async getOption(
        items
    )
    {
        if(this.calculation === 'open_product') {
            return Option.aggregate([
                {
                    $match: {
                        $and: [
                            {"slug": {$in: [...Object.values(items)]}}
                        ]
                    }
                }, {
                    $project: {
                        _id: 1,
                        name: 1,
                        slug: 1,
                        system_key: 1,
                        incremental_by: 1,
                        linked: 1,
                        dimension: 1,
                        dynamic: 1,
                        unit: 1,
                        width: 1,
                        maximum_width: 1,
                        minimum_width: 1,
                        height: 1,
                        maximum_height: 1,
                        minimum_height: 1,
                        length: 1,
                        maximum_length: 1,
                        minimum_length: 1,
                        start_cost: 1,
                        rpm: 1,
                        sheet_runs: 1,
                        runs: 1,
                    }
                }
            ])
        }
        return SupplierOption.aggregate([
            {
                $match: {
                    $and: [
                        {"tenant_id": this.supplier_id},
                        {"slug": {$in: [...Object.values(items)]}}
                    ]
                }
            }, {
                $project: {
                    _id: 1,
                    name: 1,
                    slug: 1,
                    system_key: 1,
                    incremental_by: 1,
                    linked: 1,
                    dimension: 1,
                    dynamic: 1,
                    unit: 1,
                    width: 1,
                    maximum_width: 1,
                    minimum_width: 1,
                    height: 1,
                    maximum_height: 1,
                    minimum_height: 1,
                    length: 1,
                    maximum_length: 1,
                    minimum_length: 1,
                    start_cost: 1,
                    rpm: 1,
                    sheet_runs: 1,
                    runs: 1,
                }
            }
        ]);
    }
}