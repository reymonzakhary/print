<?php

namespace App\Listeners\User;

use App\Models\Tenant\Profile;
use App\Models\Traits\GenerateIdentifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DeleteProfileListener implements ShouldQueue
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
        Profile::where('identifier', $this->generateIdentifier($event->id, 'profile'))->delete();
    }
}
