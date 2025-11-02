const axios = require('axios')

module.exports = class CalculationService {
    base_url

    constructor() {
        this.base_url = process.env.CALCULATION_BASE_URI
    }

    async obtainSemiCalculationNetPrices(supplier_id, slug, product, qty) {
        return axios.post(`${this.base_url}/shop/suppliers/${supplier_id}/categories/${slug}/products/calculate/price/semi/net`, {product, qty})
    }
}