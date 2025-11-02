<?php

namespace App\Listeners\Tenant\Report;

use App\Models\Tenants\Report;

class CreateReportListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        Report::create([
            'title' => $event->title,
            'activity' => $event->activity,
            'product' => $event->product,
            'user_id' => $event->user->id,
            'type' => $event->type
        ]);
    }
}
