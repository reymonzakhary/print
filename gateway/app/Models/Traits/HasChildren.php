<?php


namespace App\Models\Traits;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasChildren
{
    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeParents(Builder $builder)
    {
        return $builder->whereNull('parent_id');
    }

    /**
     * Get the model's children.
     *
     * @return HasMany
     */

}
