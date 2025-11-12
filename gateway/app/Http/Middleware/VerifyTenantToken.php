<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTenantToken
{
    /**
     * Handle an incoming request.
     *
     * This middleware ensures that tokens used in tenant contexts
     * actually belong to the current tenant (Universal Mode).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is authenticated via Passport token
        if ($user && $user->token()) {
            $token = $user->token();
            $currentTenantId = tenant()?->id;

            // If we're in a tenant context
            if ($currentTenantId) {
                // Verify the token belongs to this tenant
                if ($token->tenant_id !== $currentTenantId) {
                    return response()->json([
                        'message' => 'Invalid token for this tenant.',
                        'status' => Response::HTTP_FORBIDDEN
                    ], Response::HTTP_FORBIDDEN);
                }
            } else {
                // We're in central context - token should not have a tenant_id
                if ($token->tenant_id !== null) {
                    return response()->json([
                        'message' => 'Tenant token cannot be used in central context.',
                        'status' => Response::HTTP_FORBIDDEN
                    ], Response::HTTP_FORBIDDEN);
                }
            }
        }

        return $next($request);
    }
}
