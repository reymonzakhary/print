<?php

namespace App\Events\Tenant\Blueprints;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SeparatePdfEvent
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
        public string $pages,
        public string $in,
        public string $out,
        public bool   $split = false
    )
    {
    }

}
