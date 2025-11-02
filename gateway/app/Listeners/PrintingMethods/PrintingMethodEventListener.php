<?php

namespace App\Listeners\PrintingMethods;

use App\Events\PrintingMethods\CreatePrintingMethodEvent;
use App\Models\Tenants\Language;
use App\Models\Tenants\PrintingMethod;

class PrintingMethodEventListener
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
    public function onPrintingMethodEventCreated($event)
    {

        $event->printingMethod->row_id = $event->printingMethod->id;
        $event->printingMethod->iso = $event->iso;
        $event->printingMethod->save();
        $languages = Language::where('iso', '!=', $event->iso)->get()->pluck('iso');
        $dataToStore = $event->printingMethod->toArray();
        unset($dataToStore['id']);
        foreach ($languages as $language) {
            $dataToStore['iso'] = $language;
            PrintingMethod::firstOrCreate([
                'iso' => $dataToStore['iso'],
                'row_id' => $dataToStore['row_id'],

            ],$dataToStore);
        }
    }

    /**
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            CreatePrintingMethodEvent::class,
            'App\Listeners\PrintingMethods\PrintingMethodEventListener@onPrintingMethodEventCreated'
        );

    }
}
