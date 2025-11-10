<?php

namespace App\Events\Tenant\Order\Service\Media;

use App\Models\Tenant\Media;
use App\Models\Tenant\Order;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\Service;
use App\Models\Tenant\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateOrderServiceMediaEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Media
     */
    public Media $media;

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
        Media           $media,
        Service         $service,
        Quotation|Order $order,
        User            $user
    )
    {
        $this->media = $media;
        $this->service = $service;
        $this->order = $order;
        $this->user = $user;
    }
}
