<?php

namespace App\Blueprints\Contracts;

use App\Blueprints\BlueprintStack;
use Illuminate\Http\Request;

interface BlueprintFactoryInterface
{
    /**
     * @param Request        $request
     * @param object         $pipe
     * @param BlueprintStack $pipeline
     * @param string         $signature
     * @return void
     */
    public function make(Request $request, object $pipe, BlueprintStack $pipeline, string $signature): void;
}
