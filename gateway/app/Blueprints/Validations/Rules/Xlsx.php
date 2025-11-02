<?php

namespace App\Blueprints\Validations\Rules;

use RuntimeException;

class Xlsx
{
    /**
     * @param $request
     * @param $column
     * @return array
     */
    public function __invoke(
        $request,
        $column
    ): array
    {
        return match (true) {
            is_null($request) =>
            throw new RuntimeException("The {$column} field is required."),
            !is_array($request) => throw new RuntimeException("The {$column} field has to be an array."),
            default => $request
        };
    }
}
