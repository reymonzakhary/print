'use strict'

class StoreCategory {
  get rules () {
    return {
      name: 'required|unique:categories'
    }
  }
}

module.exports = StoreCategory
