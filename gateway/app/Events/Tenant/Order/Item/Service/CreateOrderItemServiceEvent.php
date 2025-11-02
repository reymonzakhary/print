<?php

namespace App\Events\Tenant\Order\Item\Service;

use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use App\Models\Tenants\Quotation;
use App\Models\Tenants\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateOrderItemServiceEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var array
     */
    public array $serviceIds;

    /**
     * @var Item
     */
    public Item $item;

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
        array           $serviceIds,
        Order|Quotation $order,
        Item            $item,
        User            $user
    )
    {
        $this->serviceIds = $serviceIds;
        $this->order = $order;
        $this->item = $item;
        $this->user = $user;
    }
}
