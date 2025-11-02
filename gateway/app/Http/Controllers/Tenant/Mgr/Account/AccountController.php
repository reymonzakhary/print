<?php

namespace App\Http\Controllers\Tenant\Mgr\Account;

use App\Facades\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\Users\UserResource;
use App\Plugins\Moneys;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Account
 */
class AccountController extends Controller
{
    protected array $hide = [];

    /**
     * Me
     *
     * get account details
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     *      "data": {
     *        "id": 1,
     *        "owner": true,
     *        "email": "test@gmail.com",
     *        "email_verified_at": "2024-05-02T01:11:50.000000Z",
     *        "created_at": "2024-05-01T11:40:06.000000Z",
     *        "updated_at": "2024-05-02T13:11:50.000000Z",
     *        "ctx": [],
     *        "profile": [],
     *        "permission": [],
     *        "roles": [],
     *        "companies": [],
     *        "addresses": [],
     *      },
     *      "meta": {
     *
     *      }
     *      "tenant_id": "6f24d2b6-2ab5-4c30-844c-bf1f8af1f16a",
     *      "status": 200,
     *      "message": null
     * }
     *
     * @return UserResource
     */
    public function me(): UserResource
    {
//        $this->authorize('auth-access');
        return UserResource::make(request()->user()->load(
            'roles', 'roles.permissions', 'profile', 'companies', 'addresses', 'teams'
        ))->hide(
            $this->hide
        )->additional([
            'meta' => [
                'modules' => request()->hostname->configure,
                'settings' => [
                    'language' => Str::lower(Settings::from('user')->managerLanguage('nl')?->value),
                    'currency' => (new Moneys())->getCurrency(),
                    'currency_key' => (new Moneys())->getCurrencyIso(),
                    'automation' => (bool)Settings::from('user')->managerLanguage(false)?->value,
                ],
            ],
            "tenant_id" => request()->tenant->uuid,
            "tenant_name" => request()->fqdn,
            'status' => Response::HTTP_OK,
            'message' => null
        ]);
    }

    /**
     * @return ResponseFactory|Response
     */
    /**
     * Logout
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     *      "message":"You have been successfully logged out"
     * }
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory|Application|ResponseFactory
     */
    public function logout(): \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory|Application|ResponseFactory
    {
        foreach (request()->user()->tokens as $token) {
            Passport::refreshToken()->where('access_token_id', $token->id)->delete();
            $token->delete();
        }
        // remove the httponly cookie
        cookie()->queue(cookie()->forget('X-PRDTK'));

        return response([
            'message' => __('You have been successfully logged out'),
        ], 200);
    }
}
