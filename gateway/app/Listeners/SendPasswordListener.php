<?php

namespace App\Listeners;

use App\Events\SendPasswordEvent;
use App\Mail\PasswordMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendPasswordListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param SendPasswordEvent $event
     * @return void
     */
    public function handle(SendPasswordEvent $event)
    {
        Mail::to($event->email)->send(
            new PasswordMail($event->password, $event->email, $event->site, $event->user)
        );
    }
}
