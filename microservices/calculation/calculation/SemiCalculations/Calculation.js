const Format = require("../Calculations/Config/Format")
const SupplierBox = require("../Models/SupplierBox")
const SupplierOption = require("../Models/SupplierOption")
const FixedPrice = require("./BuildMethod/FixedPrice")
const SlidingScale = require("./BuildMethod/SlidingScale")
const { getAllKeysFromArrayObject,
    extractAllValuesFromArrayObject,
    getDividerByKey, formatPriceObject, mergePriceObject,
    refactorPriceObject, groupByDividerWithCalcRefCopy
} = require('../Helpers/Helper')
module.exports = class {
    quantity = 0
    runs = {
        "options": {}
    }
    categoryRunBuildMethod = {name: 'Fixed price', slug: 'fixed-price', active: true}
    calcMethodAllowable = ['fixed-price', 'sliding-scale']
    result = {}
    pms = []
    num_options = 0
    options = {}
    grater_day = []
    days = {}
    firstDayAvailable = {}
    _ppp = {}
    _category = null
    opt_runs = {};
    options_start_cost = 0;

    error = null

    constructor (tenant_id, category, product, quantity) {
        this.tenant_id = tenant_id
        this.category = category
        this.product = product
        this.quantity = quantity
    }

    async calculate() {
        let options = this.prepareOptions(
            await this.getOptions(this.product)
        )
        this.num_options = options.length
        
        let method = this.category.calculation_method.find(method => {
            return method.active
        })

        if (!method || !this.calcMethodAllowable.includes(method.slug)) {
            this.error = {
                message: "Sorry, We can't handle this Method!",
                status: 404
            }
            return this
        }

        this.categoryRunBuildMethod = method
        this.runs['category'] = this.category

        this.result["qty"] = parseInt(this.quantity)
        this.result["pm"] = {}

        if (this.num_options) {
            options.forEach(option => {
                this.options[option.slug] = option
                this.generate_run_price(option)
            })
        }
        
        let GeneratedPrice = this.generate_price()
        
        if (!Object.keys(GeneratedPrice).length) {
            this.error = {
                message: "This Product don't have prices !",
                status: 404
            }
        }

        return GeneratedPrice
    }

    get_runs(data, qty) {
        return data.filter(run => {
            if (parseInt(run.from) <= parseInt(qty) && parseInt(qty) <= parseInt(run.to))
                return run
        })
    }

    getDays(option_runs) {

        for (let run in option_runs) {
            let pm = option_runs[run].pm
            if (!this.days[pm])
                this.days[pm] = []
            let days = []

            if (option_runs[run]['dlv_production']) {
                option_runs[run]['dlv_production'].forEach(day => {
                    days.push(day.days)
                })
            } else {
                days = [
                    { days: 0, value: 0, mode: 'percentage' },
                    { days: 1, value: 0, mode: 'percentage' },
                    { days: 2, value: 0, mode: 'percentage' }
                ]
            }

            if (days.length)
                this.days[pm].push(days)
            else {
                this.days[pm].push([0])
                if (!this.result['pm'].hasOwnProperty(pm)) {
                    this.result['pm'][pm] = {}
                }
            }
        }
    }

    getOptionRuns(opt_pm, run, option) {
        for (let dlv of run.dlv_production) {
            let conc_day = "day_" + dlv.days
            if (!this.result['pm'].hasOwnProperty(opt_pm)) {
                this.result['pm'][opt_pm] = {}
            }
            if (!this.result['pm'][opt_pm].hasOwnProperty(conc_day)) {
                this.result['pm'][opt_pm][conc_day] = []
            }

            this.result["pm"][opt_pm][conc_day].push({
                'option': option.slug,
                'format': option.box?.calc_ref === 'format'? this.format : null,// @todo add enum for calc_refs
                'calculation_method': option.calculation_method,
                "start_cost": parseInt(option["start_cost"]),
                "ppp": parseInt(run['price']),
                "add_price": dlv.value ? dlv.value : 0,
                "mode": (dlv.mode) ? dlv.mode : "fixed",
            })

            if (!this.grater_day[opt_pm])
                this.grater_day[opt_pm] = 0

            if (dlv.days > this.grater_day[opt_pm]) {
                this.grater_day[opt_pm] = dlv.days
            }
            if (!this._ppp[opt_pm])
                this._ppp[opt_pm] = {}

            if (!this._ppp[opt_pm][option['slug']]) {
                this._ppp[opt_pm][option['slug']] = {
                    "start_cost": parseInt(option["start_cost"]),
                    "ppp": parseInt(run['price']),
                }
            }
        }
    }

    addItemsToDays(day, pm, currentDay) {
        let first = (this.firstDayAvailable[pm] === "Infinity") ? 0 : this.firstDayAvailable[pm]
        if (currentDay >= first) {
            if (this.result["pm"][pm][day].length !== (this.num_options)) {
                for (let i = parseInt(first); i <= (this.grater_day[pm]); i++) {
                    let dayConc = "day_" + i
                    if (!this.result["pm"][pm][dayConc]) {
                        this.result["pm"][pm][dayConc] = []
                    }

                    let all_options = Object.keys(this.options);
                    let options = this.result["pm"][pm][day].map(item => item['option'])
                    let diff = all_options.filter(x => !options.includes(x));
                    diff.forEach(add_item => {
                        if (this._ppp[pm] && this._ppp[pm][add_item]) {
                            this.result["pm"][pm][day].push({
                                'option': add_item,
                                "start_cost": parseInt(this._ppp[pm][add_item]['start_cost']),
                                "ppp": parseInt(this._ppp[pm][add_item]['ppp']),
                                "add_price": 0,
                                "mode": 'fixed',
                            })
                        } else {
                            this.emptyOptionRuns({
                                "name": add_item,
                                "start_cost": this.options[add_item].start_cost,
                            }, pm, dayConc)
                        }
                    })
                }
            }
        }
    }

    createPrice(pm, day) {
        /**
         * get Build Price Method Depending on Category Build Method
         */
        if (this.categoryRunBuildMethod.slug === 'fixed-price') {
            return new FixedPrice(this.result, pm, day, this.runs, this.quantity, this.format)
        } else if (this.categoryRunBuildMethod.slug === 'sliding-scale') {
            return new SlidingScale(this.result, pm, day, this.runs, this.quantity, this.format)
        }
    }

    emptyOptionRuns(option, opt_pm, conc_day) {
        this.result['pm'][opt_pm][conc_day].push({
            "option": option.name,
            "start_cost": (option.start_cost) ? option.start_cost : 0,
            "ppp": 0,
            "add_price": 0,
            "mode": "fixed",
        })
    }

    generate_price() {
        let result = {}
        let days = {}
        for (let pm in this.result.pm) {

            let minA = this.days[pm].map(a => Math.min.apply(null, a));
            this.firstDayAvailable[pm] = Math.max(...minA)

            let maxA = this.days[pm].map(a => Math.max.apply(null, a));
            this.grater_day[pm] = Math.max(...maxA)
            this.grater_day[pm] += 1

            for (let i = this.firstDayAvailable[pm]; i <= this.grater_day[pm]; i++) {
                let extraday = "day_" + i
                if (!this.result.pm[pm].hasOwnProperty(extraday))
                    this.result.pm[pm][extraday] = []
            }
            days[pm] = null


            if (!this.days.hasOwnProperty(pm)) {
                continue
            }

            if (!this.days[pm].length) {
                continue
            }

            /**
             * sort by Days and add days if not exists
             * @type {{}}
             */
            if (!days[pm]) {
                days[pm] = Object.keys(this.result.pm[pm]).sort().reduce((acc, key) => {
                    acc[key] = this.result.pm[pm][key];
                    return acc;
                }, {})
            }

            for (let day in days[pm]) {
                let currentDay = day.replace('day_', '')

                this.addItemsToDays(day, pm, currentDay)
                if (this.result["pm"][pm][day].length !== this.num_options) {
                    continue
                }
                let price = this.createPrice(pm, day)

                if (!parseInt(price.cost) && !parseInt(price.start_cost) && !price.all && !price.all_start_cost) {
                    continue
                }
                if (!result.hasOwnProperty(pm)) {
                    result[pm] = {}
                }
                if (!result[pm].hasOwnProperty(day)) {
                    result[pm][day] = []
                }
                if (!parseInt(price.cost) && !parseInt(price.start_cost) && !parseInt(this.options_start_cost) &&  !price.all && !price.all_start_cost) {
                    throw {
                        message: "No price available",
                        status: 404
                    }
                }

                result[pm][day].push({
                    "pm": pm,
                    "dlv": currentDay,
                    "prices": {
                        "start_cost": parseInt(price.start_cost),
                        "subtotal": parseInt(price.cost),
                        "total": parseInt(price.start_cost) + parseInt(price.cost) + this.options_start_cost,
                        "qty": this.result['qty']
                    }
                })
            }
        }
        return result

    }

    generate_run_price(option) {
        if (option.runs.length) {
            option.runs.map(run => {
                if (!this.runs.options.hasOwnProperty(option.slug)) {
                    this.runs.options[option.slug] = {}
                }
                if (!this.runs.options[option.slug].hasOwnProperty(run.pm)) {
                    this.runs.options[option.slug][run.pm] = null
                }
                this.runs.options[option.slug]['calculation_method'] = option.calculation_method
                if (!this.runs.options[option.slug][run.pm]) {
                    this.runs.options[option.slug][run.pm] = option.runs.filter(pm => {
                        return pm.pm === run.pm
                    })
                }
            })
        }
        let option_runs = this.get_runs(option.runs, this.quantity)
        this.getDays(option_runs);

        if (!option_runs.length) {
            this.options_start_cost += option.start_cost
        }
        option_runs.forEach(run => {
            if (!this.opt_runs.hasOwnProperty(option.slug)) {
                this.opt_runs[option.slug] = run
            }
            if (option_runs && run.dlv_production?.length) {
                this.getOptionRuns(run.pm, run, option)
            } else if (option_runs && !run.dlv_production?.length) {
                if (!this._ppp[run.pm])
                    this._ppp[run.pm] = {}
                if (!this._ppp[run.pm][option['slug']]) {
                    this._ppp[run.pm][option['slug']] = {
                        "start_cost": parseInt(option["start_cost"]),
                        "ppp": parseInt(run['price']),
                    }
                }
            }

        })
        return option
    }

    async getOptions(product) {
        // let options = []

        /** @type{Array} */
        const filtered_boxes = getAllKeysFromArrayObject(product);

        /** @type {Array<SupplierBox>||Aggregate}*/
        const boxes = await SupplierBox.aggregate([
            {
                $match: {
                    $and: [
                        {"tenant_id": this.supplier_id},
                        {"slug" : {$in: filtered_boxes}}//[ ...Object.keys(this.items)]}}
                    ]
                }
            },{
                $project: {
                    _id: 1,
                    name: 1,
                    slug: 1,
                    system_key: 1,
                    incremental: 1,
                    sqm: 1,
                    linked: 1,
                    start_cost: 1,
                    calc_ref: 1,
                    appendage: 1
                }
            }
        ]);

        /**
         * Removes the '_format' key from the 'this.items' object and returns a new object
         * @returns {Object} - The filtered object with the '_format' key removed
         */
        let items = extractAllValuesFromArrayObject(product);

        let options = [];

        for (const box in items) {
            let opt = items[box]

            let option = (await SupplierOption.aggregate([
                {
                    '$match': {
                        "$and": [
                            {"tenant_id": this.tenant_id},
                            {"slug": opt}
                        ]
                    }
                }

            ]))[0]
            if (option) {
                let supplierBox = (await SupplierBox.aggregate([{
                    '$match': {
                        "$and": [
                            {"tenant_id": this.tenant_id},
                            {"slug": box}
                        ]
                    }
                }]))[0]

                option = {...option, box: supplierBox}
                options.push(option)
            }
        }

        return options
    }

    prepareOptions(options) {
        let addOnOption = []
        let pms = []
        options.map(option => {
            let runs = option.runs.filter(run => {
                return run.category_id.toString() == this.category._id.toString()
            }).map(run => run.runs)[0]
            option.runs = runs? runs: []
            return {...option, runs}
        })

        /**
         * get all Printing method we have
         */
        options.forEach(item => {
            if(item.runs.length)
            {

                item.runs.forEach(run => {
                    if( run.pm === "all" ){
                        addOnOption.push(item)
                    }else{
                        pms.push(run.pm)
                    }
                })
            }
        })
        // get the format of paper
        options.forEach(option => {
            if (option.box.calc_ref === 'format') {
                this.format = new Format(
                    this.category,
                    option,
                    this.quantity,
                    0,
                    0,
                    0,
                    0,
                    0,
                    [],
                    [],
                    [],
                    [],
                    [],
                    []
                ).calculate();
            }
        });

        /**
         * check if we have printing method
         * sprite all to them or return pm as it
         */
        if(pms.length){
            if (addOnOption.length){
                addOnOption.forEach(option=>{
                    let runs = []
                    option.runs.forEach(run =>{
                        if(run.pm === "all") {
                            for (let pm in pms){
                                let r = {...run}
                                r.pm = pms[pm]
                                runs.push(r)
                            }
                        }else{
                            runs.push(run)
                        }
                    })
                    option.runs = runs
                })
            }
            options.concat(addOnOption)
        }
        return options
    }

}