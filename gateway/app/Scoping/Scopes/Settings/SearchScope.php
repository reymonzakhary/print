<?php


namespace App\Scoping\Scopes\Settings;


use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SearchScope implements Scope
{

    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, ?string $value)
    {
        if ($value) {
            $lower = Str::lower($value);
            $camel = Str::camel($value);
            $kebab = Str::kebab($value);
            $upper = Str::upper($value);
            $firstUpper = Str::ucfirst($value);
            $builder->where('name', 'LIKE', "%{$lower}%")
                ->orWhere('name', 'LIKE', "%{$camel}%")
                ->orWhere('name', 'LIKE', "%{$kebab}%")
                ->orWhere('name', 'LIKE', "%{$upper}%")
                ->orWhere('name', 'LIKE', "%{$firstUpper}%");
        }
    }
}
