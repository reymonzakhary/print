class SlidingScale {
    constructor(result, pm, day, ext_runs, total_qty, format) {
        let start_cost = 0
        let cost = 0
        let dayInt = parseInt(day.replace('day_', ''));

        result[ "pm" ][ pm ][ day ].forEach(item => {
            let qty_left = parseInt(total_qty)

            if (ext_runs.options[ item[ 'option' ] ] && ext_runs.options[ item[ 'option' ] ] !== undefined) {
                let runs = ext_runs.options[ item[ 'option' ] ][ pm ]
                let calculation_method = ext_runs.options[ item[ 'option' ] ][ 'calculation_method' ]

                for (let run in runs) {
                    if (qty_left <= 0) {
                        continue
                    }
                    let qty = 0;
                    let addPrice = 0

                    if (parseInt(total_qty) >= parseInt(runs[ run ].from) && parseInt(total_qty) <= parseInt(runs[ run ].to)) {
                        let dayValue = runs[ run ].dlv_production.find(RunDay => {
                            return parseInt(RunDay.days) === parseInt(dayInt)
                        })

                        if (dayValue) {
                            if (dayValue.mode === 'percentage') {
                                addPrice = parseInt(runs[ run ].price) * parseInt(dayValue.value) / 100
                            } else if (dayValue.mode === "fixed") {
                                addPrice = parseInt(dayValue.value)
                            }

                        }
                    }

                    if (parseInt(total_qty) >= parseInt(runs[ run ].to)) {
                        qty = (parseInt(runs[ run ].to) - parseInt(runs[ run ].from)) + 1
                    } else {
                        qty = parseInt(qty_left)
                    }

                    cost += this.calc_run_price(
                        runs[ run ],
                        parseInt(qty),
                        calculation_method,
                        format
                    ) + addPrice
                    qty_left -= qty
                }

                start_cost += parseInt(item.start_cost)
            }

        })
        return {
            start_cost, cost
        }
    }

    calc_run_price (run, qty, calculation_method, format) {
        let price = parseInt(run.price) / 100000

        if (calculation_method === 'sqm') {
            return format.size.m * qty * price
        }
        return price * parseInt(qty)
    }
}

module.exports = SlidingScale
