<?php

namespace App\Scoping\Scopes\Transactions;

use App\Enums\Status;
use App\Scoping\Contracts\Scope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionSearchScope implements Scope
{

    public function apply(Builder $builder, ?string $value)
    {
        if (!empty($value) && strtolower($value) !== 'null') {
            $value = trim($value);

            $builder
                ->leftJoin('orders', 'transactions.order_id', '=', 'orders.id')
                ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
                ->leftJoin('profiles', 'profiles.user_id', '=', 'users.id')
                ->select('transactions.*')
                ->where(function ($query) use ($value) {
                    $query->where('transactions.invoice_nr', 'ilike', "%{$value}%")
                          ->orWhere('orders.order_nr', 'ilike', "%{$value}%")
                          ->orWhere('users.email', 'iLike', "%{$value}%")
                          ->orWhere(DB::raw("CONCAT(profiles.first_name, ' ', profiles.last_name)"), 'ilike', "%{$value}%");
                });
        }
    }

}
