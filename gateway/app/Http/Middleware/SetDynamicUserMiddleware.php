<?php

namespace App\Http\Middleware;

use App\Models\Tenants\Member;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetDynamicUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // This runs after your auth middleware has authenticated the user
        $user = Auth::user();

//        dd($user->id);
        if ($user && $user->mgrAccessAsMember) {
            // Check if user exists in members table
            $member = Member::query()->where('id', $user->id)->first();

            if ($member) {
                // Replace the authenticated user with member model
                Auth::setUser($member);
            }
        }

        return $next($request);
    }
}
