<?php

namespace App\Listeners\User;

use App\Models\Tenant\Addressable;
use App\Models\Traits\GenerateIdentifier;
use Illuminate\Foundation\Bus\Dispatchable;

class UnlinkUserAddressesListener
{
    use Dispatchable, GenerateIdentifier;

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        Addressable::where('identifier', $this->generateIdentifier($event->id, 'addresses'))->delete();
    }
}
