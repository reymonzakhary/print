<?php

namespace App\Http\Controllers\System\Mgr\Account;

use App\Http\Controllers\Controller;
use Laravel\Passport\Passport;
use function request;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function me()
    {
        return request()->user()->load('profile');
    }

    /**
     * @return ResponseFactory|Response
     */
    public function logout()
    {
        foreach (request()->user()->tokens as $token) {
            Passport::refreshToken()->where('access_token_id', $token->id)->delete();
            $token->delete();
        }
        // remove the httponly cookie
        cookie()->queue(cookie()->forget('X-PRDTK'));

        return response([
            'message' => 'You have been successfully logged out',
        ], 200);
    }
}
