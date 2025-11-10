<?php

namespace App\Events\Tenant\Order\Media;

use App\Models\Tenant\Order;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeleteOrderMediaEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var int
     */
    public int $mediaId;

    /**
     * @var Order
     */
    public Order|Quotation $order;

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
        int            $mediaId,
        Order|Quotation $order,
        User           $user
    )
    {
        $this->mediaId = $mediaId;
        $this->order = $order;
        $this->user = $user;
    }

}
