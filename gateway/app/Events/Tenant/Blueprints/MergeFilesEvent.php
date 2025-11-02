<?php

namespace App\Events\Tenant\Blueprints;

use App\Models\Tenants\QueueItem;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MergeFilesEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public mixed     $item,
        public string    $action,
        public int       $step,
        public QueueItem $queue,
        public string    $in,
        public string    $out
    )
    {
    }
}
