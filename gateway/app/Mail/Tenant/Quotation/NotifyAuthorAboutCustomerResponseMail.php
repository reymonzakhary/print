<?php

declare(strict_types=1);

namespace App\Mail\Tenant\Quotation;

use App\Facades\Settings;
use App\Models\Tenant\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifyAuthorAboutCustomerResponseMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        private readonly Quotation $quotation,
        private readonly string    $targetEmail,
        private readonly bool      $isAcceptation
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: Settings::mailSmtpFrom()->value,
            to: [$this->targetEmail],
            cc: $this->getCcList(),
            subject: $this->getSubject(),
        );
    }

    private function getCcList(): array
    {
        return array_filter([
            Settings::orderEmailCc()->value
        ]);
    }

    private function getSubject(): string
    {
        return __("Quotation has been updated.");
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content('emails.tenant.quotation.customer-responded-to-offer', with: [
            'quotation' => $this->quotation,
            'isAcceptation' => $this->isAcceptation
        ]);
    }
}
