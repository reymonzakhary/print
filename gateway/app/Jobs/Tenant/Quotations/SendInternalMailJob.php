<?php

declare(strict_types=1);

namespace App\Jobs\Tenant\Quotations;

use App\Actions\PriceAction\CalculationAction;
use App\Foundation\Media\FileManager;
use App\Foundation\Status\Status;
use App\Mail\Tenant\Quotation\InternalQuotationMail;
use App\Models\Tenants\MailQueue;
use App\Models\Tenants\Quotation;
use App\Utilities\Quotation\Generator\QuotationPdfGenerator;
use Carbon\Carbon;
use Hyn\Tenancy\Contracts\Website;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use LogicException;
use Random\RandomException;
use Throwable;

final class SendInternalMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly Website         $tenant,
        private readonly Authenticatable $user,
        private readonly Quotation       $quotation,
        private readonly MailQueue       $mailQueue,
        private readonly int             $expiresAfter,
        private readonly string          $language,
        private readonly string          $generatedHash,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(
        QuotationPdfGenerator $pdfGenerator,
    ): void
    {
        try {
            switchSupplier($this->tenant->uuid);

            App::setLocale($this->language);

            $pdfBasePath = $this->getPdfSavingLocation();
            $pdfName = $this->generateNameForThePdfFile();
            $pdfFullPath = sprintf('%s/%s', $pdfBasePath, $pdfName);

            $pdfGenerator
                ->generate((new CalculationAction($this->quotation->load([
                    'orderedBy',
                    'orderedBy.profile',
                    'items',
                    'items.media',
                    'items.services',
                    'items.addresses',
                    'items.children',
                    'items.children.addresses',
                    'services',
                    'delivery_address',
                    'invoice_address',
                    'delivery_address.country', 'invoice_address.country',
                ])))->Calculate(), $this->user)
                ->save($pdfFullPath, "local");

            $uploadedFile = new UploadedFile(Storage::disk('local')->path($pdfFullPath), $pdfName);

            $fileManager = app(FileManager::class);

            $fileManager->upload(
                $this->user,
                'tenancy',
                $pdfBasePath,
                $uploadedFile,
                true,
                $pdfBasePath,
                Quotation::class,
                # Casting temporarily to `string` until the `upload` method is refactored
                (string)$this->quotation->getAttribute('id')
            );


            Mail::to(
                $this->mailQueue->getAttribute('to')
            )->send(
                new InternalQuotationMail(
                    $this->mailQueue,
                    $pdfFullPath,
                    $pdfName,
                    $this->expiresAfter,
                    $this->generatedHash
                )
            ) ?: throw new LogicException(
                __('The mail could not be sent')
            );

            # As email has been sent, schedule a deletion for the attachment file after 10 minutes from now.
            DeleteEntityFromDiskJob::dispatch('local', $pdfFullPath, true)->delay(Carbon::now()->addMinutes(10));

            $this->mailQueue->update(['st' => Status::MAILED, 'sent_at' => now()]);
            $this->mailQueue->log('Email sent successfully', Status::MAILED);
        } catch (Throwable $e) {
            $this->mailQueue->update(['st' => Status::FAILED]);
            $this->mailQueue->log($e->getMessage(), Status::FAILED, $e->getTraceAsString());

            isset($pdfFullPath) && Storage::disk('local')->delete($pdfFullPath);
        }
    }

    /**
     */
    private function generateNameForThePdfFile(): string
    {
        return sprintf(
            'quotation_%s_send_at_%s.pdf',
            $this->quotation->getAttribute('id'),
            now()->format('Y_m_d_H:i:s')
        );
    }

    /**
     * @return string
     */
    private function getPdfSavingLocation(): string
    {
        return sprintf(
            "%s/quotations/%s/%s",
            $this->tenant->uuid,
            $this->quotation->getAttribute('id'),
            'emails'
        );
    }
}
