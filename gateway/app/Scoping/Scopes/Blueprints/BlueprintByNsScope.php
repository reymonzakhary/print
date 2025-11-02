<?php

namespace App\Scoping\Scopes\Blueprints;

use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class BlueprintByNsScope implements Scope
{

    public function apply(Builder $builder, ?string $value)
    {
        if ($value) {
            $builder->where('ns', $value);
        }
    }
}
