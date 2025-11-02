<?php

namespace App\Scoping\Scopes\Transactions;

use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class TransactionOrderScope implements Scope
{

    public function apply(Builder $builder, ?string $value): void
    {
        if ($value) {
            $builder->where('order_id', (int) $value);
        }
    }
}
