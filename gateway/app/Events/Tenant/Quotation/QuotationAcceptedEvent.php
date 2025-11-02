<?php

declare(strict_types=1);

namespace App\Events\Tenant\Quotation;

use App\Models\Tenants\Quotation;
use Hyn\Tenancy\Contracts\Website;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class QuotationAcceptedEvent
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public readonly Quotation $quotation,
        public readonly Website $tenant,
        public readonly Model|null $user = null,
    ) {
    }
}
