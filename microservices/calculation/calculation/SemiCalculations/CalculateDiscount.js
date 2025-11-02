'use strict'
const Axios = require("axios");
const DiscountService = 'http://discount:3333/'

class CalculateDiscount {
    /**
     * Return discount and inject it to response
     * @param prices
     * @param category
     * @returns {Promise<{length}|*>}
     */
    async calculate(prices, category) {
        let supplier = category.ref_id ? category.ref_id : category.tenant_id;
        return await Axios.get(
            `${DiscountService}discounts/suppliers/${supplier}/tenants/${category.tenant_id}/categories/${category.slug}`)
            .then(function (response) {
                return JSON.stringify(response.data)
            }).then(data => {
                data = JSON.parse(data)
                let DiscountBy = null
                if (data[0].status === true && data[0].mode === "run") {
                    DiscountBy = data[0]
                } else if (data[1].status === true && data[1].mode === "price") {
                    DiscountBy = data[1]
                }
                let result = {}
                for (let pm in prices) {

                    for (let day in prices[pm]) {
                        for (let item in prices[pm][day]) {
                            let run = this.getRun(
                                DiscountBy,
                                prices[pm][day][item].prices.qty
                            )

                            let runPrice = this.runCalculate(prices[pm][day][item], run)
                            if (!result[pm])
                                result[pm] = {}
                            if (!result[pm][day])
                                result[pm][day] = []
                            result[pm][day].push(runPrice)
                        }
                    }
                }
                return result.length ? result : prices
            }).catch(() => {
                return prices
            })

    }

    runCalculate(price, run) {
        let PrintingPrice = price.prices.total
        let buying_price = PrintingPrice
        if (!run.length) {
            price.prices.discount = {}
        } else {
            /** check buying price */
            price.prices.discount = {
                "type": run[0].type,
                "value": run[0].value,
            }
            if (run[0].type !== 0 && run[0].value !== 0) {
                buying_price = (run[0].type === 'percentage') ?
                    PrintingPrice - Math.floor((PrintingPrice * run[0].value) / 100) :
                    parseInt(PrintingPrice) + parseInt(run[0].value)
            }

        }
        price.prices.buying_price = buying_price
        return price
    }

    getRun(runs, qty) {
        return runs.slots.filter(slot => {
            let to = slot.to;
            if (to === "-1") {
                to = qty
            }
            if (slot.from <= qty && qty <= to) {
                return slot
            }
        })

    }
}

module.exports = new CalculateDiscount()
