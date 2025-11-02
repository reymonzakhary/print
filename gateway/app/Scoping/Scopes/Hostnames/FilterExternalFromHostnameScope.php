<?php

namespace App\Scoping\Scopes\Hostnames;

use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class FilterExternalFromHostnameScope  implements Scope
{

    public function apply(Builder $builder, ?string $value): void
    {
        if($value) {
            $builder->join('websites', 'hostnames.website_id', '=', 'websites.id')
                ->where('external', '=', (bool)$value);
        }
    }
}
