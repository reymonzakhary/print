<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @author Ahmed Hazem
 *
 * @method static self salt(string $salt)
 * @method static \App\Foundation\Crypto\Crypto|null algorithm(string $hash_algorithm)
 * @method static bool check(string $value, string $hashedValue)
 *
 * @see \App\Foundation\Crypto\Crypto
 */
class Crypto extends Facade
{

    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'crypto';
    }

}
