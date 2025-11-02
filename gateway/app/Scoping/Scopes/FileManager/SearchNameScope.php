<?php

declare(strict_types=1);

namespace App\Scoping\Scopes\FileManager;

use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SearchNameScope implements Scope
{
    public function apply(Builder $builder, ?string $value): void
    {
        if ($value) {
            $builder->where('name', 'iLIKE', sprintf('%%%s%%', $value));
        }
    }
}
