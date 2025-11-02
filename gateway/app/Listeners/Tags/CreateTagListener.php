<?php

namespace App\Listeners\Tags;

use App\Events\Tags\CreateTagEvent;
use App\Models\Tenants\Language;
use App\Models\Tenants\Tag;

class CreateTagListener
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
    public function onTagEventCreated($event)
    {
        $event->tag->row_id = $event->tag->id;
        $event->tag->iso = $event->iso;
        $event->tag->save();
        $languages = Language::where('iso', '!=', $event->iso)->get()->pluck('iso');
        $dataToStore = $event->tag->toArray();
        unset($dataToStore['id']);
        foreach ($languages as $language) {
            $dataToStore['iso'] = $language;
            Tag::create($dataToStore);
        }
    }

    public function subscribe($events)
    {
        $events->listen(
            CreateTagEvent::class,
            'App\Listeners\Tags\CreateTagListener@onTagEventCreated'
        );

    }
}
