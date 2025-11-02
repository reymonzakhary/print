<?php

namespace App\Events\DeliveryDays;

use App\Models\Tenants\DeliveryDay;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateDeliveryDayEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public DeliveryDay $deliveryDay,
        public string      $iso
    )
    {
    }

}
