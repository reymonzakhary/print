<?php

declare(strict_types=1);

namespace App\Events\Tenant\Order\Service;

use App\Models\Tenants\Order;
use App\Models\Tenants\Quotation;
use App\Models\Tenants\Service;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class UpdateOrderServiceEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public readonly Service $service,
        public readonly Quotation|Order $order,
        public readonly Authenticatable $user
    ) {
    }
}
