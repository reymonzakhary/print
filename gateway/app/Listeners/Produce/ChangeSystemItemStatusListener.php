<?php

namespace App\Listeners\Produce;

use App\Events\Tenant\Order\UpdateItemOrderEvent;
use App\Foundation\Status\Status;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use App\Models\Tenants\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ChangeSystemItemStatusListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        $event->item->st = $event->status->to;
        event(new UpdateItemOrderEvent($event->order, $event->item, $event->item->getOriginal(), $event->item->getAttributes(), User::find(1)));
        $event->item->save();
        if (in_array($event->item->st, [Status::IN_PROGRESS, Status::DELIVERED])) {
            $this->changeStatus($event->order);
        }
        switchSupplier($event->properties->supplier_id);

        $item = collect($event->order->properties->items)->filter(function ($i) use ($event) {
            return $i->local === $event->item->id;
        })->first();

        $item = Item::find($item->parent);
        $order = Item::find($event->order->properties->order_id);
        $item->st = $event->status->to;
        event(new UpdateItemOrderEvent($order, $item, $item->getOriginal(), $item->getAttributes(), User::find(1)));
        $item->save();
        if (in_array($event->item->st, [Status::IN_PROGRESS, Status::DELIVERED])) {
            $this->changeStatus($order);
        }
        switchSupplier($event->uuid);
    }

    public function changeStatus(Order $order)
    {
        $items = $order->items;
        $total = $items->count();

        $delivered = $items->where('st', Status::DELIVERED)->count();

        $inProgress = $items->whereNotIn('st', [Status::NEW, Status::DELIVERED, Status::DONE])->count();

        if ($total === $delivered) {
            $order->update(['st' => Status::DONE]);
        } elseif ($inProgress && !in_array($order->st, [Status::IN_PROGRESS, Status::DONE])) {
            $order->update(['st' => Status::IN_PROGRESS]);
        }
    }
}
