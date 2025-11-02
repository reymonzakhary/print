<?php

namespace App\Blueprint\Contract;

use Closure;

interface BlueprintContract
{
    public function handle($request, Closure $next, $args = null);
}
