<?php

namespace App\Console\Commands;

use App\Jobs\Tenant\RefactorItemInput;
use App\Models\Tenant\Order;
use App\Models\Tenant\Quotation;
use App\Models\Website;
use Illuminate\Console\Command;

class RefactorPrintItemObject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'items:print:refactor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This can be rendered one time, to transfer the old object to new one ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $counter = 0;
        collect(Website::all('uuid')->toArray())->map(function ($website) use (&$counter) {
            switchSupplier($website['uuid']);
            Quotation::withTrashed()->with('items')->get()->each(function (Quotation $quotation) {
                RefactorItemInput::dispatch($quotation, $quotation->items);
            });

            Order::withTrashed()->with('items')->get()->each(function (Order $order) {
                RefactorItemInput::dispatch($order, $order->items);
            });
            $this->info("Tenant {$website['uuid']} items has been updated successfully");
            $counter++;
        });
        $this->info("There are ({$counter}) tenants has been updated successfully");
    }
}
