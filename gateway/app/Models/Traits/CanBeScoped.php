<?php

namespace App\Models\Traits;

use App\Scoping\Scoper;
use Illuminate\Database\Eloquent\Builder;

trait CanBeScoped
{
    /**
     * @param Builder $builder
     * @param array   $scopes
     * @return Builder
     */
    public function scopeWithScopes(
        Builder $builder,
        array   $scopes = []
    ): Builder
    {
        return (new Scoper(request()))->apply($builder, $scopes);
    }
}
