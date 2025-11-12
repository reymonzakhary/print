<?php

namespace App\Events\Tenant\Order\Item\Service\Media;

use App\Models\Tenant\Item;
use App\Models\Tenant\Order;
use App\Models\Tenant\Service;
use App\Models\Tenant\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeleteOrderItemServiceMediaEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var int
     */
    public int $mediaId;

    /**
     * @var Service
     */
    public Service $service;

    /**
     * @var Order
     */
    public Order $order;

    /**
     * @var Item
     */
    public Item $item;

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
        int     $mediaId,
        Service $service,
        Order   $order,
        Item    $item,
        User    $user
    )
    {
        $this->mediaId = $mediaId;
        $this->service = $service;
        $this->order = $order;
        $this->item = $item;
        $this->user = $user;
    }
}
