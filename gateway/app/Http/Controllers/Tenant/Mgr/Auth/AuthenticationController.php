<?php

namespace App\Http\Controllers\Tenant\Mgr\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ImpersonateLoginRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Impersonation;
use App\Models\Tenants\User;
use App\Utilities\ProxyRequest;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\ClientRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Auth
 *
 * APIs for managing Authentication
 *
 * @package CEC3.0\controllers
 *
 * @author  Reymon Zakhary <reymon@prindustry.com>
 */
class AuthenticationController extends Controller
{

    /**
     * AuthenticationController constructor.
     * @param ProxyRequest $proxy
     */
    public function __construct(protected ProxyRequest $proxy) {}

    /**
     * Login
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     *
     * @bodyParam email string required The email of user. Example: test@gmail.com
     * @bodyParam password string required The password of user. Example: 123456
     *
     * @response 200
     * {
     *     "data": {
     *         "token_type": "Bearer",
     *         "expires_in": 432000,
     *         "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.",
     *         "refresh_token": "def50200db44970624233bd9e4416de8a1d488"
     *     },
     *     "message": "You have been logged in successfully",
     *     "status": 200
     * }
     *
     * @response 404
     * {
     *     "message": "This combination does not exists.",
     *     "status": 404
     * }
     *
     * @response 422
     * {
     *     "message": "The selected email is invalid.",
     *     "errors": {
     *         "email": [
     *             "The selected email is invalid."
     *         ]
     *     }
     * }
     * @param LoginRequest $request
     * @return ResponseFactory|JsonResponse|Response
     */
    public function login(
        LoginRequest $request
    ): JsonResponse|Response|ResponseFactory
    {

        $user = User::where('email', $request->email)->first();

        abort_unless($user, 404, __('This combination does not exists.'));
        abort_unless(
            Hash::check($request->password, $user->password),
            404,
            __('This combination does not exists.')
        );

        if ($resp = $this->proxy->grantPasswordToken($request->email, $request->password)) {
            if (!optional($user)->canAccess('mgr')) {
                return response()->json([
                    'message' => __('You are not unauthorized, contact your administrator.'),
                    'status' => Response::HTTP_UNAUTHORIZED
                ], Response::HTTP_UNAUTHORIZED);
            }
            return response([
                'data' => [
                    "token_type" => "Bearer",
                    "expires_in" => $resp->expires_in,
                    "access_token" => $resp->access_token,
                    "refresh_token" => $resp->refresh_token,
                ],
                'message' => __('You have been logged in successfully'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __('This combination does not exists.'),
            'status' => Response::HTTP_NOT_FOUND
        ], Response::HTTP_NOT_FOUND);

    }

    /**
     * @OA\Post(
     * path="/api/v1/mgr/refresh",
     * summary="Get Bearer token",
     * description="Get new Bearer token",
     * operationId="RefreshToken",
     * tags={"Authentication"},
     * security={{ "Bearer":{} }},
     * @OA\SecurityScheme(
     *      securityScheme="bearerAuth",
     *      in="header",
     *      name="bearerAuth",
     *      type="oauth2",
     *      scheme="passport",
     *      bearerFormat="JWT",
     * ),
     * @OA\Header(
     *  header="Authorization",
     *       @OA\Schema(
     *           type="string",
     *           format="Bearer token"
     *       ),
     *       description="Bearer token is required and expird in 5 minute "
     * ),
     *  @OA\Response(
     *     response=200,
     *     description="successful operation",
     *     @OA\Schema(type="string"),
     *     @OA\Header(
     *       header="Authorization",
     *       @OA\Schema(
     *           type="string",
     *           format="Bearer token"
     *       ),
     *       description="Bearer token is required and expird in 5 minute "
     *     ),
     *     @OA\Header(
     *       header="X-Rate-Limit",
     *       @OA\Schema(
     *           type="integer",
     *           format="int32"
     *       ),
     *       description="60 calls per minute allowed by the user"
     *     ),
     *     @OA\Header(
     *       header="X-Expires-After",
     *       @OA\Schema(
     *          type="string",
     *          format="date-time",
     *       ),
     *       description="date in UTC when token expires"
     *     ),
     *    @OA\JsonContent(
     *       @OA\Property(property="token_type", type="string", example="Bearer"),
     *       @OA\Property(property="expires_in", type="integer", example="1298"),
     *       @OA\Property(property="access_token", type="string", example="Bearer token"),
     *       @OA\Property(property="refresh_token", type="string", example="Refresh token"),
     *       @OA\Property(property="message", type="string", example="Token has been refreshed."),
     *   )
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Unauthorized",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthorized.")
     *    )
     *  )
     * ),
     * @param Request $request
     * @return ResponseFactory|Response
     */
    public function refreshToken(
        Request $request
    ): Response|ResponseFactory
    {
        $resp = $this->proxy->refreshAccessToken($request->get('refresh_token'));

        if ($resp) {
            return response([
                'data' => [
                    "token_type" => "Bearer",
                    "expires_in" => optional($resp)->expires_in,
                    "access_token" => optional($resp)->access_token,
                    "refresh_token" => optional($resp)->refresh_token,
                ],
                'message' => __('Token has been recreated.'),
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __("The refresh token is invalid."),
            'status' => Response::HTTP_UNAUTHORIZED
        ], Response::HTTP_UNAUTHORIZED);
    }
    /**
     * Impersonation Login
     *
     * Authenticate a user using a one-time impersonation token (e.g., for admin-to-supplier access).
     * This endpoint issues a short-lived access token (10 minutes) and switches the context to the target tenant.
     *
     * @header Origin http://{subdomain}.yourdomain.test
     * @header Referer http://{subdomain}.yourdomain.test
     *
     * @bodyParam token string required The one-time impersonation token. Example: 8f27c0c7-3fc4-4eb1-aed4-60a2f6a6b0ea
     *
     * @response 200
     * {
     *     "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
     *     "token_type": "Bearer",
     *     "expires_at": "2025-06-12T14:00:15.000000Z",
     *     "supplier_name": "Acme Corp",
     *     "email": "supplier@example.com",
     *     "tenant_id": "44b659b6-d3c9-4292-9056-a70beaa422bc"
     * }
     *
     * @response 403
     * {
     *     "message": "Invalid or expired token"
     * }
     *
     * @response 404
     * {
     *     "message": "User not found"
     * }
     *
     * @param ImpersonateLoginRequest $request
     * @return JsonResponse
     */
    public function impersonate(
        ImpersonateLoginRequest $request
    ): JsonResponse
    {
        $token = $request->input('token');


        $impersonation = Impersonation::where('id', $token)
            ->where('used', false)
            ->where('expires_at', '>=', now())
            ->first();


        if (!$impersonation) {
            return response()->json(['message' => 'Invalid or expired token'], 403);
        }

        switchTenant($impersonation->target_tenant_id);

        $client = DB::table('oauth_clients')
            ->where('password_client', true)
            ->first();

        $user = User::where('email', $impersonation->email)->first() ?? User::first();

        if (!$user) {
            return response()->json(['message' => __('User not exist.')], 404);
        }

        if (!$client) {
            (new ClientRepository)->createPersonalAccessClient(
                $user->id,
                'Auto Personal Access Client',
                config('app.url')
            );
        }

        $existingClient = DB::table('oauth_clients')
            ->where('personal_access_client', true)
            ->first();


        if (!$existingClient) {
            $client = (new ClientRepository)->createPersonalAccessClient(
                null,
                'Auto Personal Access Client',
                config('app.url')
            );

            // Step 3: Register it in oauth_personal_access_clients
            DB::table('oauth_personal_access_clients')->insert([
                'client_id' => $client->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        else {
            $existsInPAC = DB::table('oauth_personal_access_clients')
                ->where('client_id', $existingClient->id)
                ->exists();

            if (!$existsInPAC) {
                DB::table('oauth_personal_access_clients')->insert([
                    'client_id' => $existingClient->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $tokenResult = $user->createToken('impersonation-token');
        $tokenResult->token->expires_at = now()->addMinutes(10);
        $tokenResult->token->save();

        $impersonation->update(['used' => true]);

        Cookie::queue(
            'X-PRDTK',
            $tokenResult->accessToken,
            1440,
            null,
            null,
            App::isProduction(),
            true
        );

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => $tokenResult->token->expires_at,
            'supplier_name' => $impersonation->supplier_name,
            'email' => $impersonation->email,
            'tenant_id' => $impersonation->target_tenant_id
        ]);
    }

}
