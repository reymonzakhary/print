'use strict'
const Margin = use('App/Models/Margin')
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
const Factory = use('Factory')

class MarginSeeder {
  async run () {

    /* margin collection */
    let marginArray = {
      reseller_id: '2aa21820-a0db-47da-ab97-828f60cd985b',
      margin: {
        general: [
          {
            mode: 'run',
            status: true,
            slots: [
              {
                from: 0,
                to: 100,
                type: 'percentage',
                value: 10
              },
              {
                from: 101,
                to: 200,
                type: 'percentage',
                value: 50
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
                value: 500,
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
                  value: 10
                },
                {
                  from: 101,
                  to: 200,
                  type: 'percentage',
                  value: 50
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
                  value: 500,
                  currency: 'EUR'
                }
              ]
            }
          ]
        }
      }
    }

    /* insert collection to DB */
    await Margin.create(marginArray)


  }
}

module.exports = MarginSeeder
