<?php

declare(strict_types=1);

namespace App\Events\Tenant\Order\Service;

use App\Models\Tenant\Order;
use App\Models\Tenant\Quotation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class CreateOrderServiceEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public readonly int $serviceId,
        public readonly Quotation|Order $order,
        public readonly Authenticatable $user
    ) {
    }
}
