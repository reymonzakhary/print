<?php

namespace Modules\Cms\Core\Facades;

use Illuminate\Support\Facades\Facade;

class Cacheable  extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'cacheable';
    }
}
