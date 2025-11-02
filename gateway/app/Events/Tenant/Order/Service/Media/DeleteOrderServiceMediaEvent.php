<?php

namespace App\Events\Tenant\Order\Service\Media;

use App\Models\Tenants\Order;
use App\Models\Tenants\Quotation;
use App\Models\Tenants\Service;
use App\Models\Tenants\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeleteOrderServiceMediaEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var int
     */
    public int $mediaId;

    /**
     * @var Service
     */
    public Service $service;

    /**
     * @var Order
     */
    public Order $order;

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
        int             $mediaId,
        Service         $service,
        Quotation|Order $order,
        User            $user
    )
    {
        $this->mediaId = $mediaId;
        $this->service = $service;
        $this->order = $order;
        $this->user = $user;
    }
}
