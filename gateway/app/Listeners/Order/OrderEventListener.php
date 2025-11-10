<?php

namespace App\Listeners\Order;

use App\Events\Tenant\Order\CreatedItemOrderEvent;
use App\Events\Tenant\Order\CreateOrderEvent;
use App\Events\Tenant\Order\Item\Address\UpdateOrderItemAddressEvent;
use App\Events\Tenant\Order\Item\Media\CreateOrderItemMediaEvent;
use App\Events\Tenant\Order\Item\Media\DeleteOrderItemMediaEvent;
use App\Events\Tenant\Order\Item\Service\CreateOrderItemServiceEvent;
use App\Events\Tenant\Order\Item\Service\DeleteOrderItemServiceEvent;
use App\Events\Tenant\Order\Item\Service\Media\CreateOrderItemServiceMediaEvent;
use App\Events\Tenant\Order\Item\Service\Media\DeleteOrderItemServiceMediaEvent;
use App\Events\Tenant\Order\Item\Service\UpdateOrderItemServiceEvent;
use App\Events\Tenant\Order\LockOrderEvent;
use App\Events\Tenant\Order\Media\DeleteOrderMediaEvent;
use App\Events\Tenant\Order\Notification\OrderNotificationEmailEvent;
use App\Events\Tenant\Order\RemoveItemOrderEvent;
use App\Events\Tenant\Order\Service\CreateOrderServiceEvent;
use App\Events\Tenant\Order\Service\DeleteOrderServiceEvent;
use App\Events\Tenant\Order\Service\Media\CreateOrderServiceMediaEvent;
use App\Events\Tenant\Order\Service\Media\DeleteOrderServiceMediaEvent;
use App\Events\Tenant\Order\Service\UpdateOrderServiceEvent;
use App\Events\Tenant\Order\UnlockOrderEvent;
use App\Events\Tenant\Order\UpdateItemOrderEvent;
use App\Jobs\Tenant\Quotations\DeleteEntityFromDiskJob;
use App\Models\Tenant\Media;
use App\Models\Tenant\Order;
use App\Models\User;
use App\Processors\Status\ChangeItemStatus;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Bus\Dispatchable;
use JsonException;
use Throwable;

final class OrderEventListener implements ShouldQueue
{
    use Dispatchable;

    /**
     * @param CreateOrderEvent $event
     */
    public function onOrderCreated(
        CreateOrderEvent $event
    ): void
    {
        $event->order->history()->create([
            'created_by' => $event->user->getAuthIdentifier(),
            'event' => __('added new order'),
            'external' => $event->user instanceof User,
        ]);
    }

    public function onOrderMediaDelete(
        DeleteOrderMediaEvent $event
    ): void
    {
        $event->order->history()->create([
            'created_by' => $event->user->getAuthIdentifier(),
            'event' => __("has deleted media with ID {$event->mediaId} on order {$event->order->id}"),
            'from' => $event->mediaId,
            'external' => $event->user instanceof User,
        ]);
    }

    /**
     * @param CreatedItemOrderEvent $event
     */
    public function onAddItemsToOrder(
        CreatedItemOrderEvent $event
    ): void
    {
        $event->order->history()->create([
            'created_by' => $event->user->getAuthIdentifier(),
            'event' => __("has add new item with id: {$event->item->id}"),
            'to' => $event->item->id,
            'external' => $event->user instanceof User,
        ]);
    }

    /**
     * @param UpdateItemOrderEvent $event
     * @throws JsonException
     */
    public function onItemUpdate(
        UpdateItemOrderEvent $event
    ): void
    {
        $attributes = $event->attributes;
        $original = $event->original;
        $original['product'] = json_encode($original['product']->toArray(), JSON_THROW_ON_ERROR);
        $changed = array_diff_assoc($attributes, $original);

        if (count($changed) > 0) {
            foreach ($changed as $key => $value) {
                if ($key === 'product') {
                    $productOriginal = is_string($original['product']) ? json_decode($original['product'], true) : $original['product'];
                    $productAttributes = is_string($original['product']) ? json_decode($original['product'], true) : $original['product'];

                    $event->order->history()->create([
                        'created_by' => $event->user->getAuthIdentifier(),
                        'event' => __("has change {$key} on item {$event->item->id}"),
                        'from' => is_array(optional($productOriginal['price'])['product']) ? optional($productOriginal['price'])['product']['$oid'] : optional($productOriginal['price'])['product'],
                        'to' => is_array(optional($productAttributes['price'])['product']) ? optional(optional($productAttributes['price'])['product'])['$oid'] : optional(optional($productAttributes)['price'])['product'],
                        'key' => $key,
                        'external' => $event->user instanceof User,
                    ]);
                } else {
                    if ($event->order->created_from === 'system' && in_array('st', array_keys($changed))) {
                        (new ChangeItemStatus)($event->order, $event->item, $changed['st']);
                    }

                    $event->order->history()->create([
                        'created_by' => $event->user->getAuthIdentifier(),
                        'event' => __("has update {$key} on item {$event->item->id}"),
                        'from' => $event->original[$key],
                        'to' => $value,
                        'external' => $event->user instanceof User,
                    ]);

                }
            }
        }
    }

    /**
     * @param RemoveItemOrderEvent $event
     */
    public function onItemRemoveLogToHistory(
        RemoveItemOrderEvent $event
    ): void
    {
        $event->order->history()->create([
            'created_by' => $event->user->getAuthIdentifier(),
            'external' => $event->user instanceof User,
            'event' => __('has removed item with id: ":item_id"', [
                'item_id' => $event->item->getAttribute('id')
            ])
        ]);
    }

    /**
     * @param RemoveItemOrderEvent $event
     * @throws Throwable
     */
    public function onItemRemoveDeleteAttachmentsFromDisk(
        RemoveItemOrderEvent $event
    ): void
    {
        $parentEntity = $event->order;
        if (count(optional($event->item)->getMedia() ?? []) === 0
        ) {
            return;
        }

        $parentEntityType = match ($parentEntity->getAttribute('type')) {
            true => 'orders',
            false => 'quotations'
        };

        $pathToItemFolder = sprintf(
            '%s/%s/%s/items/%s',
            tenant()->uuid,
            $parentEntityType,
            $parentEntity->getAttribute('id'),
            $event->item->getAttribute('id')
        );

        DeleteEntityFromDiskJob::dispatch(
            'tenancy',
            $pathToItemFolder,
            false
        );

        $event->item->getMedia('items')->each->delete();
    }

    /**
     * @param UpdateOrderItemAddressEvent $event
     */
    public function onOrderItemAddressUpdate(
        UpdateOrderItemAddressEvent $event
    ): void
    {
        $event->order->history()->create([
            'created_by' => $event->user->getAuthIdentifier(),
            'event' => __("has sync address {$event->addressId} on item {$event->item->id}"),
            'to' => $event->addressId,
            'external' => $event->user instanceof User,

        ]);
    }

    /**
     * @param CreateOrderItemMediaEvent $event
     */
    public function onOrderItemMediaCreate(
        CreateOrderItemMediaEvent $event
    ): void
    {
        if (is_array($event->media)) {
            $to = collect($event->media)->map(function ($item) {
                return " To FileManager id: {$item->id} name: {$item->name}";
            })->implode(' ');

            $event->order->history()->create([
                'created_by' => $event->user->getAuthIdentifier(),
                'event' => __("has add new media on item {$event->item->id}"),
                'to' => $to,
                'external' => $event->user instanceof User
            ]);
        }

        if ($event->media instanceof Media) {
            $event->order->history()->create([
                'created_by' => $event->user->getAuthIdentifier(),
                'event' => __("has add new media on item {$event->item->id}"),
                'to' => $event->media->id,
                'external' => $event->user instanceof User
            ]);
        }
    }

    /**
     * @param DeleteOrderItemMediaEvent $event
     */
    public function onOrderItemMediaDelete(
        DeleteOrderItemMediaEvent $event
    ): void
    {
        $event->order->history()->create([
            'created_by' => $event->user->getAuthIdentifier(),
            'event' => __("has deleted media with ID {$event->mediaId} on item {$event->item->id}"),
            'from' => $event->mediaId,
            'external' => $event->user instanceof User
        ]);
    }

    /**
     * @param CreateOrderServiceEvent $event
     */
    public function onOrderServiceCreate(
        CreateOrderServiceEvent $event
    ): void
    {
        $event->order->history()->create([
            'created_by' => $event->user->getAuthIdentifier(),
            'to' => $event->serviceId,
            'external' => $event->user instanceof User,
            'event' => __('has assigned service ID ":service_id" on order {$event->order->id}', [
                'service_id' => $event->serviceId
            ]),
        ]);
    }

    /**
     * @param UpdateOrderServiceEvent $event
     */
    public function onOrderServiceUpdate(
        UpdateOrderServiceEvent $event
    ): void
    {
        $event->order->history()->create([
            'created_by' => $event->user->getAuthIdentifier(),
            'event' => __("has updated Services {$event->service->id} on order {$event->order->id}"),
            'external' => $event->user instanceof User

        ]);
    }

    /**
     * @param DeleteOrderServiceEvent $event
     */
    public function onOrderServiceDelete(
        DeleteOrderServiceEvent $event
    ): void
    {
        $event->order->history()->create([
            'created_by' => $event->user->getAuthIdentifier(),
            'event' => __("has detach Service with ID {$event->serviceId} on order {$event->order->id}"),
            'from' => $event->serviceId,
            'external' => $event->user instanceof User
        ]);
    }

    /**
     * @param CreateOrderServiceMediaEvent $event
     */
    public function onOrderServiceMediaCreate(
        CreateOrderServiceMediaEvent $event
    ): void
    {
        $event->order->history()->create([
            'created_by' => $event->user->getAuthIdentifier(),
            'event' => __("has added New Media in Service {$event->service->id} on order {$event->order->id}"),
            'to' => $event->media->id,
            'external' => $event->user instanceof User

        ]);
    }

    /**
     * @param DeleteOrderServiceMediaEvent $event
     */
    public function onOrderServiceMediaDelete(
        DeleteOrderServiceMediaEvent $event
    ): void
    {
        $event->order->history()->create([
            'created_by' => $event->user->getAuthIdentifier(),
            'event' => __("has deleted Media in Service {$event->service->id} on order {$event->order->id}"),
            'from' => $event->mediaId,
            'external' => $event->user instanceof User

        ]);
    }

    /**
     * @param CreateOrderItemServiceEvent $event
     */
    public function onOrderItemServiceCreate(
        CreateOrderItemServiceEvent $event
    ): void
    {
        foreach ($event->serviceIds as $key => $serviceId) {
            $event->order->history()->create([
                'created_by' => $event->user->getAuthIdentifier(),
                'event' => __("has assigned service ID {$serviceId} on item {$event->item->id} on order {$event->order->id}"),
                'to' => $serviceId,
                'external' => $event->user instanceof User

            ]);
        }
    }

    /**
     * @param UpdateOrderItemServiceEvent $event
     */
    public function onOrderItemServiceUpdate(
        UpdateOrderItemServiceEvent $event
    ): void
    {
        $event->order->history()->create([
            'created_by' => $event->user->getAuthIdentifier(),
            'event' => __("has updated Services on item {$event->item->id} on order {$event->order->id}"),
            'external' => $event->user instanceof User

        ]);
    }

    /**
     * @param DeleteOrderItemServiceEvent $event
     */
    public function onOrderItemServiceDelete(
        DeleteOrderItemServiceEvent $event
    ): void
    {
        $event->order->history()->create([
            'created_by' => $event->user->getAuthIdentifier(),
            'event' => __("has detach Service with ID {$event->serviceId} on item {$event->item->id} on order {$event->order->id}"),
            'from' => $event->serviceId,
            'external' => $event->user instanceof User

        ]);
    }

    /**
     * @param CreateOrderItemServiceMediaEvent $event
     */
    public function onOrderItemServiceMediaCreate(
        CreateOrderItemServiceMediaEvent $event
    ): void
    {
        $event->order->history()->create([
            'created_by' => $event->user->getAuthIdentifier(),
            'event' => __("has added New Media in Service {$event->service->id} on item {$event->item->id} on order {$event->order->id}"),
            'to' => $event->media->id,
            'external' => $event->user instanceof User

        ]);
    }

    /**
     * @param DeleteOrderItemServiceMediaEvent $event
     */
    public function onOrderItemServiceMediaDelete(
        DeleteOrderItemServiceMediaEvent $event
    ): void
    {
        $event->order->history()->create([
            'created_by' => $event->user->getAuthIdentifier(),
            'event' => __("has deleted Media in Service {$event->service->id} on item {$event->item->id} on order {$event->order->id}"),
            'from' => $event->mediaId,
            'external' => $event->user instanceof User

        ]);
    }

    /**
     * @param OrderNotificationEmailEvent $event
     */
    public function onOrderNotificationEmail(
        OrderNotificationEmailEvent $event
    ): void
    {
        $order = $event->order;

        $event->order->history()->create([
            'created_by' => $event->user->getAuthIdentifier(),
            'event' => __("has sent email to user {$order->orderedBy->id} on order {$order->id}"),
            'from' => $event->user->getAuthIdentifier(),
            'to' => $order->orderedBy->id,
            'external' => $event->user instanceof User

        ]);
    }

    /**
     * @param LockOrderEvent $event
     */
    public function onOrderLocked(
        LockOrderEvent $event
    ): void {
        if (!$event->order->getAttribute('locked_by')) {
            $event->order->setAttribute('locked', true);
            $event->order->setAttribute('locked_by', $event->user->getAuthIdentifier());
            $event->order->setAttribute('locked_at', Carbon::now()->toDateTimeString());

            $event->order->saveOrFail();
        }
    }

    /**
     * @param UnlockOrderEvent $event
     */
    public function onOrderUnLocked(
        UnlockOrderEvent $event
    ): void {
        Order::where([
            'type' => true,
            'locked_by' => $event->user->getAuthIdentifier()
        ])->update([
            'locked' => false,
            'locked_by' => null,
            'locked_at' => null
        ]);
    }




    /**
     * @param Dispatcher $dispatcher
     */
    public function subscribe(
        Dispatcher $dispatcher
    ): void
    {
        $dispatcher->listen(CreateOrderEvent::class, [$this, 'onOrderCreated']);

        $dispatcher->listen(CreatedItemOrderEvent::class, [$this, 'onAddItemsToOrder']);

        $dispatcher->listen(UpdateItemOrderEvent::class, [$this, 'onItemUpdate']);

        $dispatcher->listen(RemoveItemOrderEvent::class, [$this, 'onItemRemoveLogToHistory']);
        $dispatcher->listen(RemoveItemOrderEvent::class, [$this, 'onItemRemoveDeleteAttachmentsFromDisk']);

        $dispatcher->listen(UpdateOrderItemAddressEvent::class, [$this, 'onOrderItemAddressUpdate']);

        $dispatcher->listen(CreateOrderItemMediaEvent::class, [$this, 'onOrderItemMediaCreate']);

        $dispatcher->listen(DeleteOrderItemMediaEvent::class, [$this, 'onOrderItemMediaDelete']);

        $dispatcher->listen(DeleteOrderMediaEvent::class, [$this, 'onOrderMediaDelete']);

        $dispatcher->listen(CreateOrderServiceEvent::class, [$this, 'onOrderServiceCreate']);

        $dispatcher->listen(UpdateOrderServiceEvent::class, [$this, 'onOrderServiceUpdate']);

        $dispatcher->listen(DeleteOrderServiceEvent::class, [$this, 'onOrderServiceDelete']);

        $dispatcher->listen(CreateOrderServiceMediaEvent::class, [$this, 'onOrderServiceMediaCreate']);

        $dispatcher->listen(DeleteOrderServiceMediaEvent::class, [$this, 'onOrderServiceMediaDelete']);

        $dispatcher->listen(CreateOrderItemServiceEvent::class, [$this, 'onOrderItemServiceCreate']);

        $dispatcher->listen(UpdateOrderItemServiceEvent::class, [$this, 'onOrderItemServiceUpdate']);

        $dispatcher->listen(DeleteOrderItemServiceEvent::class, [$this, 'onOrderItemServiceDelete']);

        $dispatcher->listen(CreateOrderItemServiceMediaEvent::class, [$this, 'onOrderItemServiceMediaCreate']);

        $dispatcher->listen(DeleteOrderItemServiceMediaEvent::class, [$this, 'onOrderItemServiceMediaDelete']);

        $dispatcher->listen(OrderNotificationEmailEvent::class, [$this, 'onOrderNotificationEmail']);

        $dispatcher->listen(LockOrderEvent::class, [$this, 'onOrderLocked']);

        $dispatcher->listen(UnlockOrderEvent::class, [$this, 'onOrderUnLocked']);

    }

    /**
     * @throws Throwable
     */
    public function failed(
        mixed     $event,
        Throwable $exception
    ): void
    {
        throw $exception;
    }
}
