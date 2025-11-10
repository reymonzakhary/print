<?php

namespace App\Http\Controllers\Tenant\Mgr\Auth;

use App\Events\SendPasswordEvent;
use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Models\Website;
use Carbon\Carbon;
use Hyn\Tenancy\Environment;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group Tenant Auth
 */
class VerificationApiController extends Controller
{
    use VerifiesEmails;

    /**
     * Verify E-mail Address
     *
     * Mark the authenticated user's email address as verified.
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     *
     * @urlParam id integer required The ID of the User.
     *
     * @response 200
     * {
     *     "message": "Your Email has been verified!",
     *     "status": 200
     * }
     *
     * @response 422
     * {
     *     "message": "User already have verified email!",
     *     "status": 422
     * }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verify(
        Request $request
    )
    {
        $env = app(Environment::class);
        $site = $request->get('tenant');
        $env = $env->tenant($site);
        $user = User::with('profile')->findOrFail((int)$request->route('id'));
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                "message" => 'User already have verified email!',
                "status" => Response::HTTP_UNPROCESSABLE_ENTITY
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ((bool)$request->get('gp')) {
            $password = random_password(15) . rand();
            event(new SendPasswordEvent($user->email, $password, $request->get('hostname')->fqdn, $user));
            $user->password = $password;
        }
        $user->update(['email_verified_at' => Carbon::now()->format('Y-m-d g:i:s')]);

        return response()->json([
            'message' => __('Your Email has been verified!'),
            'status' => Response::HTTP_OK,
        ]);
    }

    /**
     *
     * Resend the email verification notification.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resend(
        Request $request
    )
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                "message" => 'User already have verified email!',
                "status" => Response::HTTP_UNPROCESSABLE_ENTITY
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request->user()->sendEmailVerificationNotification();
        return response()->json([
            "message" => 'The notification has been resubmitted',
            "status" => Response::HTTP_OK
        ], Response::HTTP_OK);
    }
}
