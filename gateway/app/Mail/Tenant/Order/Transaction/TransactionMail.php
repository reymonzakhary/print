<?php

declare(strict_types=1);

namespace App\Mail\Tenant\Order\Transaction;

use App\Models\Tenants\MailQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TransactionMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param MailQueue $mailQueue
     * @param array<int, Attachment> $attachmentList
     */
    public function __construct(
        private readonly MailQueue $mailQueue,
        private readonly array     $attachmentList,
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            $this->mailQueue->getAttribute('from'),
            explode(',', $this->mailQueue->getAttribute('to')),
            $this->mailQueue->getAttribute('cc') ? explode(',', $this->mailQueue->getAttribute('cc')) : [],
            $this->mailQueue->getAttribute('bcc') ? explode(',', $this->mailQueue->getAttribute('bcc')) : [],
            subject: $this->mailQueue->getAttribute('subject'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content('emails.tenant.order.transaction', with: [
            'mailQueue' => $this->mailQueue,
        ]);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return $this->attachmentList;
    }
}
