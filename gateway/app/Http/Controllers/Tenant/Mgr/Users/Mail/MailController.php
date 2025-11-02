<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Users\Mail;

use App\Events\Tenant\PasswordChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\Tenants\User;
use Hyn\Tenancy\Environment;
use Illuminate\Contracts\View\View;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;

final class MailController extends Controller
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
        private readonly Environment $environment,
    ) {
    }

    /**
     * Mark the user as verified
     *
     * @param Request $request
     * @param User $user
     *
     * @return View
     *
     * @throws Throwable
     */
    public function verify(
        Request $request,
        User $user
    ): View
    {
        if ($user->hasVerifiedEmail()) {
            return view('tenant.auth.email-is-already-verified', [
                'supplier_data' => tenantCustomFields()->toArray(),
                'logo' => tenantLogoUrl(),
                'authenticatable' => $user,
            ]);
        }

        if ($passwordGenerated = $request->boolean('gp')) {
            [$currentPasswordHashed, $newPasswordPlain] = [
                $user->getAttribute('password'),
                random_password(15) . rand() # TODO create a password generator class
            ];

            $user->updateOrFail(['password' => $newPasswordPlain]);

            $this->dispatcher->dispatch(
                new PasswordChangedEvent(
                    newPasswordPlain: $newPasswordPlain,
                    oldPasswordHashed: $currentPasswordHashed,
                    authenticatable: $user,
                    tenant: $this->environment->tenant(),
                    actor: 'system',
                    callerContext: __METHOD__,
                    shouldNewPasswordMasked: false
                )
            );
        }

        $user->updateOrFail(['email_verified_at' => Carbon::now()->format('Y-m-d g:i:s')]);

        return view('tenant.auth.email-has-verified-successfully', [
            'supplier_data' => tenantCustomFields()->toArray(),
            'logo' => tenantLogoUrl(),
            'authenticatable' => $user,
            'passwordGenerated' => $passwordGenerated
        ]);
    }
}
