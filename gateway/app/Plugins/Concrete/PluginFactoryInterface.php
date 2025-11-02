<?php

namespace App\Plugins\Concrete;

use App\Plugins\Config\PluginConfigRepository;
use App\Plugins\Contracts\PluginStack;
use Illuminate\Http\Request;

interface PluginFactoryInterface
{
    /**
     * @param Request $request
     * @param object $pipe
     * @param PluginStack $pipeline
     * @param string $signature
     * @param PluginConfigRepository $configRepository
     * @return void
     */
    public function make(Request $request, object $pipe, PluginStack $pipeline, string $signature, PluginConfigRepository $configRepository): void;
}
