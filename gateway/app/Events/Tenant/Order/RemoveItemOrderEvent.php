<?php

namespace App\Events\Tenant\Order;

use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use App\Models\Tenants\Quotation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RemoveItemOrderEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Order|Quotation $order
     * @param Item $item
     * @param mixed $user
     */
    public function __construct(
        readonly public Order|Quotation $order,
        readonly public Item $item,
        readonly public mixed $user
    ){}

}
