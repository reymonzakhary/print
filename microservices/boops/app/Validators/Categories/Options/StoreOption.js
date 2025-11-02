'use strict'

class StoreOption {
  get rules () {
    return {
      name: 'required|unique:options'
    }
  }
}

module.exports = StoreOption
