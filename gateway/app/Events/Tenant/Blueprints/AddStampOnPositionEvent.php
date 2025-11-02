<?php

namespace App\Events\Tenant\Blueprints;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AddStampOnPositionEvent
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
        public string  $origin,
        public string  $stamp,
        public float   $x,
        public float   $y,
        public int     $page,
        public array   $row,
        public string  $ref,
        public string  $output_path,
        public string  $tmp_output_dir,
        public string  $replace,
        public ?string $search = null,
        public bool    $act = false
    )
    {
    }
}
