<?php
/** params [email, password]*/

use App\Http\Controllers\Tenant\Mgr\Auth\AuthenticationController;
use App\Http\Controllers\Tenant\Mgr\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

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
