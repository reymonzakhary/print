<?php

namespace App\Listeners\Order;

use App\Enums\Status;
use App\Events\Tenant\Order\CreateQuotationEvent;
use App\Events\Tenant\Order\DeleteQuotationEvent;
use App\Events\Tenant\Order\LockQuotationEvent;
use App\Events\Tenant\Order\UnlockQuotationEvent;
use App\Events\Tenant\Order\UpdateQuotationEvent;
use App\Events\Tenant\Quotation\QuotationAcceptedEvent;
use App\Events\Tenant\Quotation\QuotationRejectedEvent;
use App\Jobs\Tenant\Quotations\NotifyAuthorAboutCustomerResponseMailJob;
use App\Models\Tenants\Media\FileManager;
use App\Models\Tenants\Order;
use App\Models\Tenants\Quotation;
use Carbon\Carbon;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class QuotationEventListener
{
    /**
     * @param CreateQuotationEvent $event
     * @return void
     */
    public function onQuotationCreated(
        CreateQuotationEvent $event
    ): void {
        $event->order->history()->create([
            'created_by' => $event->user->id,
            'event' => __('added new quotation')
        ]);
    }

    /**
     * @param DeleteQuotationEvent $event
     * @return void
     */
    public function onQuotationRemoved(
        DeleteQuotationEvent $event
    ): void {
        $event->order->history()->create([
            'created_by' => $event->user->id,
            'event' => __('has delete quotation')
        ]);

        // Clean up quotation files and directory
        $this->cleanupQuotationFiles($event->order);
    }

    /**
     * @param UpdateQuotationEvent $event
     */
    public function onQuotationUpdate(
        UpdateQuotationEvent $event
    ): void {
//        $changed = array_diff_assoc($event->attributes ,$event->original);
        foreach ($event->order->getChanges() as $key => $value) {
            $event->order->history()->create([
                'created_by' => $event->user->id,
                'event' => __("has update {$key}"),
                'from' => $event->original[$key],
                'to' => $value
            ]);
        }
    }

    /**
     * @param LockQuotationEvent $event
     * @throws Throwable
     */
    public function onQuotationLocked(
        LockQuotationEvent $event
    ): void {
        if (!$event->quotation->getAttribute('locked_by')) {
            $event->quotation->setAttribute('locked', true);
            $event->quotation->setAttribute('locked_by', $event->user->getAuthIdentifier());
            $event->quotation->setAttribute('locked_at', Carbon::now()->toDateTimeString());

            $event->quotation->saveOrFail();
        }
    }

    /**
     * @param UnlockQuotationEvent $event
     * @throws Throwable
     */
    public function onQuotationUnLocked(
        UnlockQuotationEvent $event
    ): void {
        Quotation::where([
            'type' => false,
            'locked_by' => $event->user->getAuthIdentifier()
        ])->update([
            'locked' => false,
            'locked_by' => null,
            'locked_at' => null
        ]);
    }

    public function onQuotationAcceptedHandleItemStatus(
        QuotationAcceptedEvent $event
    ): void
    {
        $event->quotation->items()->whereStatusIsDraft()->update(['st' => Status::NEW]);
    }

    /**
     * Move the attached addresses (Re-sync) from the quotation to the order
     *
     * @param QuotationAcceptedEvent $event
     *
     * @return void
     */
    public function onQuotationAcceptedSyncAddressToOrder(
        QuotationAcceptedEvent $event
    ): void
    {
        $order = Order::findOrFail($event->quotation->getAttribute('id'));

        if ($address = $event->quotation->delivery_address()->first()) {
            $order->delivery_address()->sync([
                $address->id => [
                    'type' => $address->pivot->type,
                    'full_name' => $address->pivot->full_name,
                    'company_name' => $address->pivot->company_name,
                    'phone_number' => $address->pivot->phone_number,
                    'tax_nr' => $address->pivot->tax_nr,
                    'team_address' => $address->pivot->team_address,
                    'team_id' => $address->pivot->team_id,
                    'team_name' => $address->pivot->team_name
                ]
            ]);

            $event->quotation->delivery_address()->detach();
        }

        if ($invoice = $event->quotation->invoice_address()->first()) {
            $order->invoice_address()->sync([
                $invoice->id => [
                    'type' => $invoice->pivot->type,
                    'full_name' => $invoice->pivot->full_name,
                    'company_name' => $invoice->pivot->company_name,
                    'phone_number' => $invoice->pivot->phone_number,
                    'tax_nr' => $invoice->pivot->tax_nr,
                    'team_address' => $invoice->pivot->team_address,
                    'team_id' => $invoice->pivot->team_id,
                    'team_name' => $invoice->pivot->team_name
                ]
            ]);

            $event->quotation->invoice_address()->detach();
        }
    }

    /**
     * Touching the newly-accepted quotation via the `order` model to fire the related events for that model
     *
     * @param QuotationAcceptedEvent $event
     *
     * @return void
     *
     * @throws Throwable
     */
    public function onQuotationAcceptedTouchTheOrder(
        QuotationAcceptedEvent $event
    ): void
    {
        $order = Order::query()->findOrFail($event->quotation->getAttribute('id'));
        /* @var Order $order */
        $order->history()->create([
            'created_by' => $event->user? $event->user->id : $order->orderedBy->id,
            'event' => $event->user? __("Quotation has moved to order "): __("Quotation has accepted and moved to order "),
            'from' => __("Quotation id: {$order->id}"),
            'to' => __("Order number: {$order->order_nr}")
        ]);
        /* Simple touch for the model to fire the `update` related events */
        $order->updateOrFail(['editing' => true]) && $order->updateOrFail(['editing' => false]);
    }

    /**
     * Notify the author about the acceptation of the quotation
     *
     * @param QuotationAcceptedEvent $event
     *
     * @return void
     */
    public function onQuotationAcceptedNotifyCustomer(
        QuotationAcceptedEvent $event
    ): void
    {
        NotifyAuthorAboutCustomerResponseMailJob::dispatch(
            $event->tenant->uuid,
            $event->quotation,
            isAcceptation: true
        );
    }


    /**
     * Copy Media To Order Folder On Quotation Acceptation
     *
     * @param QuotationAcceptedEvent $event
     *
     * @return void
     */
    public function onQuotationAcceptedMoveMedia(
        QuotationAcceptedEvent $event
    ): void
    {
        $quotation = $event->quotation;
        $tenant = $event->tenant;

        try {
            // Define source and destination paths
            $quotationFolderPath = "/{$tenant->uuid}/quotations/{$quotation->id}";
            if (!Storage::disk('tenancy')->exists($quotationFolderPath)) {
                return;
            }
            $this->moveQuotationMediaToOrderRecursively($quotationFolderPath);
            $this->updateMediaPathsToOrder($quotation);
        } catch (\Exception $e) {
            Log::warning('Failed to copy quotation media to order folder', [
                'order_id' => $quotation->id,
                'error' => $e->getMessage()
            ]);
        }
    }





    /**
     * Notify the author about the rejection of the quotation
     *
     * @param QuotationRejectedEvent $event
     *
     * @return void
     */
    public function onQuotationRejectedNotifyCustomer(
        QuotationRejectedEvent $event
    ): void
    {
        NotifyAuthorAboutCustomerResponseMailJob::dispatch(
            $event->tenant->uuid,
            $event->quotation,
            isAcceptation: false
        );
    }


    /**
     * Recursively copy directory contents
     *
     * @param string $source
     * @return void
     */
    private function moveQuotationMediaToOrderRecursively(string $source): void
    {
        $storage = Storage::disk('tenancy');
        $files = $storage->allFiles($source);
        foreach ($files as $file) {
            $storage->move($file , str_replace('quotations', 'orders', $file));
        }
        $directories = $storage->allDirectories($source);
        foreach ($directories as $directory) {
            $files = $storage->allFiles($directory);
            foreach ($files as $file) {
                $storage->move($file, str_replace('quotations', 'orders', $file));
            }
        }
    }



    /**
     * Update media records to point to new order paths
     *
     * @param Quotation $quotation
     * @return void
     */
    private function updateMediaPathsToOrder(Quotation $quotation): void
    {
        $quotationMedia = $quotation->getMedia();
        foreach ($quotationMedia as $media) {
            $path_edited = str_replace('quotations', 'orders', $media->path);
            $media->update([
                'path' => $path_edited,
                'model_type' => Order::class,
            ]);
        }
        foreach ($quotation->items as $item) {
            $itemMedia = $item->getMedia();
            foreach ($itemMedia as $media) {
                $path_edited = str_replace('quotations', 'orders', $media->path);
                $media->update(['path' => $path_edited]);
            }
        }
    }

    /**
     * Clean up quotation files and directories when quotation is deleted
     *
     * @param Quotation|Order $quotation
     * @return void
     */
    private function cleanupQuotationFiles(Quotation|Order $quotation): void
    {
        try {
            $tenant = tenant();
            if (!$tenant) {
                return;
            }
            $quotationFolderPath = "{$tenant->uuid}/quotations/{$quotation->id}";
            $storage = Storage::disk('tenancy');
            if (!$storage->exists($quotationFolderPath)) {
                return;
            }
            $this->removeQuotationMediaFromDatabase($quotation);
            $storage->deleteDirectory($quotationFolderPath);
        } catch (\Exception $e) {
            Log::warning('Failed to clean up quotation files', [
                'quotation_id' => $quotation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove quotation media records from the database
     *
     * @param Quotation|Order $quotation
     * @return void
     */
    private function removeQuotationMediaFromDatabase(Quotation|Order $quotation): void
    {
        try {
            $quotationMedia = $quotation->getMedia();
            foreach ($quotationMedia as $media) {
                $media->delete();
            }
            // Remove items media files from database
            foreach ($quotation->items as $item) {
                $itemMedia = $item->getMedia();
                foreach ($itemMedia as $media) {
                    $media->delete();
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to remove quotation media records from database', [
                'quotation_id' => $quotation->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * @param Dispatcher $dispatcher
     */
    public function subscribe(
        Dispatcher $dispatcher
    ): void {
        $dispatcher->listen(CreateQuotationEvent::class, [$this, 'onQuotationCreated']);

        $dispatcher->listen(DeleteQuotationEvent::class, [$this, 'onQuotationRemoved']);

        $dispatcher->listen(UpdateQuotationEvent::class, [$this, 'onQuotationUpdate']);

        $dispatcher->listen(LockQuotationEvent::class, [$this, 'onQuotationLocked']);

        $dispatcher->listen(UnlockQuotationEvent::class, [$this, 'onQuotationUnLocked']);

        $dispatcher->listen(QuotationAcceptedEvent::class, [$this, 'onQuotationAcceptedHandleItemStatus']);
        $dispatcher->listen(QuotationAcceptedEvent::class, [$this, 'onQuotationAcceptedSyncAddressToOrder']);
        $dispatcher->listen(QuotationAcceptedEvent::class, [$this, 'onQuotationAcceptedTouchTheOrder']);
        $dispatcher->listen(QuotationAcceptedEvent::class, [$this, 'onQuotationAcceptedNotifyCustomer']);
        $dispatcher->listen(QuotationAcceptedEvent::class, [$this, 'onQuotationAcceptedMoveMedia']);

        $dispatcher->listen(QuotationRejectedEvent::class, [$this, 'onQuotationRejectedNotifyCustomer']);
    }
}
