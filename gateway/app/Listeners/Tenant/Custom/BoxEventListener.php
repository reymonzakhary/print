<?php

namespace App\Listeners\Tenant\Custom;

use App\Events\Tenant\Custom\CreateBoxEvent;
use App\Events\Tenant\Custom\DeleteBoxEvent;
use App\Events\Tenant\Custom\UpdatedBoxEvent;
use App\Models\Tenants\Box;
use App\Models\Tenants\Language;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class BoxEventListener
{
    public function onBoxCreated(CreateBoxEvent $event): void
    {
        collect(Language::where('iso', '!=', app()->getLocale())->get())->map(function ($lang) use ($event) {
            $transition = collect($event->translation)->filter(fn($t) => $t['iso'] === $lang->iso)->first();

            Box::create([
                'name' => $transition['name'] ?? $event->box->name,
                'description' => $transition['description'] ?? $event->box->description,
                'input_type' => $event->box?->input_type,
                'incremental' => $event->box->incremental,
                'appendage' => $event->box->appendage,
                'select_limit' => $event->box->select_limit,
                'option_limit' => $event->box->option_limit,
                'parent_id' => $event->box->parent_id,
                'sqm' => $event->box->sqm,
                'base_id' => $event->box->base_id,
                'row_id' => $event->box->id,
                'iso' => $lang->iso,
                'created_by' => $event->box->created_by,
            ]);
        });
    }

    /**
     * handle update box with base id
     * @param UpdatedBoxEvent $event
     */
    public function onBoxUpdated(UpdatedBoxEvent $event): void
    {
        $event->box->base_id = $event->box->parent?->base_id ?: $event->box->base_id;

        collect(Box::where(['row_id' => $event->box->row_id])->get()->except([$event->box->id]))->map(
            function ($cat) use ($event) {
                $transition = collect(optional($event)->translation)->filter(fn($t) => $t['iso'] === $cat->iso)->first(
                );

                $cat->update([
                    'name' => optional($transition)['name'] ?? $cat->name,
                    'slug' => optional($transition)['name'] ? Str::slug(optional($transition)['name']) : $cat->slug,
                    'description' => optional($transition)['description'] ?? $cat->description,
                    'parent_id' => $event->box->parent_id,
                    'base_id' => $event->box->parent?->base_id ?? $event->box->row_id,
                    'input_type' => $event->box?->input_type,
                    'incremental' => $event->box->incremental,
                    'appendage' => $event->box->appendage,
                    'select_limit' => $event->box->select_limit,
                    'option_limit' => $event->box->option_limit,
                    'sqm' => $event->box->sqm,
                ]);
            }
        );

        $event->box->save();
    }

    public function onBoxDeleted(DeleteBoxEvent $event): void
    {
        # Delete other related rows but with different iso/translation
        DB::table('boxes')->where('name', $event->getBox()->getAttribute('name'))->delete();
    }

    /**
     * @param Dispatcher $dispatcher
     */
    public function subscribe(Dispatcher $dispatcher): void
    {
        $dispatcher->listen(CreateBoxEvent::class, [$this, 'onBoxCreated']);
        $dispatcher->listen(UpdatedBoxEvent::class, [$this, 'onBoxUpdated']);
        $dispatcher->listen(DeleteBoxEvent::class, [$this, 'onBoxDeleted']);
    }

    public function failed($event, $exception)
    {
        throw $exception;
    }
}
