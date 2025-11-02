<?php


namespace App\Scoping\Scopes\Acl;

use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class NamespaceScope implements Scope
{
    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, ?string $value)
    {
        if ($value) {
            return $builder->where('namespace', $value);
        }
    }
}
