'use strict'

class StoreDiscount {
  /**
   * Show all validation messages
   */
  get validateAll() {
    return true
  }

  /**
   * Handle error messages
   */
  async fails(errorMessages) {
    return this.ctx.response.send(errorMessages)
  }

  /**
   * Rules
   */
  
  get rules() {
    return {
      supplier_id: 'required|integer',
      reseller_id: "required|integer",
      discount: "required"
    };
  }
}

module.exports = StoreDiscount
