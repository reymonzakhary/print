<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CtxAuthMiddleware
{


    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure                  $next
     * @param                          $args
     * @return mixed
     */
    public function handle($request, Closure $next, $args)
    {
        if (!optional($request->user())->canAccess($args)) {
            return response()->json(
                [
                    'message' => __('UNAUTHORIZED.'),
                    'status' => Response::HTTP_UNAUTHORIZED
                ], Response::HTTP_UNAUTHORIZED
            );
        }

        return $next($request);
    }
}
