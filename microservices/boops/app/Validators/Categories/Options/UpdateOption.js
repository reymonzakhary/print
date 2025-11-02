'use strict'


class UpdateOption {
  get rules () {
    const optionId = this.ctx.params.box
    return {
      name: `required`
    }
  }
}

module.exports = UpdateOption
