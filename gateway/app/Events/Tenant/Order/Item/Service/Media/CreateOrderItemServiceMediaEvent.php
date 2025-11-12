<?php

namespace App\Events\Tenant\Order\Item\Service\Media;

use App\Models\Tenant\Item;
use App\Models\Tenant\Media;
use App\Models\Tenant\Order;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\Service;
use App\Models\Tenant\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateOrderItemServiceMediaEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Media
     */
    public ?object $media;

    /**
     * @var Service
     */
    public Service $service;

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
        ?object         $media,
        Service         $service,
        Order|Quotation $order,
        Item            $item,
        User            $user
    )
    {
        $this->media = $media;
        $this->service = $service;
        $this->order = $order;
        $this->item = $item;
        $this->user = $user;
    }
}
