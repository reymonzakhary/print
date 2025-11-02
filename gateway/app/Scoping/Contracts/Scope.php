<?php

namespace App\Scoping\Contracts;

use Illuminate\Database\Eloquent\Builder;

/**
 * Interface Scope
 * @package App\Scoping\Contracts
 */
interface Scope
{
    /**
     * @param Builder     $builder
     * @param string|null $value
     * @return mixed
     */
    public function apply(Builder $builder, ?string $value);
}
