'use strict'

class StoreMargin {
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
      reseller_id: "required|integer",
      margin: "required"
    };
  }
}

module.exports = StoreMargin
