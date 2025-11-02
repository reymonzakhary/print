<?php

namespace App\Events\Tenant\Order\Item;

use App\Models\Quotation;
use App\Models\Tenants\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChangeItemStatusEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Order|Quotation $order
     * @param int  $item_id
     */
    public function __construct(
        readonly public Order|Quotation $order, // Order On The Supplier Side
        readonly public int $item_id,
        readonly public int $order_id, // Order On The Reseller Side
        readonly public array $resellerTenant,
        readonly public bool $is_external = true,
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
