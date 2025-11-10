<?php

declare(strict_types=1);

namespace App\Mail\Tenant\Order;

use App\Models\Tenant\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public readonly Order $order,
    ) {

    }


    public function build()
    {
        $pdf = Pdf::loadView('pdf.order.order_confirmation' , [
            'order' => $this->order->load('orderedBy')
        ]);

        return
            $this->view('emails.tenant.order.order_confirmation')->subject('Your Order Has Been Updated')
            ->attachData($pdf->output(), 'order_confirmation#'. $this->order->order_nr . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
