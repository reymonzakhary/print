<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\V2\Mgr\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\System\User\UserResource;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;

final class AccountController extends Controller
{
    /**
     * Returns a general info of the currently authenticated user
     *
     * @param Authenticatable $authenticatedUser
     *
     * @return UserResource
     */
    public function me(
        Authenticatable $authenticatedUser
    ): UserResource
    {
        return UserResource::make(
            $authenticatedUser->load(['profile', 'company.addresses.country'])
        )
            ->additional([
                'message' => __('Your account data has been retrieved successfully'),
                'status' => Response::HTTP_OK
            ]);
    }

    /**
     * Logs out of the currently authenticated user
     *
     * @param Authenticatable $authenticatedUser
     *
     * @return JsonResponse
     */
    public function logout(
        Authenticatable $authenticatedUser,
    ): JsonResponse
    {
        $authenticatedUser->tokens()->each(
            static function (Token $token): void {
                Passport::refreshToken()
                    ->newQuery()
                    ->where('access_token_id', $token->getAttribute('id'))
                    ->forceDelete();

                $token->forceDelete();
            }
        );

        cookie()->queue(cookie()->forget('X-PRDTK'));

        return response()->json([
            'message' => __('You have been logged out successfully'),
            'status' => Response::HTTP_OK
        ]);
    }
}
