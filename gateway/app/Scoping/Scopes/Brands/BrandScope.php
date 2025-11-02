<?php

namespace App\Scoping\Scopes\Brands;

use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class BrandScope implements Scope
{

    /**
     * @param Builder     $builder
     * @param string|null $value
     * @return void
     */
    public function apply(Builder $builder, ?string $value): void
    {

        if ($value) {
            $builder->whereHas('brand', function ($builder) use ($value) {
                $builder->where([['slug', $value], ['iso', app()->getLocale()]]);
            });
        }
    }
}
