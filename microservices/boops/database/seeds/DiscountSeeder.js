'use strict'

const Discount = use('App/Models/Discount')

/*
|--------------------------------------------------------------------------
| DiscountSeeder
|--------------------------------------------------------------------------
|
| Make use of the Factory instance to seed database with dummy data or
| make use of Lucid models directly.
|
*/

const Factory = use('Factory')

class DiscountSeeder {
  async run () {
    /* discount collection */
    let discountArray = {
      supplier_id: '2aa21820-a0db-47da-ab97-828f60cd985b',
      reseller_id: 'c50d465b-5e2a-4c4a-9aff-68840ae8a11e',
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
          '5f21309234f288002e514caa': [
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
          ]
        }
      }
    }

    /* insert collection to DB */
    await Discount.create(discountArray)
  }
}

module.exports = DiscountSeeder
