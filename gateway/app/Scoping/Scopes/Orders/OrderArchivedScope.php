<?php


namespace App\Scoping\Scopes\Orders;

use App\Enums\Status;
use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class OrderArchivedScope implements Scope
{

    public function apply(Builder $builder, ?string $value)
    {
        if ($value) {
            $builder->where('archived', true);
        } else {
            $builder->where('archived', false); // returns only un archived orders
        }
    }
}
