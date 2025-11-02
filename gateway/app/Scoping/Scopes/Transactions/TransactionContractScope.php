<?php

namespace App\Scoping\Scopes\Transactions;

use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class TransactionContractScope implements Scope
{

    public function apply(Builder $builder, ?string $value): void
    {
        if ($value) {
            $builder->where('contract_id', $value);
        }
    }
}
