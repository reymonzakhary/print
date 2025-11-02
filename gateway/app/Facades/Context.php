<?php

namespace App\Facades;

use App\Models\Tenants\Address;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;

/**
 * @author Reymon Zakhary
 * @method static self from(?string $ctx)
 * @method static Address address()
 * @method static Collection addresses()
 * @method static bool hasAddress()
 * @method static bool hasMgrAddress()
 * @method static Collection get()
 * @method static Model first()
 * @method static Model current()
 *
 * @see \App\Foundation\Context\Contextandler;
 */

class Context extends Facade
{

    /**
     * Get the accessor for the facade.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'context';
    }
}
