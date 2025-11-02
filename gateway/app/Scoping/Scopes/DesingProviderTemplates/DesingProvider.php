<?php


namespace App\Scoping\Scopes\DesingProviderTemplates;


use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class DesingProvider implements Scope
{

    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, ?string $value)
    {

        if ($value) {
            return $builder->where('design_provider_id', $value);
        }
    }
}
