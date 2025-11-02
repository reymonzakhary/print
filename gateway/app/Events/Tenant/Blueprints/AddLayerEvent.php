<?php

namespace App\Events\Tenant\Blueprints;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AddLayerEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public mixed  $item,
        public string $action,
        public int    $step,
        public array  $assets,
        public array  $row,
        public string $ref,
        public string $base,
        public string $output_path
    )
    {
    }
}
