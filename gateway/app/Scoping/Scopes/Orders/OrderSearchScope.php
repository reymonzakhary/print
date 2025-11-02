<?php


namespace App\Scoping\Scopes\Orders;

use App\Enums\Status;
use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderSearchScope implements Scope
{

    public function apply(Builder $builder, ?string $value)
    {
        if ($value && $value !== 'null') {
            $builder
                ->leftJoin('users', 'orders.user_id', '=', 'users.id')
                ->leftJoin('profiles', 'profiles.user_id', '=', 'users.id')
                ->where('orders.reference', 'iLike', sprintf('%%%s%%', $value))
                ->orWhere('orders.order_nr', 'iLike', sprintf('%%%s%%', $value))
                ->orWhere('orders.created_at', 'iLike', sprintf('%%%s%%', $value))
                ->orWhere('users.email', 'iLike', sprintf('%%%s%%', $value))
                ->orWhere(DB::raw("CONCAT(profiles.first_name, ' ', profiles.last_name)"), 'iLike', sprintf('%%%s%%', $value));
        }
    }
}
