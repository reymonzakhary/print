<?php

namespace App\Events\Tenant\Order\Item\Media;

use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use App\Models\Tenants\Quotation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeleteOrderItemMediaEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        readonly  public int             $mediaId,
        readonly  public Order|Quotation $order,
        readonly  public Item            $item,
        readonly  public mixed           $user
    )
    {
    }
}
