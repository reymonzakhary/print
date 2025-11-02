<?php

declare(strict_types=1);

namespace App\Notifications\Tenant\Email\User;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\URL;

final class VerifyEmailNotification extends VerifyEmailBase implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(
        public readonly string $uuid,
        public readonly bool $regeneratePassword
    ) {
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param mixed $notifiable
     *
     * @return string
     */
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'users-email-verify', Carbon::now()->addMinutes(60), [
                'user' => $notifiable->getKey(),
                'tenant' => $this->uuid,
                'gp' => $this->regeneratePassword
            ]
        );
    }
}
