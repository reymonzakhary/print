<?php

namespace App\Blueprints\Snippets;

use Illuminate\Support\Str;

/**
 * @copyright Copyright (c) Reymon Zakhary
 * to handle snippets
 */
class Snippet
{
    protected const SNIPPET_PATH = "\\App\\Blueprints\\Snippets\\";

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
        if (class_exists($classes = static::SNIPPET_PATH . Str::ucfirst(Str::camel($class . 'Snippet')))) {
            return call_user_func_array(new $classes(), $args);
        }

        throw new \RuntimeException("The {$class} class doesn't exists.");
    }

}
