<?php

namespace App\Blueprint\SnippetsCode;

use Illuminate\Support\Str;

class BirthdateSnippet
{
    public function handle($row, $data)
    {

        $lang = Str::lower(optional($row)['pdf_language']) === 'en' ? ['e', 'n'] : ['n', 'e'];
        if ($lang) {
            return optional($row)['birthdate' . $lang[0]] . ' / ' . optional($row)['birthdate' . $lang[1]];
        } else {
            return optional($row)['birthplace'];
        }
    }
}
