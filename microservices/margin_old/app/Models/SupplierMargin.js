'use strict'

/** @type {typeof import('@adonisjs/lucid/src/Lucid/Model')} */
const Model = use('Model')

class SupplierMargin extends Model {

    /**
     * remove created at
     */
    static get createdAtColumn() {
        return null
    }

    /**
     * remove updated at
     */
    static get updatedAtColumn() {
        return null
    }

    static boot() {
        super.boot()

        this.addTrait("@provider:Lucid/UpdateOrCreate")
      }
}

module.exports = SupplierMargin
