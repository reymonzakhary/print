<?php

namespace App\Scoping\Scopes\Tags;

use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class SearchNameScope implements Scope
{

    /**
     * @param Builder     $builder
     * @param string|null $value
     */
    public function apply(Builder $builder, ?string $value): void
    {
        if ($value) {
            $builder->where('name', 'LIKE', "%{$value}%");
        }
    }
}
