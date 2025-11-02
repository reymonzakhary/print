<?php

namespace App\Scoping\Scopes\Boxes;

use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class BoxScope implements Scope
{

    public function apply(Builder $builder, ?string $value)
    {
        if ($value) {
            $builder->where('row_id', $value);
        }
    }
}
