<?php

namespace App\Events\Tenant\Products;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeleteProductCombinationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public string   $category,
        public array    $boops,
        public string   $tenant,
        public string   $tenant_name,
        public ?string  $host_id = null
    ) {}
}
