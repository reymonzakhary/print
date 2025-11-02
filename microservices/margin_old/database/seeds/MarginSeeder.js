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
const Margin = use('App/Models/Margin')

class MarginSeeder {
  async run() {

    /* margin collection */
    let marginArray = {
      reseller_id: '19d11d85b667048fe8abf51d0be4a8af5',
      margin: {
        general: [
            {
                mode: 'run',
                status: '1',
                slots: [
                    {
                        from: '0',
                        to: '100',
                        type: 'percentage',
                        value: '10'
                    },
                    {
                        from: '101',
                        to: '200',
                        type: 'percentage',
                        value: '50'
                    }
                ]
            },
            {
                mode: 'price',
                status: '0',
                slots: [
                    {
                        from: '1000',
                        to: '2000',
                        type: 'fixed',
                        value: '500',
                        currency: 'EUR'
                    }
                ]
            }
        ],
        categories: {
            '60c9f524183e9297a381649b': [
                {
                    mode: 'run',
                    status: '1',
                    slots: [
                        {
                            from: '0',
                            to: '100',
                            type: 'percentage',
                            value: '10'
                        },
                        {
                            from: '101',
                            to: '200',
                            type: 'percentage',
                            value: '50'
                        }
                    ]
                },
                {
                    mode: 'price',
                    status: '0',
                    slots: [
                        {
                            from: '1000',
                            to: '2000',
                            type: 'fixed',
                            value: '500',
                            currency: 'EUR'
                        }
                    ]
                }
            ],
            '60c9f525183e9297a38164a6': [
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
    await Margin.create(marginArray)
  }
}

module.exports = MarginSeeder
