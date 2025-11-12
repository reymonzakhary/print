<?php

namespace App\Events\Tenant\Order\Notification;

use App\Models\Tenant\Order;
use App\Models\Tenant\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderNotificationEmailEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Order
     */
    public Order $order;

    /**
     * @var User
     */
    public User $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        Order $order,
        User  $user
    )
    {
        $this->order = $order;
        $this->user = $user;
    }
}
