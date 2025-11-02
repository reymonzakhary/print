const CalculateDiscount = require('../SemiCalculations/CalculateDiscount')
const CalculateMargin = require('../SemiCalculations/CalculateMargin')
const Calculation = require('../SemiCalculations/Calculation')
const FetchCategory = require('../Calculations/FetchCategory')
const FetchItems = require('../Calculations/FetchItems')
const crypto = require("crypto");
const FetchProduct = require("../SemiCalculations/Calculations/FetchProduct");
const {isNumber} = require("../Helpers/Helper");

module.exports = class SemiCalculationController {
    /**
     *
     * @param req
     * @param res
     * @returns {Promise<*>}
     */
    static async calculate(req, res) {
        let {supplier_id, slug} = req.params
        const {product, quantity, vat, dlv, vat_override} = req.body

        let supplierCategory = await ((new FetchCategory(slug, supplier_id)).getCategory())

        if (supplierCategory.error) {
            res.status(200).send(supplierCategory.error)
            return res.end()
        }

        res.send(await (SemiCalculationController.returnResponse(
                dlv, req,
                supplierCategory.category,
                SemiCalculationController.price_format(
                    await CalculateMargin.calculate(
                        await CalculateDiscount.calculate(
                            await ((new Calculation(supplier_id, supplierCategory.category, product, quantity)).calculate()),
                            supplierCategory.category
                        ),
                        supplierCategory.category),
                    supplierCategory.category,
                    vat,
                    vat_override
                )
            )
        ))
    }


  /**
   * This method performs a semi-calculation based on the provided request parameters
   * and body content, fetching product data and returning a response.
   *
   * @param req - The request object containing parameters and body data.
   * @param res - The response object used to send the response.
   * @return {Object} The response object containing the fetched product data or an error message.
   */
  static async newSemiCalculate(req, res) {

    try {
      const { supplier_id, slug } = req.params;
      const { contract,product } = req.body;

      const response = await (new FetchProduct(slug, supplier_id, product, req.body, contract, true).getRunning());

      return res.send(response)
    } catch (e) {
      console.log(e)
      return res.status(200).json({
        "message" : e.message,
        "status" : 422
      })
    }

  }

  static async calculateNetPrice(req, res) {
    let {supplier_id, slug} = req.params
    const {product, quantity} = req.body

        let supplierCategory = await (new FetchCategory(supplier_id, slug)).get()

        if (supplierCategory.error) {
            res.status(200).send(supplierCategory.error)
            return
        }

        let netPrice = await (new Calculation(supplier_id, supplierCategory.category, product, quantity)).calculate()

        res.send(netPrice)
    }


    static async returnResponse(dlv,req, category, data) {
        let {items, error} = await ((new FetchItems(req.params.supplier_id, req.body.product)).getItems());
        let prices = isNumber(dlv) ? {prices: data.filter(p => p.dlv.days === dlv)} : {prices: data}
        return {
            "type": "print",
            "connection": req.params.supplier_id,
            "external": "",
            "external_id": req.params.supplier_id,
            "external_name": category.tenant_name,
            "calculation_type": "semi_calculation",
            "items": items,
            "product": req.body.product,
            "category": category,
            "margins": [],
            "divided": false,
            "quantity": req.body.quantity,
            "calculation": [],
            ...prices
        }
    }

    static price_format(data, category, vat, vat_override = false, internal = false, ) {
        let clearResult = [];
        vat = vat_override?vat:category.vat;
        for (let item in data) {
            for (let days in data[item]) {
                for (let num in data[item][days]) {
                    let newFormat = data[item][days][num]["prices"];
                    newFormat["addons_start_cost"] = (newFormat["addons_start_cost"]) ? newFormat["addons_start_cost"] : 0;
                    newFormat["addons_subtotal"] = (newFormat["addons_subtotal"]) ? newFormat["addons_subtotal"] : 0;
                    newFormat["addons_total"] = (newFormat["addons_total"]) ? newFormat["addons_total"] : 0;

                    newFormat["pm"] = data[item][days][num]["pm"];
                    newFormat["dlv"] = data[item][days][num]["dlv"];
                    newFormat["start_cost"] = newFormat["start_cost"] + newFormat["addons_start_cost"];

                    // Assuming qty is present in the original data structure
                    const quantity = newFormat.qty || 1; // Default to 1 if qty is not found
                    const gross_price = newFormat.total + newFormat.addons_total; // Calculate gross price

                    const margins = newFormat.margins || {};
                    const vat_value = vat || 0; // Make sure to get vat_value from category
                    const tenant_id = category.tenant_id;
                    const p = internal ? gross_price : (((100 + parseInt(margins.value ?? 0)) * gross_price) / 100).toFixed(2)
                    const ppp = p / quantity
                    const p_inc = (vat_value + 100) * p / 100;
                    const dlv = {days: parseInt(newFormat.dlv), title: '',};

                    let id = `${process.env.APP_KEY}_${p}_${dlv.days}_${quantity}_${tenant_id}_${ppp}_${process.env.APP_KEY}`

                    // Create the object in the desired format
                    const formattedObject = {
                        id: crypto.createHash(process.env.HASH_TYPE).update(id).digest('hex'),
                        pm: newFormat.pm,
                        qty: quantity,
                        dlv: dlv,
                        gross_price: p,
                        gross_ppp: (p / quantity).toFixed(2),
                        p: p,
                        ppp: ppp,
                        selling_price_ex: p,
                        selling_price_inc: (p_inc).toFixed(2),
                        profit: internal
                            ? ((parseInt(margins.value ?? 0) * gross_price) / 100).toFixed(2)
                            : null,
                        discount: [], // Assuming no discounts in this context
                        margins: internal ? margins : [],
                        vat: vat_value,
                        vat_p: (gross_price * vat_value / 100).toFixed(2),
                        vat_ppp: ((gross_price * vat_value / 100) / quantity).toFixed(2)
                    };

                    clearResult.push(formattedObject);
                }
            }
        }

        return clearResult;
    }

}
