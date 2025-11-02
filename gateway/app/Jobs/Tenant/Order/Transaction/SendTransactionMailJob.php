<?php

declare(strict_types=1);

namespace App\Jobs\Tenant\Order\Transaction;

use App\Foundation\Media\FileManager as FileManagerFoundation;
use App\Foundation\Status\Status;
use App\Jobs\Tenant\Quotations\DeleteEntityFromDiskJob;
use App\Mail\Tenant\Order\Transaction\TransactionMail;
use App\Models\Tenants\MailQueue;
use App\Models\Tenants\Transaction;
use App\Utilities\Order\Transaction\Generator\TransactionPdfGenerator;
use Hyn\Tenancy\Contracts\Website;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\MailManager;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

final class SendTransactionMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const string TEMP_DISK = 'local';
    public const string PERMANENT_DISK = 'tenancy';

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly Website         $tenant,
        private readonly Authenticatable $user,
        private readonly Transaction     $transaction,
        private readonly MailQueue       $mailQueue,
        private readonly string          $language,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(
        TransactionPdfGenerator $transactionPdfGenerator,
        FileSystemManager       $fileSystemManager,
        FileManagerFoundation   $fileManagerFoundation,
        MailManager             $mailManager,
        Application             $app,
        Carbon                  $carbon
    ): void
    {
        try {
            switchSupplier($this->tenant->uuid);

            $app->setLocale($this->language);

            $pdfFullPath = $this->generatePdfPath();
            $transactionPdfGenerator->generate($this->transaction)->save($pdfFullPath, self::TEMP_DISK);

            $uploadedFile = new UploadedFile(
                $fileSystemManager->disk(self::TEMP_DISK)->path($pdfFullPath),
                basename($pdfFullPath)
            );

            $fileManagerFoundation->upload(
                $this->user,
                self::PERMANENT_DISK,
                dirname($pdfFullPath),
                $uploadedFile,
                true,
                dirname($pdfFullPath),
                Transaction::class,
                # Casting temporarily to `string` until the `upload` method is refactored
                (string)$this->transaction->getAttribute('id')
            );

            $mailManager->to(
                $this->mailQueue->getAttribute('to')
            )->send(
                new TransactionMail($this->mailQueue, [
                    Attachment::fromStorageDisk(self::TEMP_DISK, $pdfFullPath)
                ])
            ) ?: throw new RuntimeException(
                __('The mail could not be sent')
            );

            # As email has been sent, schedule a deletion for the attachment file after 10 minutes from now.
            DeleteEntityFromDiskJob::dispatch(self::TEMP_DISK, $pdfFullPath, true)->delay($carbon->now()->addMinutes(10));

            $this->mailQueue->update(['st' => Status::MAILED, 'sent_at' => now()]);
            $this->mailQueue->log('Email sent successfully', Status::MAILED);
        } catch (Throwable $e) {
            $this->mailQueue->update(['st' => Status::FAILED]);
            $this->mailQueue->log($e->getMessage(), Status::FAILED, $e->getTraceAsString());

            isset($pdfFullPath) && $fileSystemManager->disk(self::TEMP_DISK)->delete($pdfFullPath);
        }
    }

    /**
     * @return string
     */
    private function generatePdfPath(): string
    {
        return sprintf(
            "%s/orders/%s/transactions/%s/transaction_%s_%s_%s_%s.pdf",
            $this->tenant->uuid,

            $this->transaction->order()->firstOrFail()->getAttribute('id'),
            $this->transaction->getAttribute('id'),

            $this->transaction->order()->firstOrFail()->getAttribute('id'),
            $this->transaction->getAttribute('id'),
            $this->mailQueue->getAttribute('id'),

            time(),
        );
    }
}
