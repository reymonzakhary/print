<?php

namespace App\Blueprint;

class OutBlueprintFactory
{
    public function out($processor)
    {
        return $processor['output'];
    }
}
