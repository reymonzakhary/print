'use strict'

class UpdateMargin {
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
      margin: "required"
    };
  }
}

module.exports = UpdateMargin
