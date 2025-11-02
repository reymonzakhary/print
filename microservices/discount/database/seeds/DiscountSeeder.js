'use strict'

/*
|--------------------------------------------------------------------------
| MarginSeeder
|--------------------------------------------------------------------------
|
| Make use of the Factory instance to seed database with dummy data or
| make use of Lucid models directly.
|
*/

/** @type {import('@adonisjs/lucid/src/Factory')} */
const SupplierDiscount = use('App/Models/SupplierDiscount')

class DiscountSeeder {
  async run() {

    /* discount collection */
    let discountArray = {
        supplier_id: '2b526015-5f35-4430-b2d3-c08eb1f6f1c7',
        tenant_id: 'c7d036720ec642008e8dc3d6249175ee',
        discount: {
          general: [
              {
                  mode: 'run',
                  status: true,
                  slots: [
                      {
                          from: 0,
                          to: 100,
                          type: 'percentage',
                          value: 20
                      }
                  ]
              },
              {
                  mode: 'price',
                  status: false,
                  slots: [
                      {
                          from: 201,
                          to: 300,
                          type: 'fixed',
                          value: 100,
                          currency: 'EUR'
                      }
                  ]
              }
          ],
          categories: {
              'envelopes': [
                  {
                      mode: 'run',
                      status: true,
                      slots: [
                          {
                              from: 0,
                              to: 100,
                              type: 'percentage',
                              value: 20
                          },
                          {
                              from: 101,
                              to: 200,
                              type: 'percentage',
                              value: 15
                          }
                      ]
                  },
                  {
                      mode: 'price',
                      status: false,
                      slots: [
                          {
                              from: 201,
                              to: 300,
                              type: 'fixed',
                              value: 100,
                              currency: 'EUR'
                          }
                      ]
                  }
              ],
              'letterhead': [
                  {
                      mode: 'run',
                      status: true,
                      slots: [
                          {
                              from: 0,
                              to: 100,
                              type: 'percentage',
                              value: 20
                          },
                          {
                              from: 101,
                              to: 200,
                              type: 'percentage',
                              value: 15
                          }
                      ]
                  },
                  {
                      mode: 'price',
                      status: false,
                      slots: [
                          {
                              from: 1000,
                              to: 2000,
                              type: 'fixed',
                              value: 100,
                              currency: 'EUR'
                          }
                      ]
                  }
              ]
          }
      }
    }

    /* insert collection to DB */
    await SupplierDiscount.create(discountArray)

  }
}

module.exports = DiscountSeeder
