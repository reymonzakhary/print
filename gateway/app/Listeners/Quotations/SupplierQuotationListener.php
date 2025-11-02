<?php

namespace App\Listeners\Quotations;

use App\Mail\Quotations\SupplierQuotationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SupplierQuotationListener // implements ShouldQueue
{
    /**
     * @param $event
     * @return void
     */
    public function handle(
        $event
    ): void
    {
        $requester_namespace = "\\{$event->contract->requester_type}";
        $contractor = $requester_namespace::where('id', $event->contract->requester_id)->first();

        // @ todo send push notification to supplier.
        Mail::alwaysFrom($contractor?->email??config('mail.from')['address'], $contractor?->name);
        Mail::to($event->contract->supplier->custom_fields->pick('email'))->send(new SupplierQuotationMail($contractor, $event->quotation));
    }
}
