<?php

namespace App\Events\Tenant\Order\Item;

use App\Models\Quotation;
use App\Models\Tenant\Item;
use App\Models\Tenant\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProduceItemEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Order|Quotation $order
     * @param mixed  $user
     */
    public function __construct(
        readonly public Quotation|Order $order,
        readonly public Item $item,
        readonly public int $producerOrderId,
        readonly public mixed  $user
    ){}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        if ($this->order->type) {
            return new PrivateChannel('orders');
        }
    }
}
