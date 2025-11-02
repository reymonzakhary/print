<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class RestrictionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->route()->getName() !== "*") {
            $user = $request->user()->load('permissions');
            if (!optional($user)->hasPermission($request->route()->getName())) {
                return response()->json([
                    'message' => 'You are not permitted to do this action.',
                    'status' => Response::HTTP_FORBIDDEN
                ], Response::HTTP_FORBIDDEN);
            }
        }
        return $next($request);
    }
}
