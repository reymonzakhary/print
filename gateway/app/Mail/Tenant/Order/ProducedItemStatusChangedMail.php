<?php

declare(strict_types=1);

namespace App\Mail\Tenant\Order;

use App\Models\Domain;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Cms\Entities\Eloquent\Collection;

final class ProducedItemStatusChangedMail extends Mailable
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
        public readonly string $tenantDomain,
        public readonly int $itemId,
        public readonly string $newStatus,
    ) {
    }


    public function build()
    {
        return $this->view('emails.tenant.order.produced-item-status-change');
    }
}
