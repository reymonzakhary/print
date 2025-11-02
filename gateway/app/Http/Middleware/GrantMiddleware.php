<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GrantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request     $request
     * @param Closure     $next
     * @param string      $namespace
     * @param string|null $area
     * @return mixed
     */
    public function handle($request, Closure $next, string $namespace, string $area = null)
    {
        if ($area && str_contains($area, '_')) {
            $area = str_replace('_', '-', $area);
        }

        $permissions = $area ? "{$namespace}-{$area}-list" : "{$namespace}-list";

        if (auth()->check()) {
            if (auth()->user()->can($permissions)) {
                return $next($request);
            }
        }

        return response()->json([
            'status' => Response::HTTP_NOT_FOUND,
            'message' => __('Page not found')
        ], Response::HTTP_NOT_FOUND);

    }
}
