<?php

namespace App\Events\PrintingMethods;

use App\Models\Tenants\PrintingMethod;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreatePrintingMethodEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public PrintingMethod|\Illuminate\Database\Eloquent\Model $printingMethod,
        public string         $iso
    )
    {
    }

}
