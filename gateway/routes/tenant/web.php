<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\Mgr\Members\Mail\MailController as MemberMailController;
use App\Http\Controllers\Tenant\Mgr\Quotations\Mails\MailController as QuotationMailController;
use App\Http\Controllers\Tenant\Mgr\Users\Mail\MailController as UserMailController;
use App\Http\Controllers\Tenant\Mgr\Webhooks\DwdWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/quotations/{quotation}/accept', [QuotationMailController::class, 'accept'])
    ->middleware('signed')
    ->name('quotations-accept');

Route::get('/quotations/{quotation}/reject', [QuotationMailController::class, 'reject'])
    ->middleware('signed')
    ->name('quotations-reject');

Route::get('/members/{member}/verify', [MemberMailController::class, 'verify'])
    ->middleware('signed')
    ->name('members-email-verify');

Route::get('/users/{user}/verify', [UserMailController::class, 'verify'])
    ->middleware('signed')
    ->name('users-email-verify');

