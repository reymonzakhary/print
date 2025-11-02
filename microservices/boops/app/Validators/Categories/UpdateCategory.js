'use strict'


class UpdateCategory {
  get rules () {
    const categoryId = this.ctx.params.category
    return {
      name: `required`
    }
  }
}

module.exports = UpdateCategory
