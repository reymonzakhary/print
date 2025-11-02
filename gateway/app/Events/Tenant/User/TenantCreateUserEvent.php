<?php

namespace App\Events\Tenant\User;

use App\Models\Tenants\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class TenantCreateUserEvent
 * @package App\Events\Tenant\User
 */
class TenantCreateUserEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var User user model */

    public User $user;

    /**
     * Create a new event instance.
     *
     * @param User $user
     */
    public function __construct(
        User $user
    )
    {
        $this->user = $user;
    }
}
