<?php

namespace App\Blueprints\Validations\Rules;

use Illuminate\Support\Str;
use RuntimeException;

class HasValueFromRegExp
{
    /**
     * @param string $string
     * @param string $pattern
     * @param string $class
     * @param array  $array
     * @param int    $limit
     * @return string
     */
    public function __invoke(
        string $string,
        string $pattern,
        string $class,
        array  $array = [],
        int    $limit = 10
    ): string
    {
        return preg_replace_callback($pattern, function ($matches) use ($array, $class) {
            return match (true) {
                is_null($matches),
                !is_array($matches),
                is_null(optional($matches)[0]),
                is_null(optional($matches)[1]) =>
                throw new RuntimeException("The requested regex does not match any values."),
                default => $array[Str::lower(Str::replace(' ', '_', optional($matches)[1]))]
            };
        }, $string, $limit);
    }
}
