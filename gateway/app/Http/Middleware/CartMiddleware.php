<?php

namespace App\Http\Middleware;

use App\Cart\Contracts\CartContractInterface;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CartMiddleware
{
    public function __construct(protected CartContractInterface $cart)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->cart->exists(user: $request->user())) {
            $this->cart->create($request->user());
        }

        return $next($request);
    }
}
