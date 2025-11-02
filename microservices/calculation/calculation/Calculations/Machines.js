const FetchColor = require("./FetchColor");
const FetchProductionTime = require("./FetchProductionTime");
const CoverMachine = require("./Machines/CoverMachine");
const PrintMachine = require("./Machines/PrintMachine");
const FetchEndpaper = require("./FetchEndpaper");
const {filterByCalcRef} = require("../Helpers/Helper");
/**
 * @typedef Promise
 * @property {Error} result Rejection error object
 */
module.exports = class Machines {

    /**
     *
     * @param format
     * @param material
     * @param weight
     * @param catalogues
     * @param machines
     * @param slug
     * @param supplier_id
     * @param product
     * @param request
     * @param category
     * @param content
     * @param binding_method
     * @param binding_direction
     * @param endpapers
     */
    constructor(
        format,
        material,
        weight,
        catalogues,
        machines,
        slug,
        supplier_id,
        product,
        request,
        category,
        content = {},
        binding_method = {},
        binding_direction = {},
        endpapers = {}
    ) {
        this.format = format;
        this.material = material;
        this.weight = weight;
        this.catalogues = catalogues;
        this.machines = machines;
        this.results = [];
        this.content = content
        this.binding_method = binding_method
        this.binding_direction = binding_direction
        this.endpapers = endpapers
        // not used
        this.slug = slug;
        this.supplier_id = supplier_id;
        this.items = product;
        this.status = 200;
        this.request = request;
        this.category = category;
        this.error = {
            message: "",
            status: 200
        };
    }

    /**
     * Prepare method that processes different types of machines based on their type
     *
     * @returns {{result: Error}} Object containing the results and any potential errors encountered during preparation
     */
    async prepare()
    {
        let options = [];


        for(let machine of this.machines) {
            if (machine.type === "covering") {
                let result = await (new CoverMachine(machine, this.items, this.request, this.supplier_id, this.slug, this.category).run())
                if (result.status === 422){
                    continue
                }
                this.results.push(result);
            }

            /**
             *
             */
            if(machine.type === "printing") {

                if(parseInt(this.weight[0].value) > machine.max_gsm || parseInt(this.weight[0].value) < machine.min_gsm) {
                    this.error.message = "The selected weight is not acceptable for the available machines.";
                    this.error.status = 422;
                    continue;
                }

                /** @internal color */
                let color = this.items.filter(function(product) {
                    return product.box.calc_ref === 'printing_colors';
                });

                if(color.length === 0) {
                    this.error.message = "The selected product missing the colors!";
                    this.error.status = 422;
                    continue;
                }

                this.color = await (new FetchColor(color, machine, this.format).get());

                if(this.color.status === 422) {
                    this.error.message = this.color.message;
                    this.error.status = this.color.status;
                    continue;
                }

                // rest of the output
                options = this.items.filter(function(product) {
                    return product.box.calc_ref !== 'printing-colors' &&
                        product.box.calc_ref !== 'printing_colors' &&
                        product.box.calc_ref !== 'weight' &&
                        product.box.calc_ref !== 'material' &&
                        product.box.calc_ref !== 'format' &&
                        // new
                        product.box.calc_ref !== 'endpapers' &&
                        product.box.calc_ref !== 'pages' &&
                        // product.box.calc_ref !== 'binding_material' &&
                        // product.box.calc_ref !== 'binding_color' &&
                        product.box.calc_ref !== 'binding_direction' &&
                        product.box.calc_ref !== 'binding_method' &&
                        product.box.calc_ref !== 'cover';
                });
                /** calculate endpaper */
                let endpaper = new FetchEndpaper(
                    this.endpapers,
                    this.category,
                    this.format
                ).get();

                if (endpaper.status !== 200) {
                    endpaper = [];
                }

                let calculations = new PrintMachine(
                    machine,
                    this.catalogues,
                    this.format,
                    this.color,
                    this.content,
                    endpaper,
                    this.request
                ).calculate();

                if(calculations.status === 422) {
                    this.error.message = calculations.message;
                    this.error.status = calculations.status;
                    continue;
                }

                this.duration = await (new FetchProductionTime(machine, color,this.format, calculations).get())

                this.results.push({
                    type: "printing",
                    results: {
                        machine: machine,
                        format: this.format,
                        calculation: calculations,
                        color: this.color,
                        options: options,
                        duration: this.duration
                    }
                })
            }


            /**
             *
             */
            if(machine.type === "lamination") {
                if(options.length) {
                    /** @internal color */
                    // let lamination = this.items.filter(function(product) {
                    //     return product.box.calc_ref === 'lamination'|| 'finishing';
                    // });
                    let lamination = filterByCalcRef(this.items, 'lamination')

                    if (!lamination.length) {
                        this.results.push({
                            type: "lamination",
                            results: {
                                machine: machine.name,
                                format: this.format,
                                calculation: {
                                    price_in_sqm: 0,
                                    sheet_sqm: this.format.size.m,
                                    area_sqm: 0,
                                    price: 0,
                                    run: [],
                                    run_price: 0
                                },
                                color: this.color,
                                options: options,
                                duration: this.duration,
                                messages: "Lamination option is empty, add it to the selected machine first.",
                                status: 422
                            }
                        });
                        continue;
                    }

                    if(!lamination[0].option.sheet_runs.length) {
                        this.results.push({
                            type: "lamination",
                            results: {
                                machine: machine.name,
                                format: this.format,
                                calculation: {
                                    price_in_sqm: 0,
                                    sheet_sqm: this.format.size.m,
                                    area_sqm: 0,
                                    price: 0,
                                    run: [],
                                    run_price: 0
                                },
                                color: this.color,
                                options: options,
                                duration: this.duration,
                                messages: "Lamination option is empty, add it to the selected machine first.",
                                status: 422
                            }
                        });
                        continue;
                    }
                    // use the machine
                    let start_cost = parseInt(machine.price) / 100000;
                    let area_sqm = this.format.quantity * this.format.size.m

                    // rest of the output
                    options = options.filter(function(product) {
                        return product.box.calc_ref !== 'lamination';
                    });

                    // get the runs related to this category
                    let runs = lamination[0].option.sheet_runs?.filter(run => run.machine.toString() === machine._id.toString())[0];


                    let run = runs?.runs.filter((run) =>
                        this.format.quantity >= parseInt(run.from) && this.format.quantity <= parseInt(run.to)
                    )

                    this.results.push({
                        type: "lamination",
                        results: {
                            machine: machine,
                            format: this.format,
                            lamination:lamination,
                            calculation: {
                                calculation_method: lamination[0].option.calculation_method,
                                area_sqm: area_sqm,
                                start_cost: start_cost,
                                option_start_cost: parseInt(lamination[0].option?.start_cost??0) /100000,
                                run: run,
                                run_price: Number(parseInt(run?run[0]?.price??0:0) / 100000)
                            },
                            color: this.color,
                            options: options,
                            duration: this.duration
                        }
                    })


                }
            }


            if(machine.type === "bundling") {
                if(options.length) {
                    // use the machine
                }
            }
        }

        return Object.assign(this.results, this.error);
    }
}
