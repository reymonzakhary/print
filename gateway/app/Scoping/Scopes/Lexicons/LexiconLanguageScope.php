<?php

declare(strict_types=1);

namespace App\Scoping\Scopes\Lexicons;

use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

final class LexiconLanguageScope implements Scope
{
    public function apply(Builder $builder, ?string $value): void
    {
        $builder->where('language', $value??app()->getLocale());
    }
}
