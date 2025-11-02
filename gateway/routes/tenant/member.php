<?php

use App\Http\Controllers\Tenant\Mgr\Members\Address\AddressController;
use App\Http\Controllers\Tenant\Mgr\Members\MemberController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'grant:members'], function () {
    Route::put('/members/{member}/verification', [MemberController::class, 'verification'])
        ->name('members-update');

    /**
     * members group
     * handel all the event for get all en single status
     */
    Route::apiResource('members', MemberController::class, [
        'names' => [
            'index' => 'members-list',
            'show' => 'members-read',
            'store' => 'members-create',
            'update' => 'members-update',
            'destroy' => 'members-delete',
        ]
    ]);
    Route::post('members/{member}/restore', [MemberController::class, 'restore' ])->name('members-update');

    Route::apiResource('members/{member}/addresses', AddressController::class, [
        'names' => [
            'index' => 'members-addresses-list',
            'show' => 'members-addresses-read',
            'store' => 'members-addresses-create',
            'update' => 'members-addresses-update',
            'destroy' => 'members-addresses-delete',
        ]
    ]);
});
