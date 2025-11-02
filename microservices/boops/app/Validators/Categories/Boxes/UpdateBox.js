'use strict'


class UpdateBox {
  get rules () {
    const boxId = this.ctx.params.box
    return {
      name: `required`
    }
  }
}

module.exports = UpdateBox
