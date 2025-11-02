const FetchItems = require('../Calculations/FetchItems');

module.exports = class ProductController {


    /**
     * Fetches items based on supplier ID, slug, and products.
     *
     * @param {object} req - The request object.
     * @param {object} res - The response object.
     * @returns {Promise<*>} A Promise that resolves with the fetched items.
     */
    static async index(req, res) {

        const {supplier_id} = req.params, {calculation_type, products} = req.body,
            /** Fetch the supplier category from mongoose */
            {items, error} = await (new FetchItems(supplier_id, products, calculation_type).getItems());
        if(error.status === 422) {
            return res.status(200).json({
                "message" : error.message ,
                "status" : error.status
            })
        }
        return res.send(items);
    }
}