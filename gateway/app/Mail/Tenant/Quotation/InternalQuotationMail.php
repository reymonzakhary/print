<?php

declare(strict_types=1);

namespace App\Mail\Tenant\Quotation;

use App\Facades\Settings;
use App\Models\Tenant\MailQueue;
use App\Models\Tenant\Media\FileManager;
use App\Models\Tenant\Quotation;
use App\Utilities\Quotation\QuotationHasher;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

final class InternalQuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param MailQueue $mailQueue
     * @param string $attachment
     * @param string $attachmentName
     * @param int $expiresAfter
     * @param string $generatedHash
     */
    public function __construct(
        public readonly MailQueue $mailQueue,
        public readonly string $attachment,
        public readonly string $attachmentName,
        public readonly int $expiresAfter,
        private readonly string $generatedHash
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            $this->mailQueue->from,
            explode(',', $this->mailQueue->to),
            $this->mailQueue->cc ? explode(',', $this->mailQueue->cc) : [],
            $this->mailQueue->bcc ? explode(',', $this->mailQueue->bcc) : [],
            subject: $this->mailQueue->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $quotation = Quotation::findOrFail($this->mailQueue->model_id);

        $urls = [
            'accept_url' => URL::temporarySignedRoute(
                'quotations-accept',
                Carbon::now()->addDays($this->expiresAfter),
                parameters: [
                    'quotation' => $quotation->getAttribute('id'),
                    'qh' => $this->generatedHash
                ]
            ),

            'reject_url' => URL::temporarySignedRoute(
                'quotations-reject',
                Carbon::now()->addDays($this->expiresAfter),
                [
                    'quotation' => $quotation->getAttribute('id'),
                    'qh' => $this->generatedHash
                ]
            )
        ];
        $settings = Settings::newModelQuery()
                    ->where([['namespace', 'themes'], ['area', 'mail']])
                    ->pluck('value', 'key')
                    ->map(fn($value) => parseMailSetting($value))
                    ->toArray();
        if ($settings['mail_logo']) {
            $media = FileManager::find($settings['mail_logo']);
            $image_url = Storage::disk('assets')->url(tenant()->uuid . '/' . $media->name);
            $urls['image_url'] = $image_url;
        }

        $with = array_merge($urls, $settings);

        return new Content('emails.tenant.quotation.internal-quotation', with: $with);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('local', $this->attachment)
                ->as($this->attachmentName)
                ->withMime('application/pdf'),
        ];
    }
}
