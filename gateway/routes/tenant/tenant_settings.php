<?php

/**
 * Tenant settings
 */

use App\Http\Controllers\Tenant\Mgr\Account\Setting\PluginController;
use App\Http\Controllers\Tenant\Mgr\Account\Setting\TenantHostnameController;
use App\Http\Controllers\Tenant\Mgr\Contracts\TenantContractController;
use Illuminate\Support\Facades\Route;



Route::group(['prefix' => 'tenant'], function () {
    Route::group(['middleware' => 'grant:settings'], function () {
        Route::get('settings', [TenantHostnameController::class, 'show'])->name('settings-read');
        Route::put('settings', [TenantHostnameController::class, 'update'])->name('settings-update');
    });

    Route::group(['middleware' => ['grant:messages']], function () {
        Route::get('messages', [\App\Http\Controllers\Tenant\Mgr\Account\Setting\MessageController::class, 'index'])
            ->name('messages-list');
        Route::get('messages/{message}', [\App\Http\Controllers\Tenant\Mgr\Account\Setting\MessageController::class, 'show'])
            ->name('messages-read');
        Route::post('messages', [\App\Http\Controllers\Tenant\Mgr\Account\Setting\MessageController::class, 'store'])
            ->name('messages-create');
        Route::post('messages/{message}/reply', [\App\Http\Controllers\Tenant\Mgr\Account\Setting\MessageController::class, 'reply'])
            ->name('messages-update');
    });

    Route::group(['middleware' => 'grant:contracts'], function () {
        Route::get('contracts/{contract}', [TenantContractController::class, 'show'])->name('contracts-read');
        Route::put('contracts/{contract}', [TenantContractController::class, 'update'])->name('contracts-update');

    });

    Route::group(['middleware' => 'grant:plugins', 'prefix' => 'plugin'], function () {
        Route::get('/', [PluginController::class, 'index'])->name('*');
        Route::put('/', [PluginController::class, 'update'])->name('*');

        Route::post('/sync', [PluginController::class, 'sync'])->name('*');
        Route::get('/categories', [PluginController::class, 'categories'])->name('*');
        Route::post('/get-price', [PluginController::class, 'getPrice'])->name('*');
    });
});
