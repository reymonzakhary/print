<?php

namespace App\Http\Controllers\Tenant\Mgr\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetEmailVerificationRequest;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Http\Requests\Auth\PasswordUpdateRequest;
use App\Mail\Tenant\Auth\UpdatePasswordMail;
use App\Models\Tenants\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Auth
 */
class PasswordResetController extends Controller
{

    /**
     * Forget Password
     *
     * send otp to user's mail to reset password
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     *
     * @response 200
     * {
     *    "message": "Password recovery mail has been sent successfully.",
     *    "status": 200
     * }
     *
     * @response 404
     * {
     *    "message": "Page not found.",
     *    "status": 404
     * }
     *
     * @response 422
     * {
     *  "message": "The selected email is invalid. (and 1 more error)",
     *      "errors": {
     *          "email": [
     *               "The selected email is invalid."
     *          ],
     *          "user": [
     *               "The user field is required."
     *          ]
     *      }
     * }
     *
     * @bodyParam email string required The email of user. Example: test@gmail.com
     * @bodyParam user Model required system select the user from his Email
     *
     * @param PasswordResetRequest $request
     * @return JsonResponse
     */
    public function forget(
        PasswordResetRequest $request
    ): JsonResponse
    {
        if ($request->validated('user')?->email_verified_at){
            $salt = Str::random(4);

            $token = rand(100000, 999999);

            $verify = Hash::make($token.$salt);

            DB::table('password_resets')->updateOrInsert([
                'email' => $request->email
                ],[
                    'email' => $request->email,
                    'token' => $verify,
                    'created_at' => now()
                ]
            );
            // send password update email
            Mail::to($request->validated('user')->email)->send(
                new UpdatePasswordMail($token)
            );

            return response()->json([
                'message' => __('If that email address is associated with an account, you’ll receive a password reset email shortly.'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK)->withCookie(cookie(
                'X-PRDPVR',
                $salt,
                140, // 1 days
                null,
                null,
                config('app.env') === 'production',
                true // httponly
            ));
        }

        return response()->json([
            'message' => __('Page not found.'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }

    /**
     * Verify reset password code
     *
     * verify user token from database
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     *
     * @bodyParam email string required The email of user. Example: test@gmail.com
     * @bodyParam token string required the otp sent on user"s mail Example: 123456
     *
     * @response 202
     * {
     *      "message": "Successfully verified",
     *      "status": 202,
     * }
     *
     * @response 403
     * {
     *      "message": "Forbidden Request."
     *      "status": 403,
     * }
     *
     * @param PasswordResetEmailVerificationRequest $request
     * @return JsonResponse
     */
    public function verify(
        PasswordResetEmailVerificationRequest $request
    ): JsonResponse
    {
        $salt = $request->cookie('X-PRDPVR');
        $email = $request->get('email');
        $token = $request->get('token');

        $db_token= DB::table('password_resets')
            ->where('email', $email)
            ->first();

        if(Hash::check($token.$salt, $db_token?->token)) {
            $su = Str::random(10);

            $url = $db_token?->email.env('APP_KEY');
            $token = Hash::make($su.$url);

            DB::table('password_resets')
                ->where([
                    ['token', $db_token?->token],
                    ['email', $db_token?->email]
                ])->update(['token' => $token]);

            return response()->json([
                'message' => __('Successfully verified'),
                'status' => Response::HTTP_ACCEPTED,
            ], Response::HTTP_ACCEPTED)
            ->withCookie(cookie(
                'X-PRDPVRD',
                $su,
                140, // 1 days
                null,
                null,
                false,
                true // httponly
            ))->withCookie(cookie()->forget('X-PRDPVR'));
        }

        return response()->json([
            'message' => __('Forbidden Request.'),
            'status' => Response::HTTP_FORBIDDEN
        ], Response::HTTP_FORBIDDEN);

    }

    /**
     * Reset Password
     *
     * user can update his password
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     *
     * @bodyParam email string required The email of user. Example: test@gmail.com
     * @bodyParam password string required The password of user. Example: password
     * @bodyParam password_confirmation string required The confirmation password of user. Example: password
     *
     * @response 401
     * {
     *      "message": "Unauthorized.",
     *      "status": 401
     * }
     *
     * @response 403
     * {
     *      "message": "Verification failed signature mismatch.",
     *      "status": 403
     * }
     *
     * @response 200
     * {
     *      "message": "Password has been reset successfully.",
     *      "status": 200
     * }
     *
     * @response 403
     * {
     *      "message": "Failed to reset password",
     *      "status": 403
     * }
     *
     * @response 422
     * {
     *      "message": "The email field is required. (and 2 more errors)",
     *       "errors": {
     *           "email": [
     *               "The email field is required."
     *           ],
     *           "password": [
     *               "The password field is required."
     *           ],
     *           "password_confirmation": [
     *               "The password confirmation field is required."
     *           ]
     *       }
     * }
     *
     * @param PasswordUpdateRequest $request
     * @return JsonResponse
     */
    public function reset(
        PasswordUpdateRequest $request
    )
    {
        $su = request()->cookie('X-PRDPVRD');
        $email = $request->get('email');

        $url = $email.env('APP_KEY');
        $token = $su.$url;

        cookie()->queue(
            cookie()->forget('X-PRDPVRD')
        );

        $reset_code = DB::table('password_resets')
            ->where('email', $email)
            ->first();

        if (!$reset_code){
            return response()->json([
                'message' => __('Unauthorized.'),
                'status' => Response::HTTP_UNAUTHORIZED
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!Hash::check($token, $reset_code->token)){
            return response()->json([
                'message' => __('Verification failed signature mismatch.'),
                'status' => Response::HTTP_FORBIDDEN
            ], Response::HTTP_FORBIDDEN);
        }

        if (User::where('email', $email)->update(['password' => bcrypt($request->password)])){
            DB::table('password_resets')
                ->where('email', $email)
                ->delete();

            return response()->json([
                'message' => __('Password has been reset successfully.'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __('Failed to reset password'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);

    }

}
