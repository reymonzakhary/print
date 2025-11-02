<?php

namespace App\Listeners\Order;

use Illuminate\Support\Facades\Mail;

class MailQuotationToCustomerListener
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
        Mail::send(
            'emails.order_details',
            [
                'order' => $event->order
            ], function ($message) use ($event) {
            $message->to($event->order->orderedBy->email, $event->order->orderedBy->username)->subject('Offer Details');
        });
    }
}
