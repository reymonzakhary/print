<?php

namespace App\Events\Tenant\Order;

use App\Models\Tenant\Item;
use App\Models\Tenant\Order;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateItemOrderEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Order|Quotation $order
     * @param Item            $item
     * @param array           $original
     * @param array           $attributes
     * @param User            $user
     */
    public function __construct(
        readonly public Order|Quotation $order,
        public Item            $item,
        readonly public array           $original,
        readonly public array           $attributes,
        readonly public mixed           $user
    )
    {
        $this->item = Item::where('id', $item->id)->first();
    }

}
