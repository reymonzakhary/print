<?php

namespace App\Events\Tenant\Order\Item\Address;

use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use App\Models\Tenants\Quotation;
use App\Models\Tenants\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateOrderItemAddressEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var int
     */
    public int $addressId;

    /**
     * @var Order|Quotation
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
        int             $addressId,
        Order|Quotation $order,
        Item            $item,
        User            $user
    )
    {
        $this->addressId = $addressId;
        $this->order = $order;
        $this->item = $item;
        $this->user = $user;
    }
}
