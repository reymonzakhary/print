<?php

namespace App\Blueprints\Validations;

use Illuminate\Support\Str;
use RuntimeException;

class Validator
{
    /**
     *
     */
    private const RULES_DIR_PATH = "\\App\\Blueprints\\Validations\\Rules\\";

    /**
     * @param $class
     * @param $args
     * @return string
     */
    public static function __callStatic(
        $class,
        $args
    )
    {
        if (class_exists($classes = static::RULES_DIR_PATH . Str::ucfirst($class))) {
            return call_user_func_array(new $classes(), $args);
        }
        throw new RuntimeException("The $class class doesn't exists.");
    }
}
