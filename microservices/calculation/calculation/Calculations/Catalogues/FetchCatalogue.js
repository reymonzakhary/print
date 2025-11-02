const SupplierCatalogue = require("../../Models/SupplierCatalogue");
const {throwError, calculatePaperThickness} = require("../../Helpers/Helper");
/**
 * @typedef Promise
 * @property {Error} result Rejection error object
 */
module.exports = class FetchCatalogue {

    /**
     *
     * @param material
     * @param weight
     * @param supplier_id
     */
    constructor(
        material,
        weight,
        supplier_id
    ) {
        this.material = material;
        this.weight = weight;
        this.supplier_id = supplier_id;
        this.results = [];
        this.error = {
            message : "",
            status : 200
        };
    }

    /**
     * Retrieves catalogues based on supplier ID, material link, and weight link.
     *
     * @returns {Object} Returns an object with a "results" property and an "error" property.
     *                   If no catalogues are found, the "error" property will contain an error message and status.
     *                   Otherwise, the "results" property will be an array of catalogues.
     */
    async get() {

        /**
         * Retrieves catalogues that match the given criteria.
         *
         * @param {string} supplierId - The ID of the supplier.
         * @param {string} materialValueLink - The value link of the material.
         * @param {string} weightValueLink - The value link of the weight.
         * @param {Aggregate<Array<any>>|{result:Error}} catalogues - The value link of the weight.
         * @returns {Promise<Array<Object>>} - A promise that resolves to an array of catalogues matching the criteria.
         */
        const { supplier_id, material, weight} = this;
        const MATCH_FIELDS = {
            tenant_id: supplier_id,
            material_id: material[0]?.option_id,
            grs_id: weight[0]?.option_id,
            // grs: parseInt( weight[0]?.option.name.match(/\d+/) ? weight[0]?.option.name.match(/\d+/)[0] : '0', 10)
        };

        let catalogues = await (this.fetchCatalogues(MATCH_FIELDS));
        if (!this.cataloguesExist(catalogues)) {
            this.error.message = 'Catalog not found, please try again';
            this.error.status = 422;
            throwError(this.error, 'Catalog not found, please try again')
        }

        /**
         *
         * @property {boolean} catalogue.sheet
         * @property {number} catalogue.price
         * @property {number} catalogue.grs
         * @property {number} catalogue.width
         * @property {number} catalogue.height
         */
        for (const catalogue of catalogues) {
            let catalogue_price = catalogue.price / 100000;
            let amount_of_sqm_in_kg = 1000 / catalogue.grs;
            let price_sqm = 0
            let sqm_area = 0;
            let catalogue_meter_in_sqm = 0;
            let sqm =  catalogue.width * catalogue.height / 1000000;

            if(catalogue.sheet){
                this.results.push(this.sheet(
                    catalogue_price,
                    amount_of_sqm_in_kg,
                    price_sqm,
                    sqm_area,
                    catalogue,
                    sqm
                ))
            }else if (!catalogue.sheet) {
                this.results.push(this.roll(
                    catalogue_price,
                    amount_of_sqm_in_kg,
                    price_sqm,
                    sqm_area,
                    catalogue,
                    sqm
                ))
            }
        }

        return {
            results: this.results,
            error: {
                message: this.error.message,
                status: this.error.status
            }
        };
    }

    /**
     * Fetches catalogues based on specified match fields.
     *
     * @param {Object} matchFields - The match fields to filter the catalogues. Each key-value pair represents a field and its value.
     * @returns {Promise<Array<Object>|undefined>} - A promise that resolves to an array of catalogues that match the supplied match fields. Returns undefined if an error occurs.
     */
    async fetchCatalogues(matchFields) {
        try {
            return await SupplierCatalogue.aggregate([
                {
                    '$match': {
                        "$and": Object.entries(matchFields).map(([key, value]) => ({ [key]: value }))
                    }
                }
            ]);
        } catch (err) {
            return undefined;
        }
    }

    /**
     * Checks if an array of catalogues exists.
     *
     * @param {Array|[]|{result: Error}} catalogues - The array of catalogues.
     * @returns {boolean} - Returns true if the catalogues exist, otherwise false.
     */
    cataloguesExist(catalogues) {
        return !(typeof catalogues === 'undefined' || catalogues.length === 0);
    }

    /**
     *
     * Calculates the properties of a sheet based on the provided parameters.
     *
     * @param {number} catalogue_price - The price of the catalogue.
     * @param {number} amount_of_sqm_in_kg - The amount of square meters in one kilogram.
     * @param {number} price_sqm - The price per square meter.
     * @param {number} sqm_area - The area of the sheet in square meters.
     * @param {Object} catalogue - The catalogue object containing information about the sheet.
     * @param {number} sqm - The number of square meters in the sheet.
     *
     * @return {Object} - An object containing the calculated properties of the sheet:
     *   - art_nr {null|string} - The article number of the sheet.
     *   - density {number} - The density of the sheet.
     *   - lm_in_sqm {number} - The length multiplied by the width of the sheet in square meters.
     *   - length {*} - The length of the sheet.
     *   - grs {*} - The gross weight of the sheet.
     *   - lm_price {number*/
    sheet(
        catalogue_price,
        amount_of_sqm_in_kg,
        price_sqm,
        sqm_area,
        catalogue,
        sqm
    )
    {
        switch (catalogue.calc_type) {
            case "kg":
                price_sqm = catalogue_price / amount_of_sqm_in_kg; // price per m
                break;
            case "sqm":
                price_sqm = catalogue_price; // price per m
                break;
            default:
                price_sqm = catalogue_price / amount_of_sqm_in_kg;
        }

        let price_key = `${catalogue.calc_type}`+"_price";
        let results = {
            id: catalogue._id,
            supplier: catalogue.supplier,
            art_nr: catalogue.art_nr,
            material: catalogue.material,
            grs: catalogue.grs,
            ean: catalogue.ean,
            width: catalogue.width,
            height: catalogue.height,
            length: catalogue.length,
            density: catalogue.density,
            thickness: calculatePaperThickness(parseInt(catalogue.grs),parseFloat(catalogue.density)),
            price: catalogue.price,
            calc_type: catalogue.calc_type,

            sheet: catalogue.sheet,
            lm_price: 0,
            lm_in_sqm: 0,
            roll_in_sqm: 0,
            sqm_price: price_sqm,
            sheet_price: price_sqm * sqm,
            sheet_in_sqm: sqm,
            amount_of_sqm_in_kg: amount_of_sqm_in_kg,

        };
        results[price_key] = catalogue_price
        return results;
    }

    /**
     * Calculates various properties and prices for a roll of material.
     *
     * @param {number} catalogue_price - The price of the material in the catalogue.
     * @param {number} amount_of_sqm_in_kg - The amount of square meters in a kilogram of the material.
     * @param {number} price_sqm - The price per square meter of the material.
     * @param {number} sqm_area - The area in square meters of the material.
     * @param {object} catalogue - The details of the material in the catalogue.
     * @param {number} sqm - The amount of material in square meters.
     *
     * @returns {object} - An object containing various properties and prices for the roll of material.
     *                    Properties include id, supplier, art_nr, material, grs, ean, width, height,
     *                    length, density, price, calc_type, sheet, lm_price, lm_in_sqm, roll_in_sqm,
     *                    catalogue_meter_in_sqm, sqm_price, sheet_price, sheet_in_sqm, and amount_of_sqm_in_kg.
     */
    roll(
        catalogue_price,
        amount_of_sqm_in_kg,
        price_sqm,
        sqm_area,
        catalogue,
        sqm
    )
    {
        let lm_in_sqm = 1000 * catalogue.width / 1000000
        switch (catalogue.calc_type) {
            case "kg":
                price_sqm = catalogue_price / amount_of_sqm_in_kg; // price per m
                break;
            case "sqm":
                price_sqm = catalogue_price; // price per m
                break;
            case "lm":
                price_sqm = catalogue_price * lm_in_sqm; // price per m
                break;
            default:
                price_sqm = catalogue_price / amount_of_sqm_in_kg;
        }

        let price_key = `${catalogue.calc_type}`+"_price";

        let results = {
            id: catalogue._id,
            supplier: catalogue.supplier,
            art_nr: catalogue.art_nr,
            material: catalogue.material,
            grs: catalogue.grs,
            ean: catalogue.ean,
            width: catalogue.width,
            height: catalogue.height,
            length: catalogue.length,
            density: catalogue.density,
            thickness: calculatePaperThickness(catalogue.grs,catalogue.density),
            price: catalogue.price,
            calc_type: catalogue.calc_type,
            sheet: catalogue.sheet,
            lm_price: lm_in_sqm * price_sqm,
            lm_in_sqm: lm_in_sqm,
            roll_in_sqm: sqm,
            catalogue_meter_in_sqm: 1 / lm_in_sqm,
            sqm_price: price_sqm,
            sheet_price: 0,
            sheet_in_sqm: 0,
            amount_of_sqm_in_kg: amount_of_sqm_in_kg,

        };
        results[price_key] = catalogue_price
        return results;
    }

}
