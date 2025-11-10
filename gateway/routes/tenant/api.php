<?php

use App\Blueprint\Blueprint;
use App\Http\Controllers\Tenant\Mgr\Account\AccountController;
use App\Http\Controllers\Tenant\Mgr\Account\Setting\SettingController;
use App\Http\Controllers\Tenant\Mgr\Apps\AppController;
use App\Http\Controllers\Tenant\Mgr\Auth\AuthenticationController;
use App\Http\Controllers\Tenant\Mgr\Auth\ImpersonateController;
use App\Http\Controllers\Tenant\Mgr\Auth\PasswordResetController;
use App\Http\Controllers\Tenant\Mgr\Auth\VerificationApiController;
use App\Http\Controllers\Tenant\Mgr\Cart\Media\MediaController;
use App\Http\Controllers\Tenant\Mgr\Companies\Addresses\AddressController as CompanyAddressController;
use App\Http\Controllers\Tenant\Mgr\Companies\CompanyController;
use App\Http\Controllers\Tenant\Mgr\Companies\Teams\TeamController as CompanyTeamController;
use App\Http\Controllers\Tenant\Mgr\Company\Addresses\AddressController as OwnerCompanyAddressController;
use App\Http\Controllers\Tenant\Mgr\Company\CompanyController as OwnerCompanyController;
use App\Http\Controllers\Tenant\Mgr\Contexts\Address\AddressController as ContextAddressController;
use App\Http\Controllers\Tenant\Mgr\Contexts\ContextController;
use App\Http\Controllers\Tenant\Mgr\Countries\CountryController;
use App\Http\Controllers\Tenant\Mgr\Currency\CurrencyController;
use App\Http\Controllers\Tenant\Mgr\Languages\LanguageController;
use App\Http\Controllers\Tenant\Mgr\Lexicons\LexiconController;

use App\Http\Controllers\Tenant\Mgr\Namespaces\NamespaceController;
use App\Http\Controllers\Tenant\Mgr\Settings\SettingController as SysSettingsController;
use App\Http\Controllers\Tenant\Mgr\Statuses\StatusController;
use App\Http\Controllers\Tenant\Mgr\Tags\TagController;
use App\Http\Controllers\Tenant\Mgr\Units\UnitController;
use App\Http\Controllers\Tenant\Mgr\Users\Address\AddressController;

use App\Http\Controllers\Tenant\Mgr\Users\Companies\Addresses\AddressController as UserCompanyAddressController;
use App\Http\Controllers\Tenant\Mgr\Users\Companies\CompanyController as UserCompanyController;
use App\Http\Controllers\Tenant\Mgr\Users\Profile\ProfileController;
use App\Http\Controllers\Tenant\Mgr\Users\UserController;
use App\Models\Tenant\Item;
use App\Models\Tenant\Order;
use Cart\CartController;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Mgr', 'prefix' => 'mgr', 'middleware' => ['dynamic.user','auth.tenant.gate']], function () {

    /** params [email, password]*/
    Route::post('login', [AuthenticationController::class, 'login'])
        ->name('login.access');
    Route::post('impersonate', [AuthenticationController::class, 'impersonate'])
        ->name('login.access');
    /** params [email]*/
    Route::post('password/forget', [PasswordResetController::class, 'forget'])
        ->name('login.access');
    /** params [email, token]*/
    Route::post('password/reset/verify', [PasswordResetController::class, 'verify'])->name('password.reset.verify');
    /** params [email, password, confirmed_password]*/
    Route::post('password/reset', [PasswordResetController::class, 'reset'])
        ->name('login.access');

    Route::get('/info', function () {

        return response()->json([
            'logo' => hostname()->logo_url ?? null,
            'fqdn' => hostname()->fqdn,
        ]);
    });

    Route::get('/apps', AppController::class);


    Route::group(['prefix' => '/currencies'], function (): void {
        Route::get('/', [CurrencyController::class, '__invoke'])->name('currencies-list');
    });

    /**
     * @todo need to be under a gust group and middleware
     */
    Route::get('email/verify/{id}', [VerificationApiController::class, 'verify'])->name('verificationapi.verify');
    Route::get('email/resend', [VerificationApiController::class, 'resend'])->name('verificationapi.resend');
    Route::get('quotations/{order}/notifications/acceptance', '\App\Http\Controllers\Tenant\Mgr\Quotations\Notifications\MailController@acceptance')->name('quotations-notifications-access');
    Route::get('quotations/{order}/notifications/show', '\App\Http\Controllers\Tenant\Mgr\Quotations\Notifications\MailController@view')->name('quotations-notifications-access');
    /**
     * access manager
     */
    Route::group(['middleware' => ['auth.ctx:mgr', 'auth:tenant', 'restrictAccess', 'verified']], function () {

        Route::post('generate-impersonate', [ImpersonateController::class, '__invoke'])
            ->name('*');
        Route::post('refresh', [AuthenticationController::class, 'refreshToken'])
            ->name('*');
        /** user account */
        Route::get('account/me', [AccountController::class, 'me'])
            ->name('*');
        Route::post('account/logout', [AccountController::class, 'logout'])
            ->name('*');

        require __DIR__ . '/dashboard.php';
        require __DIR__ . '/tenant_settings.php';


        /**
         * user settings
         */
        Route::get('account/settings', [SettingController::class, 'index'])
                ->name('*');
        Route::put('account/settings/{setting}', [SettingController::class, 'update'])
            ->name('*');

        Route::get('units', [UnitController::class, '__invoke'])->name('*');

        Route::group(['namespace' => 'Warehouses', 'middleware' => 'grant:warehouses'], function () {
            Route::apiResource('warehouses', 'WarehouseController', [
                'names' => [
                    'index' => 'warehouses-list',
                    'show' => 'warehouses-read',
                    'store' => 'warehouses-create',
                    'update' => 'warehouses-update',
                    'destroy' => 'warehouses-delete'
                ]
            ]);
            Route::group(['namespace' => 'Addresses'], function () {
                Route::apiResource('warehouses/{warehouse}/addresses', 'AddressController', [
                    'names' => [
                        'index' => 'warehouses-addresses-list',
                        'show' => 'warehouses-addresses-read',
                        'store' => 'warehouses-addresses-create',
                        'update' => 'warehouses-addresses-update',
                        'destroy' => 'warehouses-addresses-delete'
                    ]
                ]);
            });
            Route::group(['namespace' => 'Locations'], function () {
                Route::apiResource('warehouses/{warehouse}/locations', 'LocationController', [
                    'names' => [
                        'index' => 'warehouses-locations-list',
                        'show' => 'warehouses-locations-read',
                        'store' => 'warehouses-locations-create',
                        'update' => 'warehouses-locations-update',
                        'destroy' => 'warehouses-locations-delete'
                    ]
                ]);
            });
        });
        /////////////////////////////
        ///  ACL group
        ////////////////////////////
        require __DIR__ . '/acl.php';

        // roles and Permissions
        Route::group(['namespace' => 'Teams', 'middleware' => 'grant:teams'], function () {
            Route::resource('teams', 'TeamController', [
                'names' => [
                    'index' => 'teams-list',
                    'show' => 'teams-read',
                    'store' => 'teams-create',
                    'update' => 'teams-update',
                    'destroy' => 'teams-delete'
                ]
            ])->except(['create', 'edit']);

            Route::group(['prefix' => 'teams'], function () {
                Route::delete('/{team}/users/{user}', 'TeamController@userDetaching')->name('teams-users-delete');
            });

            Route::group(['prefix' => 'teams'], function () {
                Route::delete('/{team}/members', 'TeamController@memberDetaching')->name('teams-members-delete');
            });

            Route::group(['prefix' => 'teams', 'namespace' => 'Accessibility'], function () {
                Route::get('/{team}/accessibility', 'AccessibilityController@index')->name('teams-accessibility-list');
                Route::post('/{team}/accessibility', 'AccessibilityController@store')->name('teams-accessibility-create');
                Route::delete('/{team}/accessibility/users/{user}', 'AccessibilityController@userDetaching')->name('teams-accessibility-delete');
                Route::delete('/{team}/accessibility/categories', 'AccessibilityController@categoriesDetaching')->name('teams-accessibility-delete');
                Route::delete('/{team}/accessibility/product/{product}', 'AccessibilityController@productDetaching')->name('teams-accessibility-delete');
            });

            Route::group(['prefix' => 'teams', 'namespace' => 'Address'], function () {
                Route::get('/{team}/addresses', 'AddressController@index')->name('teams-addresses-list');
                Route::get('/{team}/addresses/{address}', 'AddressController@show')->name('teams-addresses-read');
                Route::post('/{team}/addresses', 'AddressController@store')->name('teams-addresses-create');
                Route::put('/{team}/addresses/{address}', 'AddressController@update')->name('teams-addresses-update');
                Route::delete('/{team}/addresses/{address}', 'AddressController@destroy')->name('teams-addresses-delete');
            });

            Route::post('teams/{team}/media-source', 'TeamController@syncMediaSource')->name('teams-update');
        });


        Route::group(['namespace' => 'Roles'], function () {
            Route::resource('roles', 'RoleController', [
                'names' => [
                    'index' => 'roles-list',
                    'show' => 'roles-read',
                    'store' => 'roles-create',
                    'update' => 'roles-update',
                    'destroy' => 'roles-delete'
                ]
            ])->except(['create', 'edit']);
            Route::put('roles/{role}/permissions', 'RoleController@updatePermissions')->name('roles-update');
            Route::group(['middleware' => 'grant:roles,permissions'], function () {
                Route::resource('roles.permissions', 'PermissionsController', [
                    'names' => [
                        'index' => 'roles-permissions-list',
                        'show' => 'roles-permissions-read',
                        'store' => 'roles-permissions-create',
                        'update' => 'roles-permissions-update',
                        'destroy' => 'roles-permissions-delete'
                    ]
                ])->except(['create', 'edit']);
                Route::get('permissions', 'PermissionsController@listAll')->name('roles-permissions-list');
            });
        });
        // roles and Permissions


        /////////////////////////////
        ///  Media source
        /////////////////////////////
        require __DIR__ . '/media_source.php';

        /////////////////////////////
        ///  machines
        /////////////////////////////
        require __DIR__ . '/machines.php';

        /////////////////////////////
        /**
         * Tenant system settings
         */
        Route::get('settings', [SysSettingsController::class, 'index'])
            ->name('*');
        Route::group(['middleware' => 'grant:settings'], function () {

            Route::put('settings/{setting}', [SysSettingsController::class, 'update'])
                ->name('settings-update');
        });

        /**
         * namespaces
         * @TODO check
         */
        Route::group(['middleware' => 'grant:namespaces'], function () {
            Route::get('settings/namespaces', [NamespaceController::class, 'index'])
                ->name('namespaces-list');
            Route::post('settings/namespaces', [NamespaceController::class, 'store'])
                ->name('namespaces-create');
        });


        Route::group(['middleware' => 'grant:tags'], function () {
            Route::get('/tags', [TagController::class, 'index'])
                ->name('tags-list');
            Route::get('/tags/{tag}', [TagController::class, 'show'])
                ->name('tags-read');
            Route::post('/tags', [TagController::class, 'store'])
                ->name('tags-create');
            Route::put('/tags/{tag}', [TagController::class, 'update'])
                ->name('tags-update');
            Route::delete('/tags/{tag}', [TagController::class, 'destroy'])
                ->name('tags-delete');
        });

        /**
         * members group
         *
         */
        require __DIR__ . '/member.php';


        /**
         * handling user
         */
        Route::group(['middleware' => 'grant:users'], function () {
            Route::get('/users', [UserController::class, 'index'])
                ->name('users-list');
            Route::get('/users/{user}', [UserController::class, 'show'])
                ->name('users-read');
            Route::post('/users', [UserController::class, 'store'])
                ->name('users-create');
            Route::put('/users/{user}', [UserController::class, 'update'])
                ->name('users-update');
            Route::get('/users/{user}/verification', [UserController::class, 'verification'])
                ->name('users-update');
            Route::delete('/users/{user}', [UserController::class, 'destroy'])
                ->name('users-delete');
            /**
             * user profile
             */
            Route::group(['middleware' => 'grant:users,profiles'], function () {
                Route::get('/users/{user}/profile', [ProfileController::class, 'show'])
                    ->name('users-profiles-read');
                Route::put('/users/{user}/profile', [ProfileController::class, 'update'])
                    ->name('users-profiles-update');
            });
            /**
             * addresses
             */

            Route::group(['middleware' => 'grant:users,addresses'], function () {
                Route::get('/users/{user}/addresses', [AddressController::class, 'index'])->name('users-addresses-list');
                Route::get('/users/{user}/addresses/{address}', [AddressController::class, 'show'])->name('users-addresses-read');
                Route::post('/users/{user}/addresses', [AddressController::class, 'store'])->name('users-addresses-create');
                Route::put('/users/{user}/addresses/{address}', [AddressController::class, 'update'])->name('users-addresses-update');
                Route::delete('/users/{user}/addresses/{address}', [AddressController::class, 'destroy'])->name('users-addresses-delete');
            });

            /**
             * companies
             */
            Route::group(['middleware' => 'grant:companies', "prefix" => "company"], function () {
                Route::get('/', [OwnerCompanyController::class, 'index'])->name('companies-list');
                Route::put('/', [OwnerCompanyController::class, 'update'])->name('companies-update');
                // Route::put('/config', [ConfigController::class, 'update'])->name('company-config-update');
                // Route::get('/config', [ConfigController::class, 'index'])->name('company-config-list');
                /**
                 * company address
                 */
                Route::group(['middleware' => 'grant:companies'], function () {
                    Route::get('/addresses', [OwnerCompanyAddressController::class, 'index'])->name('companies-addresses-list');
                    Route::post('/addresses', [OwnerCompanyAddressController::class, 'store'])->name('companies-addresses-create');
                    Route::put('/addresses/{address}', [OwnerCompanyAddressController::class, 'update'])->name('companies-addresses-update');
                    Route::delete('/addresses/{address}', [OwnerCompanyAddressController::class, 'destroy'])->name('companies-addresses-delete');
                });
            });

            /**
             * companies
             */
            Route::group(['middleware' => 'grant:companies', "prefix" => "companies"], function () {
                Route::get('/', [CompanyController::class, 'index'])->name('companies-list');
                Route::get('/{company}', [CompanyController::class, 'show'])->name('companies-read');
                Route::post('/', [CompanyController::class, 'store'])->name('companies-create');
                Route::put('/{company}', [CompanyController::class, 'update'])->name('companies-update');
                // Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('companies-delete');

                /**
                 * company address
                 */
                Route::group(['middleware' => 'grant:companies'], function () {
                    Route::get('/{company}/addresses', [CompanyAddressController::class, 'index'])
                        ->name('companies-addresses-list');
                    Route::get('/{company}/addresses/{address}', [CompanyAddressController::class, 'show'])
                        ->name('companies-addresses-read');
                    Route::post('/{company}/addresses', [CompanyAddressController::class, 'store'])
                        ->name('companies-addresses-create');
                    Route::put('/{company}/addresses', [CompanyAddressController::class, 'update'])
                        ->name('companies-addresses-update');
                    Route::delete('/{company}/addresses', [CompanyAddressController::class, 'destroy'])
                        ->name('companies-addresses-delete');
                });

                /**
                 * Company Teams
                 */
                Route::group(['middleware' => 'grant:companies,teams'], function () {
                    Route::get('/{company}/teams', [CompanyTeamController::class, 'index'])->name('companies-teams-list');
                    Route::get('/{company}/teams/{team}', [CompanyTeamController::class, 'show'])->name('companies-teams-read');
                    Route::post('/{company}/teams', [CompanyTeamController::class, 'store'])->name('companies-teams-create');
                    Route::put('/{company}/teams/{team}', [CompanyTeamController::class, 'update'])->name('companies-teams-update');
                    Route::delete('/{company}/teams/{team}', [CompanyTeamController::class, 'destroy'])->name('companies-teams-delete');
                });
            });

            Route::group(['middleware' => 'grant:users,companies'], function () {
                Route::get('/users/{user}/companies', [UserCompanyController::class, 'index'])->name('users-companies-list');
                Route::get('/users/{user}/companies/{company}', [UserCompanyController::class, 'show'])->name('users-companies-read');
                Route::post('/users/{user}/companies', [UserCompanyController::class, 'index'])->name('users-companies-create');
                Route::put('/users/{user}/companies/{company}', [UserCompanyController::class, 'index'])->name('users-companies-update');
                Route::delete('/users/{user}/companies/{company}', [UserCompanyController::class, 'index'])->name('users-companies-delete');

                /**
                 * company address
                 */
                Route::group(['middleware' => 'grant:users,companies_addresses'], function () {
                    Route::get('/users/{user}/companies/{company}/addresses', [UserCompanyAddressController::class, 'index'])
                        ->name('users-companies-addresses-list');
                    Route::get('/users/{user}/companies/{company}/addresses/{address}', [UserCompanyAddressController::class, 'show'])
                        ->name('users-companies-addresses-read');
                    Route::post('/users/{user}/companies/{company}/addresses', [UserCompanyAddressController::class, 'store'])
                        ->name('users-companies-addresses-create');
                    Route::put('/users/{user}/companies/{company}/addresses', [UserCompanyAddressController::class, 'update'])
                        ->name('users-companies-addresses-update');
                    Route::delete('/users/{user}/companies/{company}/addresses', [UserCompanyAddressController::class, 'destroy'])
                        ->name('users-companies-addresses-delete');
                });
            });
        });


        /**
         * statuses group
         * handel all the event for get all en single status
         */
        Route::apiResource('statuses', StatusController::class, [
            'names' => [
                'index' => 'status-list',
                'show' => 'status-read',
                'store' => 'status-create',
                'update' => 'status-update',
                'destroy' => 'status-delete',
            ]
        ]);

        Route::apiResource('lexicons', LexiconController::class, [
            'names' => [
                'index' => 'lexicons-list',
                'show' => 'lexicons-read',
                'update' => 'lexicons-update',
            ]
        ])->only(['index', 'show', 'update']);;

        /**
         * countries group
         */
        Route::get('countries', [CountryController::class, 'index'])->name('*');
        Route::get('countries/{country}/vats', [\App\Http\Controllers\Tenant\Mgr\Countries\CountryVatController::class, 'index'])->name('*');
        Route::post('countries/{country}/vats', [\App\Http\Controllers\Tenant\Mgr\Countries\CountryVatController::class, 'store'])->name('*');
        Route::post('countries/{country}/addresses/search', [CountryController::class, 'search'])->name('*');
        Route::post('countries/{country}/addresses', [CountryController::class, 'store'])->name('*');


        /**
         * get languages
         */

        Route::get('languages', [LanguageController::class, '__invoke'])->name('*');


        /**
         * quotations group
         */
        require __DIR__ . '/quotations.php';
        Route::get('/blue/{order}/{item}', function (Order $order, Item $item) {
            return (new Blueprint)($order, $item);
        })->name("*");
        /**
         * countries group
         */

        require __DIR__ . '/orders.php';

        require __DIR__ . '/invoice.php';

        require __DIR__ . '/design_provider.php';

        /**
         * Delivery Days
         */
        require __DIR__ . '/delivery.php';

        /**
         * Blueprints group
         */
        require __DIR__ . '/blueprint.php';

        /**
         * Transactions routes
         */
        require __DIR__ . '/transactions.php';

        /**
         * countries group
         */
        Route::group(['middleware' => 'grant:contexts'], function () {
            Route::get('/contexts', [ContextController::class, 'index'])->name('contexts-list');
            Route::post('/contexts', [ContextController::class, 'store'])->name('contexts-create');
            Route::get('/contexts/{context}/users', [ContextController::class, 'showUsers'])->name('contexts-list');

            /**
             * addresses
             */
            Route::group(['middleware' => 'grant:contexts,addresses'], function () {
                Route::get('/contexts/{context}/addresses', [ContextAddressController::class, 'index'])
                    ->name('contexts-addresses-list');
                Route::post('/contexts/{context}/addresses', [ContextAddressController::class, 'store'])
                    ->name('contexts-addresses-create');
                Route::put('/contexts/{context}/addresses/{address}', [ContextAddressController::class, 'update'])
                    ->name('contexts-addresses-update');
                Route::delete('/contexts/{context}/addresses/{address}', [ContextAddressController::class, 'destroy'])
                    ->name('contexts-addresses-delete');
            });
        });


        ////
        Route::group(['middleware' => 'grant:finder', 'namespace' => 'Finder', 'prefix' => 'finder'], function () {
            Route::group(['middleware' => 'grant:finder,categories', 'namespace' => 'Categories'], function () {
                Route::get('/categories/search', 'CategoryController@search')->name('finder-categories-list');
                Route::resource('/categories', 'CategoryController', [
                    'names' => [
                        'index' => 'finder-categories-list',
                        'show' => 'finder-categories-read',
                        'store' => 'finder-categories-create',
                        'update' => 'finder-categories-update',
                        'destroy' => 'finder-categories-delete'
                    ]
                ])->except([
                    'create', 'edit'
                ]);
                Route::group(['prefix' => 'categories/{category}/boxes/{box}', 'namespace' => 'Options'], function () {
                    Route::get('options', 'OptionController@index')->name('finder-categories-list');
                });
                Route::group(['namespace' => "Products"], function () {
                    Route::post('categories/{category}/products', 'ProductController@shop')->name('finder-categories-list');
                });
            });

            Route::group(['middleware' => 'grant:finder,boxes', 'namespace' => 'Boxes'], function () {
                Route::get('boxes/search', 'BoxController@search')->name('finder-boxes-list');
            });

            Route::group(['middleware' => 'grant:finder,options', 'namespace' => 'Options'], function () {
                Route::get('options/search', 'OptionController@search')->name('finder-options-list');
            });
        });

        Route::group(['middleware' => 'grant:finder', 'namespace' => 'Marketplace', 'prefix' => 'marketplace'], function () {
            Route::get('/categories', 'MarketplaceController@categories')->name('finder-categories-list');
            Route::get('/options', 'MarketplaceController@options')->name('finder-categories-list');
        });



        /**
         * get suppliers
         * * discount
         * * * categories
         * * * * products
         */
        Route::group(['middleware' => 'grant:suppliers', 'prefix' => 'suppliers', 'namespace' => 'Suppliers'], function () {
            Route::get('/', 'SupplierController@index')->name('suppliers-access');
            Route::get('/{supplier}', 'SupplierController@show')->name('suppliers-read');
            Route::get('/{website:id}/categories', 'SupplierController@categories')->name('*');
            //create categories
            Route::get('/{website:id}/categories/{category}', 'SupplierController@category')->name('*');
            Route::post('/{website:id}/categories/{category}/link', 'SupplierController@link')->name('*');
            /**
             * check this one again
             */
            Route::group(['middleware' => 'grant:suppliers,discounts', 'prefix' => '/{supplier}/discounts', 'namespace' => 'Discounts'], function () {
                Route::get('/', 'DiscountController@index')->name('suppliers-discounts-create');
//                Route::get('/general','DiscountController@general')->name('create-orders');
//                Route::get('/categories','DiscountController@categories')->name('create-orders');
//                Route::get('/categories/{category}','DiscountController@category')->name('create-orders');
            });
        });


        require_once __DIR__ . '/catalogues.php';

        require_once __DIR__ . '/print_assortment.php';

        Route::group(['prefix' => 'custom'], function () {
            require_once __DIR__ . '/custom_assortment.php';
        });

        /**
         * Shop
         */
        require_once __DIR__ . '/shops.php';

        /**
         * own margins
         */
        Route::group(['prefix' => 'margins', 'namespace' => 'Margins', 'middleware' => 'grant:margins'], function () {
            Route::get('/', 'MarginController@index')->name('margins-list');
            Route::put('/', 'MarginController@update')->name('margins-update');
        });

        /**
         * own Discount
         */
        Route::group(['namespace' => 'Discounts', 'middleware' => 'grant:discounts'], function () {
            Route::resource('discounts', 'DiscountController', [
                'names' => [
                    'index' => 'discounts-list',
                    'show' => 'discounts-read',
                    'store' => 'discounts-create',
                    'update' => 'discounts-update',
                    'destroy' => 'discounts-delete'
                ]
            ])->except([
                'create', 'edit'
            ]);
        });

        /**
         * Services
         */
        Route::group(['namespace' => 'Services', 'middleware' => 'grant:services'], function () {
            Route::resource('services', 'ServiceController', [
                'names' => [
                    'index' => 'services-list',
                    'show' => 'services-read',
                    'store' => 'services-create',
                    'update' => 'services-update',
                    'destroy' => 'services-delete'
                ]
            ]);
        });

        /**
         * Stocks
         */
        Route::group(['namespace' => 'Stocks', 'middleware' => 'grant:stocks'], function () {
            Route::resource('stocks', 'StockController', [
                'names' => [
                    'index' => 'stocks-list',
                    'show' => 'stocks-read',
                    'store' => 'stocks-create',
                    'update' => 'stocks-update',
                    'destroy' => 'stocks-delete'
                ]
            ]);
        });

        Route::group(['namespace' => 'Cart', 'middleware' => 'grant:cart'], function () {
            Route::post('cart/checkout', 'CheckoutController')->name('orders-create');
            Route::apiResource('cart', \CartController::class, [
                'parameters' => [
                    'cart' => 'product'
                ],
                'names' => [
                    'index' => 'cart-list',
                    'show' => 'cart-read',
                    'store' => 'cart-create',
                    'update' => 'cart-update',
                    'destroy' => 'cart-delete'
                ]

            ]);
            Route::group(['middleware' => 'grant:cart-items,media'], function () {
                Route::post('cart/items/{item}/media', [MediaController::class, 'store'])->name('cart-items-media-create');
                Route::delete('cart/items/{item}/media/{media}', [MediaController::class, 'delete'])->name('cart-items-media-delete');
            });
        });
    });

});

Route::group(['namespace' => 'Web'], function () {

    Route::post('login', [App\Http\Controllers\Tenant\Web\Auth\AuthenticationController::class, 'login'])
        ->name('login.access');

    Route::group(['middleware' => ['auth.ctx:web', 'auth:tenant']], function () {
        Route::resource('cart', CartController::class, [
            'parameters' => [
                'cart' => 'product'
            ]
        ]);
    });
});

/**
 * @Route("/")
 */
//Route::fallback(function () {
//    return response()->json([
//        'message'   => __('We could\'nt follow your request.'),
//        'status'    => Response::HTTP_NOT_FOUND
//    ], 200);
//});
