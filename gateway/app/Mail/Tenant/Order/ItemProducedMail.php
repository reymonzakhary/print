<?php

declare(strict_types=1);

namespace App\Mail\Tenant\Order;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class ItemProducedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public readonly string $companyName,
        public readonly int $orderId,
        public readonly string $producerDomain,
    ) {
        // TODO : Investigate if we need to pass any parameters to the constructor
    }


    public function build()
    {
        return $this->view('emails.tenant.order.item-produced');
    }
}
