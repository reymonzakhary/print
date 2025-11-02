/**
 * @typedef Promise
 * @property {Error} result Rejection error object
 */
const {getAllKeysFromArrayObject, extractAllValuesFromArrayObject, throwError} = require("../Helpers/Helper");
const SupplierBox = require("../Models/SupplierBox");
const SupplierOption = require("../Models/SupplierOption");
const SupplierCategory = require("../Models/SupplierCategory");

/**
 * Exports the module containing functions and variables to be used in other modules.
 */
module.exports = class FetchCategory {

    /**
     * Creates a new object representing a product constructor.
     *
     * @param {string} slug - The slug of the product.
     * @param {string} supplier_id - The ID of the product's supplier.
     * @return {void}
     */
    constructor(
        slug,
        supplier_id,
    ) {
        this.slug = slug;
        this.supplier_id = supplier_id;
        this.category = {};
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
    async getCategory()
    {
        try {
            this.category = await SupplierCategory.aggregate([
                {
                    '$match': {
                        "$and": [
                            {"tenant_id": this.supplier_id},
                            {"slug": this.slug},
                            // {"published": true}
                        ]
                    }
                },{
                    "$lookup": {

                        "from": "supplier_boops",
                        "localField": "_id",
                        "foreignField": "supplier_category",
                        "as": "boops"
                    }
                },{
                    "$lookup": {

                        "from": "supplier_machines",
                        "localField": "additional.machine",
                        "foreignField": "_id",
                        "as": "machine"
                    }
                },{
                    "$project" : {
                        '_id': 1,
                        "machine": 1,
                        "boops": 1,
                        "display_name": 1,
                        "name": 1,
                        "price_build": 1,
                        "slug": 1,
                        "linked": 1,
                        "countries": 1,
                        "category_slug": 1,
                        'start_cost': 1,
                        "tenant_id": 1,
                        "tenant_name": 1,
                        "production_days" : 1,
                        "calculation_method" : 1,
                        "ref_id" : 1,
                        "ref_category_name" : 1,
                        "ranges": 1,
                        "limits": 1,
                        "range_list": 1,
                        "free_entry": 1,
                        "range_around" : 1,
                        "bleed": 1,
                        "vat": 1,
                        "production_dlv": 1,
                    }
                }
            ]);

            if (this.category.length === 0) {
                throwError(this.error, "Category not found.");
            }

            return {
                category: this.category[0]
            };

        } catch (e) {
            this.error.message = "Category not found."
            this.error.status = 422
            return this;
        }
    }
}