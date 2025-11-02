<?php

namespace App\Events\Tenant\Products;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateProductCombinationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public string  $category,
        public array   $request,
        public string  $tenant,
        public string  $tenant_name,
        public ?string $host_id
    )
    {
    }
}
