'use strict'

/*
|--------------------------------------------------------------------------
| SupplierMarginSeeder
|--------------------------------------------------------------------------
|
| Make use of the Factory instance to seed database with dummy data or
| make use of Lucid models directly.
|
*/

/** @type {import('@adonisjs/lucid/src/Factory')} */
const SupplierMargin = use('App/Models/SupplierMargin')

class SupplierMarginSeeder {
  async run() {

    /* margin collection */
    let marginArray = {
      supplier_id: '2373d94c-0169-4e2b-8a33-35279ed9413c',
      tenant_id: '73eb50da-cff1-4d2e-ab8e-5f8f26f1db5c',
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
            'envelopes': [
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
    await SupplierMargin.create(marginArray)
  }
}

module.exports = SupplierMarginSeeder
