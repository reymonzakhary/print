<?php

namespace App\Blueprint\SnippetsCode;

class FullnameSnippet
{
    public function handle($row, $data)
    {
        $fullname = collect($row)->only(['firstname', 'middlename', 'nameaffix', 'lastname'])->filter()->implode(' ');
        $max = (int)data_get($data, 'input.break.after');
        $max_char = !$max || $max == 0 ? null : $max;
        if (!is_null($max_char) && strlen($fullname) > $max_char) {
            $fullname =
                collect($row)->only(['firstname', 'middlename'])->filter()->implode(' ') .
                " \n" .
                collect($row)->only(['nameaffix', 'lastname'])->filter()->implode(' ');
        }
        return $fullname;
    }
}
