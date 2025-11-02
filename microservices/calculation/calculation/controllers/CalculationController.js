const FetchProduct = require('../Calculations/FetchProduct');

module.exports = class CalculationController {

    /**
     *
     * @param req
     * @param res
     * @returns {Promise<*>}
     */
    static async calculate(req, res) {


        try {
            const {supplier_id, slug} = req.params, {contract,product} = req.body,
                /** Fetch the supplier category from mongoose */
                response = await (new FetchProduct(
                    slug,
                    supplier_id,
                    product,
                    req.body,
                    contract,
                    true
                ).getRunning());
            return res.send(response)
        } catch (e) {
            console.log(e)
            return res.status(200).json({
                "message" : e.message,
                "status" : 422
            })
        }
    }

}
