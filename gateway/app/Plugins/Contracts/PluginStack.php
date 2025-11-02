<?php

namespace App\Plugins\Contracts;

use App\Plugins\Concrete\PluginActionContractInterface;
use App\Plugins\Config\PluginConfigRepository;
use Closure;
use Illuminate\Http\Request;

class PluginStack
{
    /**
     * @var Closure
     */
    protected Closure $start;

    /**
     * build stack
     */
    public function __construct()
    {
        $this->start = static function () {};
    }

    /**
     * @param Request $request
     * @param PluginPipeline $pip
     * @param callable|PluginActionContractInterface $pipeline
     * @param string $signature
     * @param PluginConfigRepository $configRepository
     */
    public function add(
        Request                                 $request,
        PluginPipeline                          $pip,
        callable|PluginActionContractInterface  $pipeline,
        string                                  $signature,
        PluginConfigRepository                  $configRepository,
    ): void
    {
        $next = $this->start;
        $this->start = static function () use ($pipeline, $pip, $request, $next, $signature, $configRepository) {
            return $pipeline($request, $pip, $next, $signature, $configRepository);
        };
    }

    /**
     * @return mixed
     */
    public function handle(): mixed
    {
        return call_user_func($this->start);
    }
}
