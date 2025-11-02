<?php

namespace App\Listeners\Order\Item;

use App\Events\Tenant\Order\Item\ChangeItemStatusEvent;
use App\Events\Tenant\Order\Item\ProduceItemEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Bus\Dispatchable;
use Throwable;

final class ItemEventListener implements ShouldQueue
{
    use Dispatchable;

    public function onItemProduced(
        ProduceItemEvent $event
    ): void {

    }

    public function onItemStatusChange(
        ChangeItemStatusEvent $event
    ): void {

    }

    /**
     * @param Dispatcher $dispatcher
     */
    public function subscribe(
        Dispatcher $dispatcher
    ): void
    {
        $dispatcher->listen(ProduceItemEvent::class, [$this, 'onItemProduced']);
        $dispatcher->listen(ChangeItemStatusEvent::class, [$this, 'onItemStatusChange']);
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
