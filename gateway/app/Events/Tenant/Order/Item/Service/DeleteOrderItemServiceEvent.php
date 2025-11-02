<?php

namespace App\Events\Tenant\Order\Item\Service;

use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use App\Models\Tenants\Quotation;
use App\Models\Tenants\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeleteOrderItemServiceEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var int
     */
    public int $serviceId;

    /**
     * @var Order
     */
    public Order|Quotation $order;

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
        int             $serviceId,
        Order|Quotation $order,
        Item            $item,
        User            $user
    )
    {
        $this->serviceId = $serviceId;
        $this->order = $order;
        $this->item = $item;
        $this->user = $user;
    }
}
