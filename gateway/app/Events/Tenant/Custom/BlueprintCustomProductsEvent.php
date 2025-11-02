<?php

namespace App\Events\Tenant\Custom;

use App\Models\Tenants\Cart;
use App\Models\Tenants\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BlueprintCustomProductsEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public mixed  $request,
        public Cart   $cart,
        public User   $user,
        public string $uuid,
        public string $hostId,
        public string $host_fqdn
    )
    {
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('cart');
    }
}
