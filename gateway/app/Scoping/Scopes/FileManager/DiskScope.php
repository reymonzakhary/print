<?php

declare(strict_types=1);

namespace App\Scoping\Scopes\FileManager;

use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class DiskScope implements Scope
{
    /**
     * @param Builder     $builder
     * @param string|null $value
     */
    public function apply(Builder $builder, ?string $value): void
    {
        if ($value) {
            $builder->where('disk', $value);
        }
    }
}
