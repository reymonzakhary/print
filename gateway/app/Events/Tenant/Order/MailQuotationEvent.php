<?php

namespace App\Events\Tenant\Order;

use App\Models\Tenant\MailQueue;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MailQuotationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param MailQueue $mail
     * @param string $uuid
     * @param string $domain
     */
    public function __construct(
        readonly public MailQueue $mail,
        readonly public string $uuid,
        readonly public string $domain
    ){}
}
