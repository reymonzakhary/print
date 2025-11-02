const FetchCategory = require('../../Calculations/FetchCategory')
const CalculateDiscount = require('../../Calculations/CalculateDiscount')
const CalculateMargin = require('../../Calculations/CalculateMargin')
const CollectionCalculation = require('../../Calculations/CollectionPrices/CollectionCalculation')
const SupplierBoops = require('../../Models/SupplierBoops')
const FetchSupplierBoops = require('../../Calculations/FetchSupplierBoops')

module.exports = class CollectionPricesController {

    /**
     *
     * @param req
     * @param res
     * @returns {Promise<*>}
     */
    static async calculate(req, res) {
        let {supplier_id, slug} = req.params
        const {product, quantity} = req.body

        let supplierCategory = await (new FetchCategory(supplier_id, slug)).get()

        if (supplierCategory.error){
            res.status(200).send(supplierCategory.error)
            return res.end()
        }

        let result = await (new CollectionCalculation()).calculate(supplier_id, supplierCategory.category, product, quantity)
        res.send(result)
    }

}
