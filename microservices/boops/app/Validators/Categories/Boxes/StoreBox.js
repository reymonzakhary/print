'use strict'

class StoreBox {
  get rules () {
    return {
      name: 'required|unique:boxes'
    }
  }
}

module.exports = StoreBox
