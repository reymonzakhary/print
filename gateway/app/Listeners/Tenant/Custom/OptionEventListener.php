<?php

namespace App\Listeners\Tenant\Custom;

use App\Events\Tenant\Custom\CreateOptionEvent;
use App\Events\Tenant\Custom\DeleteOptionEvent;
use App\Events\Tenant\Custom\UpdatedOptionEvent;
use App\Models\Tenants\Language;
use App\Models\Tenants\Option;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class OptionEventListener
{
    public function onOptionCreated(CreateOptionEvent $event): void
    {
        collect(Language::where('iso', '!=', app()->getLocale())->get())->map(function ($lang) use ($event) {
            $transition = collect($event->translation)->filter(fn($t) => $t['iso'] === $lang->iso)->first();

            Option::create([

                'name' => $transition['name'] ?? $event->option->name,
                'description' => $transition['description'] ?? $event->option->description,
                'box_id' => $event->option->box_id,
                'input_type' => $event->option?->input_type,
                'incremental_by' => $event->option->incremental_by,
                'min' => $event->option->min,
                'max' => $event->option->max,
                'width' => $event->option->width,
                'height' => $event->option->height,
                'length' => $event->option->length,
                'unit' => $event->option->unit,
                'single' => $event->option->single,
                'upto' => $event->option->upto,

                'margin_value' => $event->option->margin_value,
                'margin_type' => $event->option->margin_type,
                'discount_value' => $event->option->discount_value,
                'discount_type' => $event->option->discount_type,
                'properties' => $event->option->properties,
                'price' => $event->option->price?->amount(),
                'price_switch' => $event->option->price_switch,
                'sort' => $event->option->sort,
                'secure' => $event->option->secure,
                'parent_id' => $event->option->parent_id,
                'base_id' => $event->option->base_id,
                'row_id' => $event->option->id,
                'iso' => $lang->iso,
                'published' => $event->option->published,
                'created_by' => $event->option->created_by,
                'published_by' => $event->option->published_by,
                'published_at' => $event->option->published_at,
            ]);
        });
    }

    /**
     * handle update option with base id
     * @param $event
     */
    public function onOptionUpdated(UpdatedOptionEvent $event): void
    {
        $event->option->base_id = $event->option->parent?->base_id ?: $event->option->base_id;

        collect(Option::where(['row_id' => $event->option->row_id])->get()->except([$event->option->id]))->map(
            function ($cat) use ($event) {
                $transition = collect($event->translation)->filter(fn($t) => $t['iso'] === $cat->iso)->first();
                $cat->update([

                    'name' => optional($transition)['name'] ?? $cat->name,
                    'slug' => optional($transition)['name'] ? Str::slug(optional($transition)['name']) : $cat->slug,
                    'description' => optional($transition)['description'] ?? $cat->description,

                    'parent_id' => $event->option->parent_id,
                    'base_id' => $event->option->parent?->base_id ?? $event->option->row_id,

                    'input_type' => $event->option?->input_type,
                    'incremental_by' => $event->option->incremental_by,
                    'min' => $event->option->min,
                    'max' => $event->option->max,
                    'width' => $event->option->width,
                    'height' => $event->option->height,
                    'length' => $event->option->length,
                    'unit' => $event->option->unit,
                    'single' => $event->option->single,
                    'upto' => $event->option->upto,
                    'properties' => $event->option->properties,
                    'margin_value' => $event->option->margin_value,
                    'margin_type' => $event->option->margin_type,
                    'discount_value' => $event->option->discount_value,
                    'discount_type' => $event->option->discount_type,

                    'price' => $event->option->price?->amount(),
                    'price_switch' => $event->option->price_switch,
                    'sort' => $event->option->sort,
                    'secure' => $event->option->secure,
                    'published' => $event->option->published,
                    'published_by' => $event->option->published_by,
                    'published_at' => $event->option->published_at,
                ]);
            }
        );

        $event->option->save();
    }

    public function onOptionDeleted(DeleteOptionEvent $event): void
    {
        # Delete other related rows but with different iso/translation
        DB::table('options')->where('name', $event->getOption()->getAttribute('name'))->delete();
    }

    /**
     * @param Dispatcher $dispatcher
     */
    public function subscribe(Dispatcher $dispatcher): void
    {
        $dispatcher->listen(CreateOptionEvent::class, [$this, 'onOptionCreated']);
        $dispatcher->listen(UpdatedOptionEvent::class, [$this, 'onOptionUpdated']);
        $dispatcher->listen(DeleteOptionEvent::class, [$this, 'onOptionDeleted']);
    }

    public function failed($event, $exception)
    {
        throw $exception;
    }
}
