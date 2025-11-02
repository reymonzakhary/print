<?php


namespace App\Scoping\Scopes;


use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class OrderTypeScope implements Scope
{
    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, ?string $value)
    {
        return $builder->where('type', $value === null);
    }
}
