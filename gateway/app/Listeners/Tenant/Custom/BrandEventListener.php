<?php

namespace App\Listeners\Tenant\Custom;

use App\Events\Tenant\Custom\CreateBrandEvent;
use App\Events\Tenant\Custom\UpdatedBrandEvent;
use App\Models\Tenant\Brand;
use App\Models\Tenant\Language;
use Illuminate\Support\Str;

class BrandEventListener
{

    public function onBrandCreated($event)
    {
        collect(Language::where('iso', '!=', app()->getLocale())->get())->map(function ($lang) use ($event) {
            $transition = collect($event->translation)->filter(fn($t) => $t['iso'] === $lang->iso)->first();
            Brand::create([
                'row_id' => $event->brand->id,
                'name' => optional($transition)['name'] ?? $event->brand->name,
                'description' => optional($transition)['description'] ?? $event->brand->description,
                'iso' => $lang->iso,
                'created_by' => $event->brand->created_by,
                'published' => $event->brand->published,
                'published_by' => $event->brand->published_by,
                'published_at' => $event->brand->published_at
            ]);
        });
    }

    /**
     * handle update brand with base id
     * @param $event
     */
    public function onBrandUpdated($event)
    {
        collect(
            Brand::where(['row_id' => $event->brand->row_id])->get()->except([$event->brand->id])
        )->map(function ($cat) use ($event) {
            $transition = collect($event->translation)->filter(fn($t) => $t['iso'] === $cat->iso)->first();
            $cat->update([
                'name' => optional($transition)['name'] ?? $cat->name,
                'slug' => optional($transition)['name'] ? Str::slug(optional($transition)['name']) : $cat->slug,
                'description' => optional($transition)['description'] ?? $cat->description,
                'published' => $event->brand->published,
                'published_by' => $event->brand->published_by,
                'published_at' => $event->brand->published_at
            ]);
        });
        $event->brand->save();
    }

    /**
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            CreateBrandEvent::class,
            'App\Listeners\Tenant\Custom\BrandEventListener@onBrandCreated'
        );

        $events->listen(
            UpdatedBrandEvent::class,
            'App\Listeners\Tenant\Custom\BrandEventListener@onBrandUpdated'
        );
    }

    public function failed($event, $exception)
    {
        dd($exception);
    }
}
