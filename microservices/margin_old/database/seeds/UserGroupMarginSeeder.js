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
const UserGroupMargin = use('App/Models/UserGroupMargin')

class UserGroupMarginSeeder {
  async run () {

    /* margin collection */
    let array = {
        "reseller_id": "1",
        "user_group_id": "3",
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
              '5e41cfadd7ba83015777b62c': [
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
              '5e41cfadd7ba82323423423462c': [
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
   await UserGroupMargin.create(array)
  }
}

module.exports = UserGroupMarginSeeder
