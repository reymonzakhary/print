'use strict'

const { default: mongoose } = require("mongoose");
const SupplierProduct = require("../../Models/SupplierProduct");
const FetchProduct = require("../FetchProduct");
const CalculationService = require("../../Services/CalculationService");
const ObjectId = mongoose.Schema.Types.ObjectId


class CollectionCalculation {
    tenant_id = null
    slug = null
    product = {}
    quantity = 1

    constructor() { }

    /**
     *
     * @param req
     * @param res
     * @returns {Promise<*>}
     */
    async calculate(tenant_id, category, product, quantity) {

        this.tenant_id = tenant_id
        this.slug = category.slug
        this.product = product
        this.quantity = quantity

        let prices = {}
        if (product == null) {
            return prices
        }

        let productsPrice = await this.getProducts(tenant_id, category, product)

        if (!productsPrice) { return this.priceFormat([], category) }

        let Price = productsPrice.prices

        for (let p in Price) {
            let pm = Price[p].tables.pm.toLowerCase()
            if (!prices.hasOwnProperty(pm)) {
                prices[pm] = {}
            }

            if (!prices[pm].hasOwnProperty(["day_" + Price[p].tables.dlv.days])) {
                prices[pm]["day_" + Price[p].tables.dlv.days] = []
            }

            prices[pm]["day_" + Price[p].tables.dlv.days].push({
                'pm': pm,
                'dlv': Price[p].tables.dlv.days,
                'prices': {
                    "start_cost": 0,
                    "subtotal": Price[p].tables.p,
                    "total": Price[p].tables.p,
                    "qty": Price[p].tables.qty
                }
            })
        }

        return this.priceFormat(prices, category)
    }

    async getProducts(tenant_id, category, product) {
        delete product['_format'] // delete custom format

        return (new FetchProduct()).whereObject(product).where([
            {'tenant_id': {$eq: tenant_id}},
            {'supplier_category': {$eq: category._id}}
        ]).first()
    }

    priceFormat(data, category) {
        // return data
        let clearResult = []
        for (let item in data) {
            for (let days in data[item]) {
                for (let num in data[item][days]) {
                    let newFormat = data[item][days][num]["prices"]
                    newFormat["addons_start_cost"] = (newFormat["addons_start_cost"]) ? newFormat["addons_start_cost"] : 0
                    newFormat["addons_subtotal"] = (newFormat["addons_subtotal"]) ? newFormat["addons_subtotal"] : 0
                    newFormat["addons_total"] = (newFormat["addons_total"]) ? newFormat["addons_total"] : 0

                    newFormat["pm"] = data[item][days][num]["pm"]

                    newFormat["dlv"] = data[item][days][num]["dlv"]
                    newFormat["start_cost"] = newFormat["start_cost"] + newFormat["addons_start_cost"]

                    newFormat["subtotal"] = newFormat["subtotal"] + newFormat["addons_start_cost"] - newFormat["addons_start_cost"]
                    newFormat["total"] = newFormat["total"] + newFormat["addons_total"]
                    newFormat["discount"] = (newFormat["discount"]) ? newFormat["discount"] : {}

                    newFormat["margins"] = (newFormat["margins"]) ? newFormat["margins"] : {}
                    newFormat["buying_price"] = (newFormat["buying_price"]) ? newFormat["buying_price"] : newFormat["total"]
                    newFormat["selling_price"] = (newFormat["selling_price"]) ? newFormat["selling_price"] : newFormat["total"]
                    newFormat["profit"] = newFormat["selling_price"] + newFormat["addons_total"] - newFormat["buying_price"]

                    data[item][days][num] = {
                        "pm": newFormat["pm"],
                        "qty": data[item][days][num]['prices'].qty,
                        "dlv": newFormat["dlv"],
                        "p": newFormat["total"],
                        "ex": newFormat["addons_total"],
                        "ppp": newFormat["total"] / data[item][days][num]['prices'].qty,
                        "gross_price": newFormat["total"],
                        "buying_price": newFormat["buying_price"],
                        "selling_price": newFormat["selling_price"],
                        "profit": newFormat["profit"],
                        "discount": newFormat["discount"],
                        "margins": newFormat["margins"],
                        "data": {
                            "production_days": category.production_days,
                            "countries": category.countries,
                        }
                    }

                    clearResult.push(
                        {
                            "pm": newFormat["pm"],
                            "tables": data[item][days][num]
                        }
                    )
                }

            }
        }
        return clearResult
    }
}

module.exports = CollectionCalculation
