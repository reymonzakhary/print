<?php

use App\Http\Controllers\Tenant\Mgr\Transactions\Logs\TransactionLogController;
use App\Http\Controllers\Tenant\Mgr\Transactions\TransactionController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'grant:transactions',
    'namespace' => 'Transactions'
], function () {

    Route::resource('transactions', TransactionController::class, [
        'names' => [
            'index' => 'transactions-list',
            'show' => 'transactions-read'
        ]
    ])->only(['index', 'show']);

    Route::group([
        'middleware' => 'grant:transactions,logs',
        'prefix' => 'transactions/{transaction}',
        'as' => 'transactions-',
        'namespace' => 'Logs'
    ], function () {
        Route::get('/logs', TransactionLogController::class)->name('read');
    });

});
