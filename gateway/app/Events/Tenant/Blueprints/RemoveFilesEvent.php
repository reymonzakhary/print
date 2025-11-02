<?php

namespace App\Events\Tenant\Blueprints;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RemoveFilesEvent
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
        public string $disk,
        public array  $files
    )
    {
    }
}
