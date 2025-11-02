<?php

namespace App\Events\Tenant\Blueprints;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AddLayerOnPositionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public mixed   $item,
        public string  $action,
        public int     $step,
        public string  $tmp_dir,
        public int     $page,
        public string  $path,
        public string  $disk,
        public string  $position,
        public string  $output_path,
        public string  $layer,
        public ?array  $row,
        public ?string $ref
    )
    {
    }

}
