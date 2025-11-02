<?php

namespace App\Scoping\Scopes\Hostnames;

use App\Scoping\Contracts\Scope;
use Illuminate\Database\Eloquent\Builder;

class SearchHostnameScope  implements Scope
{

    public function apply(Builder $builder, ?string $value): void
    {
        if($value) {
            $builder->join('websites', 'hostnames.website_id', '=', 'websites.id')
                ->addSelect('hostnames.*', 'websites.*' , 'hostnames.id as id' , 'websites.id as website_id')
                ->where('custom_fields', 'iLike', sprintf('%%%s%%', $value))
                ->orWhere('fqdn', 'iLike', sprintf('%%%s%%', $value))
                ->orWhere('websites.uuid', 'iLike', sprintf('%%%s%%', $value));
        }
    }
}
