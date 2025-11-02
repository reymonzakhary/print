<?php


namespace App\Scoping\Scopes\Transactions;

use App\Enums\Status;
use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class TransactionPaymentMethodScope implements Scope
{

    public function apply(Builder $builder, ?string $value): void
    {
        if ($value) {
            $builder->where('payment_method', $value);
        }
    }
}
