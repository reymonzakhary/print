'use strict'
const Axios = require("axios");
const MarginService = 'http://margin:3333/'

class CalculateMargin {
    /**
     * return margin and inject to the Response
     * @param prices
     * @param params
     * @param category
     * @returns {Promise<{length}|*>}
     */
    async calculate(prices, category) {
        return await Axios.get(
            `${MarginService}margins/tenants/${category.tenant_id}/categories/${category.slug}`)
            .then(function (response) {
                return JSON.stringify(response.data)
            })
            .then(data => {
                return this.makeCalculate(data, prices)
            }).catch(() => {
                return prices
            })
    }

    makeCalculate(data, prices){
        try {
            data = JSON.parse(data)
        }catch (e){
            return prices
        }
        let MarginBy = null // run | price

        if (data[0].status && data[0].mode === "run") {
            MarginBy = data[0]
        } else if (data[1].status && data[1].mode === "price") {
            MarginBy = data[1]
        }

        let result = {}
        for (let pm in prices) {
            for (let day in prices[pm]) {

                for (let item in prices[pm][day]) {
                    let run = this.getRun(
                        MarginBy,
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
    }

    runCalculate(price, run) {
        let PrintingPrice = price.prices.total
        let selling_price = PrintingPrice

        if (!run.length) {
            price.prices.margins = {}

        } else {
            price.prices.margins = {
                "type": run[0].type,
                "value": run[0].value,
            }
            /** check selling price */

            if (run[0].type !== 0 && run[0].value !== 0) {
                selling_price = (run[0].type === 'percentage') ?
                    PrintingPrice + (PrintingPrice * run[0].value) / 100 :
                    parseInt(PrintingPrice) + parseInt( Math.round(run[0].value) )
            }

        }
        price.prices.selling_price = Math.round(selling_price)
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

module.exports = new CalculateMargin()
