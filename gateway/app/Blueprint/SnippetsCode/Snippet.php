<?php

namespace App\Blueprint\SnippetsCode;

use Illuminate\Support\Str;

class Snippet
{
    public function __call($name, $args)
    {
        $classname = 'App\Blueprint\SnippetsCode\\' . Str::ucfirst(Str::camel($name . 'Snippet'));
        if (class_exists($classname)) {
            return (new $classname)->handle(...$args);
        } else {
            return $name;
        }
    }
}
