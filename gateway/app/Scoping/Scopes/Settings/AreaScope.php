<?php


namespace App\Scoping\Scopes\Settings;


use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class AreaScope implements Scope
{

    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, ?string $value)
    {
        if ($value) {
            return $builder->where('area', $value);
        }
    }
}
