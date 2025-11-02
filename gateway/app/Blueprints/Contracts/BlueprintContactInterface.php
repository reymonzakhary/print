<?php

namespace App\Blueprints\Contracts;

interface BlueprintContactInterface
{
    public function run(int $step = 1);
}
