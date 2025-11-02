class FixedPrice {
    constructor(result, pm, day, ext_runs, qty, format) {
        let start_cost = 0
        let cost = 0
        result[ "pm" ][ pm ][ day ].forEach(run => {

            let addPrice = 0, price

            let calculation_method = ext_runs.options[ run['option'] ]?.calculation_method

            price = this.calc_run_price(run, qty, calculation_method, format)

            if (run.mode === 'percentage') {
                addPrice = (price * parseInt(run[ 'add_price' ])) / 100
            } else if (run.mode === "fixed") {
                addPrice = parseInt(run[ 'add_price' ])
            }

            cost += parseInt(addPrice) + price

            start_cost += parseInt(run.start_cost)

        })


        return { start_cost, cost }
    }

    calc_run_price (run, qty, calculation_method, format) {
        let price = parseInt(run.ppp)

        if (calculation_method === 'sqm') {
            return format.size.m * qty * price
        }
        return run.ppp * parseInt(qty)
    }
}

module.exports = FixedPrice
