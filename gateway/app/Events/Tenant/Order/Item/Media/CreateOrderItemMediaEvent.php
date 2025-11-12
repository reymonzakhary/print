<?php

namespace App\Events\Tenant\Order\Item\Media;

use App\Models\Tenant\Item;
use App\Models\Tenant\Media;
use App\Models\Tenant\Order;
use App\Models\Tenant\Quotation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateOrderItemMediaEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        readonly  public array|Media $media,
        readonly  public Quotation|Order $order,
        readonly  public Item  $item,
        readonly  public mixed $user
    )
    {
    }
}
