<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\V2\Mgr\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ImpersonateTokenRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Http\Resources\System\Auth\PassportTokenResource;
use App\Models\Impersonation;
use App\Models\User;
use App\Utilities\ProxyRequest;
use Exception;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticationController extends Controller
{
    /**
     * @param ProxyRequest $proxy
     */
    public function __construct(
        private readonly ProxyRequest $proxy
    ) {
    }

    /**
     * Authenticate by a given credentials and returns the auth tokens
     *
     * @param LoginRequest $request
     * @param Hasher $hashed
     *
     * @return JsonResponse|PassportTokenResource
     * @throws Exception
     */
    public function login(
        LoginRequest $request,
        Hasher       $hashed,
    ): JsonResponse|PassportTokenResource
    {
        if (!$user = User::query()->firstWhere('email', $request->input('email'))) {
            return response()->json([
                'message' => __('auth.combination_does_not_exists'),
                'status' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        if (!$hashed->check($request->input('password'), $user->getAttribute('password'))) {
            return response()->json([
                'message' => __('auth.combination_does_not_exists'),
                'status' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        if (!$response = $this->proxy->grantPasswordToken($request->input('email'), $request->input('password'))) {
            return response()->json([
                'message' => __('auth.combination_does_not_exists'),
                'status' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        return PassportTokenResource::make($response)
            ->additional([
                'message' => __('You have logged in successfully'),
                'status' => Response::HTTP_OK
            ]);
    }

    /**
     * Refreshing the auth tokens
     *
     * @param RefreshTokenRequest $request
     *
     * @return JsonResponse|PassportTokenResource
     * @throws Exception
     */
    public function refreshToken(
        RefreshTokenRequest $request,
    ): JsonResponse|PassportTokenResource
    {
        if (!$response = $this->proxy->refreshAccessToken($request->validated('refresh_token'))) {
            return response()->json([
                'message' => __('auth.combination_does_not_exists'),
                'status' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        return PassportTokenResource::make($response)
            ->additional([
                'message' => __('auth.token_refreshed'),
                'status' => Response::HTTP_OK
            ]);
    }

    /**
     * Generate Impersonation Token
     *
     * Generates a one-time, short-lived impersonation token for a tenant user. This is typically used
     * by admins or system-level users to initiate a secure impersonation session with a specific tenant user.
     *
     * @header Origin http://{subdomain}.yourdomain.test
     * @header Referer http://{subdomain}.yourdomain.test
     *
     * @bodyParam tenant_id string required The UUID of the tenant to generate the token for. Example: 44b659b6-d3c9-4292-9056-a70beaa422bc
     *
     * @response 200
     * {
     *     "token": "8f27c0c7-3fc4-4eb1-aed4-60a2f6a6b0ea",
     *     "message": "Session token generated"
     * }
     *
     * @response 404
     * {
     *     "message": "Email not found"
     * }
     *
     * @param ImpersonateTokenRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function generateSessionToken(
        ImpersonateTokenRequest $request
    ): JsonResponse
    {
        $tenant_id = $request->input('tenant_id');
        
        $initiator_id = auth()->id();
        
        if (!$initiator_id) {
            return response()->json([
                'message' => __('UNAUTHORIZED.'),
                'status' => Response::HTTP_UNAUTHORIZED
            ], Response::HTTP_UNAUTHORIZED);
        }

        switchTenant($tenant_id);
        $custom_fields = \tenantCustomFields();
        $email = $custom_fields->pick('email');
        $company_name = $custom_fields->pick('company_name');
        if (!$email) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        Impersonation::where('target_tenant_id', $tenant_id)->delete();

        $impersonation = Impersonation::create([
            'id' => Str::uuid(),
            'initiator_id' => $initiator_id,
            'target_tenant_id' => $tenant_id,
            'email' => $email,
            'supplier_name' => $company_name,
            'meta' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
            'expires_at' => now()->addMinutes(10),
        ]);

        return response()->json([
            'token' => $impersonation->id,
            'message' => 'Session token generated',
        ]);
    }
}
