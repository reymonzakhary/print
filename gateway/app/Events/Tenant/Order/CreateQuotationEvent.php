<?php

namespace App\Events\Tenant\Order;

use App\Models\Quotation;
use App\Models\Tenant\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateQuotationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Quotation|Order $order
     * @param mixed  $user
     */
    public function __construct(
        readonly public Quotation|Order $order,
        readonly public mixed  $user
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        if (!$this->order->type) {
            return new PrivateChannel('quotations');
        }
    }
}
