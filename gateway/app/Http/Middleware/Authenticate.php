<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param Request $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return route('login');
        }
    }

//$response->headers->setCookie(
//new Cookie(
//'XSRF-TOKEN', $request->session()->token(), $this->availableAt(60 * $config['lifetime']),
//$config['path'], $config['domain'], $config['secure'], false, false, $config['same_site'] ?? null
//)
//);
//
//return $response;
}
