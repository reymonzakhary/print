<?php

namespace Modules\Cms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Cms\Foundation\Cart\Contracts\CartContractInterface;

class CartMiddleware
{
    public function __construct(protected CartContractInterface $cart)
    { }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->cart->exists(user: $request->user())) {
            $this->cart->create($request->user());
        }

        return $next($request);
    }
}
