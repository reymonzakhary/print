<?php

declare(strict_types=1);

namespace App\Events\Tenant;

use Hyn\Tenancy\Contracts\Website;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * This is an after-action event that is fired whenever the password for an authenticatable entity (user, member, etc.) is changed.
 */
final class PasswordChangedEvent
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance
     *
     * @param string $newPasswordPlain The new changed password in PLAIN FORMAT
     * @param string $oldPasswordHashed The old password in hashed format
     * @param Authenticatable $authenticatable Any authenticatable entity that implement the interface (User, Member, etc.)
     * @param Website $tenant The current tenant object
     * @param string $actor who has performed this action (e.g. `system`, `user`, etc.). TODO Make an enum for this
     * @param string $callerContext The context from which the event has been fired (__CLASS_, __METHOD__, etc.)
     * @param bool $shouldNewPasswordMasked Should the new-password-plain be hided or masked in any notification or email
     *
     * @return void
     */
    public function __construct(
        public readonly string $newPasswordPlain,
        public readonly string $oldPasswordHashed,
        public readonly Authenticatable $authenticatable,
        public readonly Website $tenant,
        public readonly string $actor,
        public readonly string $callerContext,
        public readonly bool $shouldNewPasswordMasked = true,
    ) {
    }
}
