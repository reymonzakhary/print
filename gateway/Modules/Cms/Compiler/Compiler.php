<?php


namespace Modules\Cms\Compiler;


interface Compiler
{

    public function get(string $string): array;

    public function replace(string $string, string $key): string;


}
