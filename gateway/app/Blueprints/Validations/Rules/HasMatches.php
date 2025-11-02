<?php

namespace App\Blueprints\Validations\Rules;

use RuntimeException;

class HasMatches
{

    /**
     * @param string $string
     * @param string $pattern
     * @param string $class
     * @param array  $array
     * @return array
     */
    public function __invoke(
        string $string,
        string $pattern,
        string $class,
        array  $array = [],
        string $select = null,
    ): array
    {
        preg_match_all($pattern, $string, $matches);
        return match (true) {
            is_null($matches),
            !is_array($matches),
            is_null(optional($matches)[$select ?? 0]) =>
            throw new RuntimeException("The requested regex does not match any values."),
            default => optional($matches)[$select ?? 0]
        };
    }
}
