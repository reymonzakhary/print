<?php

namespace App\Scoping\Scopes\Transactions;

use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class TransactionCompanyScope implements Scope
{

    public function apply(Builder $builder, ?string $value)
    {
        if ($value) {
            // TODO: Insure that the company id is string or integer ?
            $builder->where('company_id', (int) $value);
        }
    }
}
