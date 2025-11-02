<?php

namespace App\Blueprints\Snippets;

class FullnameSnippet
{
    /**
     * @param int|null $width
     * @param array    $array
     * @return string
     */
    public function __invoke(
        ?int  $width,
        array $array = []
    ): string
    {
        return collect($array)->only(['firstname', 'middlename', 'nameaffix', 'lastname'])->filter()->implode(' ');
//        if(!is_null($width) && strlen($name) > $width){
//            $name =
//                collect($array)->only(['firstname', 'middlename'])->filter()->implode(' ') .
//                " \n" .
//                collect($array)->only(['nameaffix', 'lastname'])->filter()->implode(' ');
//        }
//        return $name;
    }
}
