<?php

namespace App\Events\Tenant\Order;

use App\Models\Tenants\Order;
use App\Models\Tenants\Quotation;
use App\Models\Tenants\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class UpdateQuotationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Order|Quotation $order
     * @param array           $original
     * @param array           $attributes
     * @param User            $user
     */
    public function __construct(
        public Order|Quotation $order,
        public array           $original,
        public array           $attributes,
        readonly public mixed           $user
    )
    {
        if (optional($original)['price'] && !is_int($original['price'])) {
            $original['price'] = $original['price']->amount();
        }

        if (optional($attributes)['price'] && !is_int($attributes['price'])) {
            $attributes['price'] = $attributes['price']->format();
        }

        $this->order = Order::where('id', $order->id)->first();
        $this->original = $original;
        $this->attributes = $attributes;
    }
}
