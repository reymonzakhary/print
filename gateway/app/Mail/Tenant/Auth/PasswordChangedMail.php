<?php

declare (strict_types=1);

namespace App\Mail\Tenant\Auth;

use Hyn\Tenancy\Contracts\Website;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class PasswordChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param string $newPasswordPlain The new changed password in PLAIN FORMAT
     * @param Authenticatable $authenticatable Any authenticatable entity that implement the interface (User, Member, etc.)
     * @param Website $tenant The current tenant object
     * @param string $actor who has performed this action (e.g. `system`, `user`, etc.). TODO Make an enum for this
     *
     * @return void
     */
    public function __construct(
        private readonly string $newPasswordPlain,
        private readonly Authenticatable $authenticatable,
        private readonly Website $tenant,
        private readonly string $actor
    ) {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->subject('Password')
            ->view('emails.tenant.auth.password_changed', [
                'newPasswordPlain' => $this->newPasswordPlain,
                'authenticatable' => $this->authenticatable,
                'tenant' => $this->tenant,
                'actor' => $this->actor,
            ]);
    }
}
