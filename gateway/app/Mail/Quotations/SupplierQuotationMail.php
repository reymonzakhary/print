<?php

namespace App\Mail\Quotations;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupplierQuotationMail extends Mailable
{
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public $contract,
        public $quotation,
    ) {}

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.new-quotation');
    }
}
