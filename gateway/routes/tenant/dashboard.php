<?php

use App\Http\Controllers\Tenant\Mgr\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'dashboard'], function () {
    Route::get('/', [DashboardController::class, '__invoke'])->name('*');
});
