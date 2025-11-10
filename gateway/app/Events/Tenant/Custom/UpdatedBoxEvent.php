<?php

namespace App\Events\Tenant\Custom;

use App\Models\Tenant\Box;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdatedBoxEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Box $box, public ?array $transition = [])
    {
    }

}
