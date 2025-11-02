<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @author Ahmed Hazem
 *
 * @method array validation(TenantsDesignProvider $provider)
 * @method array store(TenantsDesignProvider $provider)
 * @method array update(TenantsDesignProvider $provider, DesignProviderTemplate $template)
 * 
 * @see \App\Foundation\DesignProviders\DesignProvider;
 */
class DesignProvider extends Facade
{

    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'design-provider';
    }

}
