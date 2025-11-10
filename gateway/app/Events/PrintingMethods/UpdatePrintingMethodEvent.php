<?php

namespace App\Events\PrintingMethods;

use App\Models\Tenant\PrintingMethod;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdatePrintingMethodEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public PrintingMethod $printingMethod,
        public string         $iso
    )
    {
    }

}
