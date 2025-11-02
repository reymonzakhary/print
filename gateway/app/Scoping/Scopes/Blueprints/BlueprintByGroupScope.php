<?php

namespace App\Scoping\Scopes\Blueprints;

use App\Enums\BlueprintNamespaces;
use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class BlueprintByGroupScope implements Scope
{

    public function apply(Builder $builder, ?string $value)
    {
        if ($value) {
            $builder->whereIn('ns', BlueprintNamespaces::getNamespaceGroup($value));
        }
    }
}
