<?php

declare(strict_types=1);

use App\Http\Controllers\System\V2\Mgr\Account\AccountController;
use App\Http\Controllers\System\V2\Mgr\App\AppController;
use App\Http\Controllers\System\V2\Mgr\Auth\AuthenticationController;
use App\Http\Controllers\System\V2\Mgr\Auth\PasswordResetController;
use App\Http\Controllers\System\V2\Mgr\Boxes\BoxController;
use App\Http\Controllers\System\V2\Mgr\Boxes\MatchedBoxController;
use App\Http\Controllers\System\V2\Mgr\Boxes\MergeBoxController;
use App\Http\Controllers\System\V2\Mgr\Boxes\UnlinkedBoxController;
use App\Http\Controllers\System\V2\Mgr\Boxes\UnmatchedBoxController;
use App\Http\Controllers\System\V2\Mgr\Categories\ManifestController;
use App\Http\Controllers\System\V2\Mgr\Categories\MatchedCategoryController;
use App\Http\Controllers\System\V2\Mgr\Categories\MergeCategoryController;
use App\Http\Controllers\System\V2\Mgr\Categories\UnlinkedCategoryController;
use App\Http\Controllers\System\V2\Mgr\Categories\UnmatchedCategoryController;
use App\Http\Controllers\System\V2\Mgr\Clients\MediaController;
use App\Http\Controllers\System\V2\Mgr\Companies\CompanyController;
use App\Http\Controllers\System\V2\Mgr\Companies\ContractController;
use App\Http\Controllers\System\V2\Mgr\Companies\QuotationController;
use App\Http\Controllers\System\V2\Mgr\Country\CountryController;
use App\Http\Controllers\System\V2\Mgr\Currency\CurrencyController;
use App\Http\Controllers\System\V2\Mgr\Messages\MessageController;
use App\Http\Controllers\System\V2\Mgr\Options\MatchedOptionController;
use App\Http\Controllers\System\V2\Mgr\Options\MergeOptionController;
use App\Http\Controllers\System\V2\Mgr\Options\OptionController;
use App\Http\Controllers\System\V2\Mgr\Options\UnlinkedOptionController;
use App\Http\Controllers\System\V2\Mgr\Options\UnmatchedOptionController;
use App\Http\Controllers\System\V2\Mgr\Tenant\DeliveryZoneController;
use App\Http\Controllers\System\V2\Mgr\Tenant\TenantController;
use App\Http\Controllers\System\V2\Mgr\User\UserController;
use App\Http\Controllers\System\V2\Mgr\Categories\CategoryController;
use Illuminate\Support\Facades\Route;


Route::group(['namespace' => 'Mgr', 'prefix' => 'mgr'], function (): void {
    /* Anonymous Endpoints */
    Route::group(['namespace' => 'Auth', 'prefix' => '/auth'], function (): void {
        Route::post('/login', [AuthenticationController::class, 'login']);
        Route::post('/refresh-token', [AuthenticationController::class, 'refreshToken']);
        Route::post('/generate-session', [AuthenticationController::class, 'generateSessionToken'])
            ->middleware(['auth:api', 'auth.gate']);

        Route::group(['prefix' => '/reset'], function (): void {
            Route::post('/forget', [PasswordResetController::class, 'forget']);
            Route::post('/verify', [PasswordResetController::class, 'verify']);
            Route::post('/reset', [PasswordResetController::class, 'reset']);
        });
    });

    /* Authenticated Endpoints */
    Route::group(['middleware' => ['auth:api', 'auth.gate']], function (): void {
        Route::group(['prefix' => '/account'], function (): void {
            Route::get('/me', [AccountController::class, 'me']);
            Route::post('/logout', [AccountController::class, 'logout']);
        });

        /* Permission Redistricted Endpoints */
        Route::group(['middleware' => ['restrictAccess']], function (): void {
            Route::group(['namespace' => 'Companies'], function(){
                Route::get('/company', [CompanyController::class, 'company'])
                    ->middleware('grant:company')
                    ->name('company-read');


                Route::group([
                    'prefix' => 'companies',
                    'middleware' => 'grant:companies'
                ], function () {
                    Route::get('/', [CompanyController::class, 'index'])
                        ->name('companies-read');

                    Route::group([
                        'middleware' => 'grant:companies,contracts'
                    ], function () {
                        Route::get('/{company}/contracts', [ContractController::class, 'index'])
                            ->name('companies-contracts-read');
                        Route::post('/{company}/contracts', [ContractController::class, 'store'])
                            ->name('companies-contracts-create');;
                        Route::put('/{company}/contracts/{contract}', [ContractController::class, 'update'])
                            ->name('companies-contracts-update');
                    });


                    Route::group([
                        'namespace' => 'Quotations',
                        'middleware' => 'grant:companies,contracts,quotations'
                    ], function () {
                        Route::get('{company}/quotations/{quotation}', [QuotationController::class, 'index'])
                            ->name('companies-contracts-quotations-read');
                        Route::post('{company}/quotations', [QuotationController::class, 'store'])
                            ->name('companies-contracts-quotations-create');;

                        Route::post('{company}/contracts/{contract}/quotations/{quotation}/accept', [QuotationController::class, 'accept'])
                            ->name('companies-contracts-quotations-update');
                        Route::post('{company}/contracts/{contract}/quotations/{quotation}/decline', [QuotationController::class, 'decline'])
                            ->name('companies-contracts-quotations-update');
                    });
                });
            });

            Route::group([
                'namespace' => 'Clients',
                'middleware' => 'grant:clients'
            ], function () {
                Route::post('clients/{client}/media', [MediaController::class, 'store'])
                ->name('clients-media-create');
            });

            Route::group(['prefix' => '/apps'], function (): void {
                Route::get('/', [AppController::class, '__invoke'])->name('apps-list');
            });

            Route::group(['prefix' => '/countries'], function (): void {
                Route::get('/', [CountryController::class, '__invoke'])->name('countries-list');
            });

            Route::group(['prefix' => '/currencies'], function (): void {
                Route::get('/', [CurrencyController::class, '__invoke'])->name('currencies-list');
            });

            Route::group(['prefix' => '/tenants'], function (): void {
                Route::get('/', [TenantController::class, 'index'])->name('tenants-list');
                Route::post('/', [TenantController::class, 'store'])->name('tenants-create');
                Route::post('/{tenant}/delivery-zones', [DeliveryZoneController::class, 'store'])->name('tenants-create');
                Route::put('/{tenant}/delivery-zones', [DeliveryZoneController::class, 'update'])->name('tenants-update');


                Route::get('/{tenant}', [TenantController::class, 'show'])->name('tenants-read');
                Route::post('/{tenant}', [TenantController::class, 'update'])->name('tenants-update');
                Route::delete('/{tenant}', [TenantController::class, 'destroy'])->name('tenants-delete');
            });

            Route::group(['prefix' => '/users'], function (): void {
                Route::get('/', [UserController::class, 'index'])->name('users-list');
                Route::post('/', [UserController::class, 'store'])->name('users-create');

                Route::get('/{user}', [UserController::class, 'show'])->name('users-read');
                Route::put('/{user}', [UserController::class, 'update'])->name('users-update');
                Route::delete('/{user}', [UserController::class, 'destroy'])->name('users-delete');
            });

            // categories
            Route::get('/unlinked/categories', [UnlinkedCategoryController::class, 'index'])->name('categories-list');
            Route::get('/unmatched/categories', [UnmatchedCategoryController::class, 'index'])->name('categories-list');
            Route::delete('/unmatched/categories/{category}', [UnmatchedCategoryController::class, 'destroy'])->name('categories-delete');
            Route::get('/matched/categories', [MatchedCategoryController::class, 'index'])->name('categories-list');
            Route::post('/merge/categories', [MergeCategoryController::class, 'store'])->name('categories-create');

            Route::group(['prefix' => 'categories', 'middleware' => 'grant:categories'], function (): void {

                Route::get('', [CategoryController::class, "index"])->name('categories-list');
                Route::get('/{category}', [CategoryController::class, "show"])->name('categories-read');
                Route::get('/{linked}/suppliers', [CategoryController::class, "linkedCategoriesSuppliers"])->name('categories-read');
                Route::post('/{linked}/suppliers/{supplier_id}/manifest/load', [CategoryController::class, "linkedCategoriesManifest"])->name('categories-create');
                Route::post('/', [CategoryController::class, "store"])->name('categories-create');
                Route::put('/{category}', [CategoryController::class, "update"])->name('categories-update');
                Route::delete('/{category}', [CategoryController::class, "destroy"])->name('categories-delete');
                Route::put('/{category}/attach', [CategoryController::class, "attach"])->name('categories-update');
                Route::put('/{category}/detach', [CategoryController::class, "detach"])->name('categories-update');
                Route::group(['prefix' => '{category}', 'middleware' => 'grant:categories,manifest'], function (): void {

                    Route::get('/manifest', [ManifestController::class, "show"])->name('categories-manifest-read');
                    Route::post('/manifest', [ManifestController::class, "store"])->name('categories-manifest-create');
                    Route::put('/manifest', [ManifestController::class, "update"])->name('categories-manifest-update');
                    Route::get('/manifest/{supplier_id}', [ManifestController::class, "supplierManifest"])->name('categories-supplier-manifest-read');
                    Route::get('/manifest/{supplier_id}/linked', [ManifestController::class, "linked"])->name('categories-manifest-read');
                });
            });

            /**
             * group collection of system boxes
             */
            Route::group(['namespace' => 'Boxes', 'middleware' => 'grant:boxes'], function () {
                Route::get('/unlinked/boxes', [UnlinkedBoxController::class,'index'])->name('boxes-list');
                Route::get('unmatched/boxes', [UnmatchedBoxController::class, 'index'])->name('boxes-list');
                Route::delete('unmatched/boxes/{box}', [UnmatchedBoxController::class, 'destroy'])->name('boxes-delete');
                Route::get('matched/boxes', [MatchedBoxController::class, 'index'])->name('boxes-list');
                Route::post('/merge/boxes', [MergeBoxController::class, 'store'])->name('boxes-update');

                Route::get('boxes', [BoxController::class, 'index'])->name('boxes-list');
                Route::get('boxes/{box}', [BoxController::class, 'show'])->name('boxes-read');
                Route::post('boxes', [BoxController::class, 'store'])->name('boxes-create');
                Route::put('boxes/{box}', [BoxController::class, 'update'])->name('boxes-update');
                Route::delete('boxes/{box}', [BoxController::class, 'destroy'])->name('boxes-delete');
                Route::put('/boxes/{box}/attach', [BoxController::class, 'attach'])->name('boxes-update');
                Route::put('/boxes/{box}/detach', [BoxController::class, 'detach'])->name('boxes-update');
//                Route::resource('/boxes/{box}/relations', 'BoxRelationController');

//                Route::group(['namespace' => 'Options', 'prefix' => 'boxes/{box}'], function () {

//                    Route::resource('options', 'OptionController', [
//                        'names' => [
//                            'index' => 'access-options',
//                            'show' => 'read-options'
//                        ]
//                    ])->except([
//                        'create', 'edit', 'store'
//                    ]);
//                });
            });

            /**
             * group collection of system options
             */
            Route::group(['namespace' => 'Options', 'middleware' => 'grant:options'], function () {
                Route::get('/unlinked/options', [UnlinkedOptionController::class, 'index'])->name('options-list');
                Route::get('/unmatched/options', [UnmatchedOptionController::class, 'index'])->name('options-list');
                Route::delete('/unmatched/options/{option}', [UnmatchedOptionController::class, 'destroy'])->name('options-delete');
                Route::get('/matched/options', [MatchedOptionController::class, 'index'])->name('options-list');
                Route::post('/merge/options', [MergeOptionController::class, 'store'])->name('options-update');

                Route::get('options', [OptionController::class, 'index'])->name('options-list');
                Route::get('options/{option}', [OptionController::class, 'show'])->name('options-read');
                Route::post('options', [OptionController::class, 'store'])->name('options-create');
                Route::put('options/{option}', [OptionController::class, 'update'])->name('options-update');
                Route::delete('options/{option}', [OptionController::class, 'destroy'])->name('options-delete');
                Route::put('/options/{options}/attach', [OptionController::class, 'attach'])->name('options-update');
                Route::put('/options/{options}/detach', [OptionController::class, 'detach'])->name('options-update');
//                Route::resource('/options/{option}/relations', 'OptionRelationController');
            });

            /**
             * Message group
             */
            Route::group(['namespace' => 'Messages', 'middleware' => 'grant:messages'], function () {
               Route::get('/messages', [MessageController::class, 'index'])->name('messages-list');
               Route::get('/messages/{message}', [MessageController::class, 'show'])->name('messages-read');
               Route::put('/messages/{message}/reply', [MessageController::class, 'reply'])->name('messages-update');
            });
        });
    });

    Route::group([
        'namespace' => 'Suppliers',
        'prefix' => 'suppliers',
        'middleware' => ['signed', 'web']
    ], function () {
        Route::get(
            'invitation/{hostname:host_id}/{company}',
            [ContractController::class, 'invitation']
        )->name('suppliers.invitation');
    });
});
