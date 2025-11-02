<?php


namespace App\Scoping\Scopes\Orders;

use App\Enums\Status;
use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class OrderStatusScope implements Scope
{

    public function apply(Builder $builder, ?string $value)
    {
        if ($value) {
            $builder->whereIn('st', explode(",",$value));
        }
    }
}
