<?php

namespace App\Observers\Orders;

use App\Enums\Status;
use App\Events\Tenant\Order\CreateOrderEvent;
use App\Events\Tenant\Order\CreateQuotationEvent;
use App\Events\Tenant\Order\DeleteQuotationEvent;
use App\Events\Tenant\Order\UpdateOrderForCustomerEvent;
use App\Events\Tenant\Order\UpdateQuotationEvent;
use App\Models\Tenants\Order;
use App\Models\Tenants\Quotation;

class OrderObserver
{

    /**
     * retrieved : after a record has been retrieved.
     * creating : before a record has been created.
     * created : after a record has been created.
     * updating : before a record is updated.
     * updated : after a record has been updated.
     * saving : before a record is saved (either created or updated).
     * saved : after a record has been saved (either created or updated).
     * deleting : before a record is deleted or soft-deleted.
     * deleted : after a record has been deleted or soft-deleted.
     * restoring : before a soft-deleted record is going to be restored.
     * restored : after a soft-deleted record has been restored.
     */

    /**
     * @param Quotation|Order $order
     */
    public function retrieved(Quotation|Order $order)
    {
//        dump('retrieved', $order->id);
//        if(in_array(request()->method() , ["PUT", "PATCH"])){
//            $user  = auth()->user();
//            $language = Language::where('iso','en')->firstORFail();
//            event(new UpdateResourceEvent($resource, $user, $language));
//        }
    }

    /**
     * Handle the order "saving" event.
     * @param Quotation|Order $order
     * @return void
     */
    public function saving(Quotation|Order $order)
    {
//        dump('saving');
    }

    /**
     * Handle the order "saved" event.
     * @param Quotation|Order $order
     * @return void
     */
    public function saved(Quotation|Order $order)
    {
//        dump('saved');
    }

    /**
     * Handle the order "creating" event.
     * @param Quotation|Order $order
     * @return void
     */
    public function creating(Quotation|Order $order)
    {
//        dump('creating');
    }

    /**
     * Handle the order "created" event.
     *
     * @param Quotation|Order|Quotation $order
     * @return void
     */
    public function created(
        Quotation|Order $order
    )
    {
        $user = auth()->user();
        if ($order->type) {
            event(new CreateOrderEvent($order, $user));
        } else {
            event(new CreateQuotationEvent($order, $user));
        }
    }

    /**
     * Handle the order "updating" event.
     * @param Quotation|Order $order
     * @return void
     */
    public function updating(Quotation|Order $order)
    {
        $user = auth()->user();
        if ($order->order) {
            event(new CreateOrderEvent($order, $user));
        } elseif (!$order->order) {
            event(new UpdateQuotationEvent($order, $order->getOriginal(), $order->getAttributes(), $user));
        }
    }

    /**
     * Handle the order "updated" event.
     *
     * @param Quotation|Order $order
     * @return void
     */
    public function updated(Quotation|Order $order)
    {
        if ($order->type && $order->wasChanged('st') && $order->st > Status::NEW->value && $order->orderedBy) {
            event(new UpdateOrderForCustomerEvent($order , $order->orderedBy));
        }
    }

    /**
     * Handle the order "deleted" event.
     *
     * @param Quotation|Order $order
     * @return void
     */
    public function deleted(Quotation|Order $order)
    {
        $user = auth()->user();
        if ($order->order) {
//            event(new CreateOrderEvent($order, $user));
        } elseif (!$order->order) {
            event(new DeleteQuotationEvent($order, $user));
        }
    }

    /**
     * Handle the order "restored" event.
     *
     * @param Quotation|Order $order
     * @return void
     */
    public function restored(Quotation|Order $order)
    {
//        event(new CreateOrderEvent($order, auth()->user()));
    }

    /**
     * Handle the order "force deleted" event.
     *
     * @param Quotation|Order $order
     * @return void
     */
    public function forceDeleted(Quotation|Order $order)
    {
    }
}
