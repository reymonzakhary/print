const FetchProduct = require("../SemiCalculations/Calculations/FetchProduct");

module.exports = class ShopSemiCalculationController {

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
      const { product, contract } = req.body;

      const response = await (new FetchProduct(slug, supplier_id, product, req.body, contract, false).getRunning());

      return res.send(response)
    } catch (e) {
      console.log(e)
      return res.status(200).json({
        "message" : e.message,
        "status" : 422
      })
    }

  }

}
