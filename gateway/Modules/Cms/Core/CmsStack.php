<?php

namespace Modules\Cms\Core;

use Closure;

class CmsStack
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
        $this->start = static function () {
        };
    }

    /**
     * @param Request                          $request
     * @param Pipeline                         $pip
     * @param callable|ActionContractInterface $pipeline
     * @param string                           $signature
     */
    public function add(
        Request                          $request,
        Pipeline                         $pip,
        callable|ActionContractInterface $pipeline,
        string                           $signature
    ): void
    {
        $next = $this->start;
        $this->start = static function () use ($pipeline, $pip, $request, $next, $signature) {
            return $pipeline($request, $pip, $next, $signature);
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
