<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\V2\Mgr\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetEmailVerificationRequest;
use App\Http\Requests\Auth\PasswordUpdateRequest;
use App\Http\Requests\System\Auth\PasswordResetRequest;
use App\Mail\Tenant\Auth\UpdatePasswordMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

final class PasswordResetController extends Controller
{
    /**
     * Initiate the password reset process and send an OTP to the user's email
     *
     * @param PasswordResetRequest $request
     *
     * @return JsonResponse
     */
    public function forget(
        PasswordResetRequest $request
    ): JsonResponse
    {
        $salt = Str::random(4);

        $token = rand(100000, 999999);

        $verify = Hash::make($token . $salt);

        DB::table('password_resets')->updateOrInsert([
            'email' => $request->email
        ], [
                'email' => $request->email,
                'token' => $verify,
                'created_at' => now()
            ]
        );

        Mail::to(
            $request->input('user')->getAttribute('email')
        )->send(
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
            App::isProduction(),
            true // httponly
        ));
    }

    /**
     * Verification of the OTP
     *
     * @param PasswordResetEmailVerificationRequest $request
     *
     * @return JsonResponse
     */
    public function verify(
        PasswordResetEmailVerificationRequest $request
    ): JsonResponse
    {
        $salt = $request->cookie('X-PRDPVR');
        $email = $request->get('email');
        $token = $request->get('token');

        $db_token = DB::table('password_resets')
            ->where('email', $email)
            ->first();

        if (Hash::check($token . $salt, $db_token?->token)) {
            $su = Str::random(10);

            $url = $db_token?->email . env('APP_KEY');
            $token = Hash::make($su . $url);

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
                    App::isProduction(),
                    true // httponly
                ))->withCookie(cookie()->forget('X-PRDPVR'));
        }

        return response()->json([
            'message' => __('Forbidden Request.'),
            'status' => Response::HTTP_FORBIDDEN
        ], Response::HTTP_FORBIDDEN);

    }

    /**
     * Changing of the user password
     *
     * @param PasswordUpdateRequest $request
     *
     * @return JsonResponse
     */
    public function reset(
        PasswordUpdateRequest $request
    ): JsonResponse
    {
        $su = request()->cookie('X-PRDPVRD');
        $email = $request->get('email');

        $url = $email . env('APP_KEY');
        $token = $su . $url;

        cookie()->queue(
            cookie()->forget('X-PRDPVRD')
        );

        $reset_code = DB::table('password_resets')
            ->where('email', $email)
            ->first();

        if (!$reset_code) {
            return response()->json([
                'message' => __('Unauthorized.'),
                'status' => Response::HTTP_UNAUTHORIZED
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!Hash::check($token, $reset_code->token)) {
            return response()->json([
                'message' => __('Verification failed signature mismatch.'),
                'status' => Response::HTTP_FORBIDDEN
            ], Response::HTTP_FORBIDDEN);
        }

        if (User::where('email', $email)->update(['password' => bcrypt($request->password)])) {
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
