<?php

namespace App\Events\Tenant\Order;

use App\Models\Tenant\Quotation;
use App\Models\Tenant\Order;
use App\Models\Tenant\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeleteQuotationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        readonly public Quotation|Order $order,
        readonly public  mixed $user
    ) {}

}
