<?php

namespace App\Listeners\DeliveryDays;

use App\Events\DeliveryDays\CreateDeliveryDayEvent;
use App\Events\DeliveryDays\UpdateDeliveryDayEvent;
use App\Models\Tenants\DeliveryDay;
use App\Models\Tenants\Language;

class DeliveryDayEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function onDeliveryDayEventCreated($event)
    {
        $event->deliveryDay->row_id = $event->deliveryDay->id;
        $event->deliveryDay->iso = $event->iso;
        $event->deliveryDay->base_id = $event->deliveryDay->id;
        $event->deliveryDay->save();
        $languages = Language::where('iso', '!=', $event->iso)->get()->pluck('iso');
        $dataToStore = $event->deliveryDay->toArray();

        unset($dataToStore['id']);
        unset($dataToStore['base_id']);

        foreach ($languages as $language) {
            $dataToStore['iso'] = $language;
            DeliveryDay::create($dataToStore);
        }
    }


    /**
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            CreateDeliveryDayEvent::class,
            'App\Listeners\DeliveryDays\DeliveryDayEventListener@onDeliveryDayEventCreated'
        );
    }
}
