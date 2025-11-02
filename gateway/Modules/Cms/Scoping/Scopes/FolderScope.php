<?php

namespace Modules\Cms\Scoping\Scopes;

use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class FolderScope implements Scope
{
    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, ?string $value)
    {
        if ($value) {
            return $builder->where('folder_id', $value);
        }
    }
}
