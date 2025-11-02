'use strict'
/** @type {typeof import('@adonisjs/lucid/src/Lucid/Model')} */
const Model = use('Model')

class CategoryBoxes extends Model {

      /**
   * disable timestamp
   */
  static boot () {
    super.boot()
    this.addTrait('NoTimestamp')
  }
  
}

module.exports = CategoryBoxes
