<?php

namespace App\Blueprint;

use Illuminate\Support\Str;

class PipelineBlueprintFactory
{
    public function pipeline($data)
    {
        return collect($data['pipeline'])->map(function ($o) {
            if (class_exists("\App\Blueprint\Processors\\" . $o['mode'] . 'Processor')) {
                return "\App\Blueprint\Processors\\" . Str::ucfirst(Str::camel($o['mode'])) . 'Processor';
            }

        })->filter();
    }
}
