<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProfileJsonResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure                  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (
            $response instanceof JsonResponse &&
            app('debugbar')->isEnabled() &&
            $request->has('_debug')
        ) {
            $response->setData($response->getData(true) + [
                    '_debugbar' => Arr::only(app('debugbar')->getData(), ['queries', 'memory', 'time'])
                ]);
        }

        return $response;
    }
}
