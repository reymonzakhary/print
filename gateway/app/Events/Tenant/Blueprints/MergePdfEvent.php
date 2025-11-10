<?php

namespace App\Events\Tenant\Blueprints;

use App\Models\Tenant\QueueItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MergePdfEvent
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
        public string    $disk,
        public string    $directory,
        public string    $destinations,
        public string    $filename,
        public bool      $separate,
        public string    $tmp_output_dir
    )
    {}
}
