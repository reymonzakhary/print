<?php

namespace App\Scoping\Scopes\Members;

use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class MemberTypeScope implements Scope
{
    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, ?string $value)
    {
        if($value) {
            return $builder->where('type', $value);
        }
    }
}
