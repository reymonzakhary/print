<?php

namespace App\Http\Controllers\System\Mgr\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
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
     * @param User $user
     * @return ResponseFactory|Response
     */
    public function login(
        LoginRequest $request
    )
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response([
                'message' => __('auth.combination_does_not_exists'),
                'status' => 404
            ], 404);
        }
        if (!Hash::check($request->password, $user->password)) {
            return response([
                'message' => __('auth.combination_does_not_exists'),
                'status' => 404
            ], 404);
        }
        $resp = $this->proxy
            ->grantPasswordToken($request->email, $request->password);

        return response([
            'data' => $resp,
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
            'token' => $resp->access_token,
            'expiresIn' => $resp->expires_in,
            'message' => __('auth.token_refreshed'),
        ], 200);
    }
}
