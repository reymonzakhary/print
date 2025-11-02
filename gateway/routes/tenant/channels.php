<?php

use App\Models\Tenants\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('quotations', function ($user) {
    return (bool)optional($user)->isAbleTo('quotations-list');
});

Broadcast::channel('orders', function ($user) {
    return (bool)optional($user)->isAbleTo('orders-list');
});

Broadcast::channel('campaigns', function ($user) {
    return (bool)optional($user)->isAbleTo('campaigns-list');
});

Broadcast::channel('designProviderTemplate', function ($user) {
    return (bool)optional($user)->isAbleTo('design-providers-templates-list');
});

Broadcast::channel("fm", function ($user) {
    return (bool)optional($user)->isAbleTo('auth-access');
});

Broadcast::channel("products", function ($user) {
    return (bool)optional($user)->isAbleTo('print-assortments-combinations-list');
});

Broadcast::channel("blueprints", function ($user) {
    return (bool)optional($user)->isAbleTo('auth-access');
});

Broadcast::channel("categories", function ($user) {
    return (bool)optional($user)->isAbleTo('print-assortments-categories-list');
});

Broadcast::channel('messages', function (User $user): bool {
    return $user->isOwner();
});

Broadcast::channel('customerOrders.{user_id}', function ($user , $user_id) {
    return (bool)optional($user)->id == (int) $user_id;
});
