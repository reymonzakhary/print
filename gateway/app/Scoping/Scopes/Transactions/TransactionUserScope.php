<?php

namespace App\Scoping\Scopes\Transactions;

use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class TransactionUserScope implements Scope
{

    public function apply(Builder $builder, ?string $value)
    {
        if ($value) {
            $builder->where('user_id', (int) $value);
        }
    }
}
