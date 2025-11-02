<?php

declare(strict_types=1);

namespace App\Jobs\Tenant\Quotations;

use App\Facades\Settings;
use App\Mail\Tenant\Quotation\NotifyAuthorAboutCustomerResponseMail;
use App\Models\Tenants\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotifyAuthorAboutCustomerResponseMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string    $tenant_uuid,
        private readonly Quotation $quotation,
        private readonly bool      $isAcceptation
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        switchSupplier($this->tenant_uuid);

        $targetEmail = Settings::orderEmailTo()->value ?? Settings::mailSmtpFrom()->value;

        Mail::to($targetEmail)->send(new NotifyAuthorAboutCustomerResponseMail($this->quotation, $targetEmail, $this->isAcceptation));
    }
}
