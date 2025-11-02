<?php

namespace App\Scoping\Scopes\Members;

use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class MemberOnlyTrashedScope implements Scope
{
    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, ?string $value)
    {
        if(filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return $builder->onlyTrashed();
        }
    }
}
