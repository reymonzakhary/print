<?php

namespace App\Http\Controllers\Tenant\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Tenants\User;
use App\Utilities\ProxyRequest;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{
    /**
     *
     * @var ProxyRequest
     */
    protected ProxyRequest $proxy;

    /**
     * AuthenticationController constructor.
     * @param ProxyRequest $proxy
     */
    public function __construct(
        ProxyRequest $proxy
    )
    {
        $this->proxy = $proxy;
    }

    /**
     * @param LoginRequest $request
     * @return ResponseFactory|Response
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        abort_unless($user, 404, 'This combination does not exists.');
        abort_unless(
            Hash::check($request->password, $user->password),
            404,
            'This combination does not exists.'
        );

        $resp = $this->proxy
            ->grantPasswordToken($request->email, $request->password);

        return response([
            'data' => [
                "token_type" => "Bearer",
                "expires_in" => $resp->expires_in,
                "access_token" => $resp->access_token,
            ],
            'message' => 'You have been logged in',
            'status' => 200
        ], 200);
    }

    /**
     * @return ResponseFactory|Response
     */
    public function refreshToken()
    {
        $resp = $this->proxy->refreshAccessToken();

        return response([
            'data' => [
                "token_type" => "Bearer",
                "expires_in" => $resp->expires_in,
                "access_token" => $resp->access_token,
            ],
            'message' => 'Token has been refreshed.',
        ], 200);
    }
}
