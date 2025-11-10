<?php

namespace App\Jobs\Tenant;

use App\DTO\Tenant\Orders\ItemDTO;
use App\Facades\Settings;
use App\Models\Tenant\Order;
use App\Models\Tenant\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection as SupportCollection;

class RefactorItemInput
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly Quotation|Order  $quotation,
        public readonly Collection|SupportCollection $items,
    ){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        collect(ItemDTO::fromPrintDB(
            $this->items
        ))->each(fn ($item, $id) =>
            $this->quotation->items()->where('items.id', $id)->update([
                'product' => $item,
                'vat' => Settings::vat()?->value
            ])
        );

    }
}
