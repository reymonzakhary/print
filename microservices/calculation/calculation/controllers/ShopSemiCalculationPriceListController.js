const FetchProduct = require("../SemiCalculations/Calculations/FetchProduct");
const FetchCategory = require("../Calculations/FetchCategory");
const {rangeListFromCategory} = require("../Helpers/Helper");


module.exports = class ShopSemiCalculationPriceListController {

  /**
   * This method performs a semi-calculation based on the provided request parameters
   * and body content, fetching product data and returning a response.
   *
   * @param req - The request object containing parameters and body data.
   * @param res - The response object used to send the response.
   * @return {Object} The response object containing the fetched product data or an error message.
   */
  static async calculate(req, res) {

    try {
      const { supplier_id, slug } = req.params;
      const {category} = await (new FetchCategory(slug, supplier_id).getCategory());
      const { product } = req.body;
      let response = [];
      
      let list = rangeListFromCategory(category).reduce((acc, item) => {
          acc.push(...item.range_list)
          return acc;
      }, []);

      for (let q of list) {
          req.body.quantity = q

          /** Fetch the supplier category from mongoose */
          response.push(await (get(slug, supplier_id, product, req)));
      }
      const results =  response.filter(x => Object.keys(x).length);
      const price_list = results.map((item) => item.prices).flat();
      response = results.slice(0,1);
      if(response.length > 0) {
          response[0].prices =  price_list
      }

      return res.send(...response)
    } catch (e) {
      console.log(e)
      return res.status(200).json({
        "message" : e.message,
        "status" : 422
      })
    }

  }

}


/**
 * Asynchronously fetches product information using the provided parameters.
 *
 * @param {string} slug - The slug of the product.
 * @param {string} supplier_id - The ID of the product's supplier.
 * @param {string} product - The product information to fetch.
 * @param {object} req - The request object containing additional data.
 * @returns {Promise<Array>} A promise that resolves with the fetched product information or an empty array if an error occurs.
 */
const get = async (slug, supplier_id, product, req) =>  {

    try {
        return await (new FetchProduct(slug, supplier_id, product, req.body, false).getRunning())
    } catch (e) {
        return [];
    }
}
