<?php

namespace App\Events\Tenant\Custom;

use App\Models\Tenants\CartVariation;
use App\Models\Tenants\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BlueprintCustomProductEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public mixed         $request,
        public CartVariation $cartVar,
        public string        $cart_id,
        public User          $user,
        public string        $uuid,
        public string        $hostId,
        public string        $host_fqdn
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
