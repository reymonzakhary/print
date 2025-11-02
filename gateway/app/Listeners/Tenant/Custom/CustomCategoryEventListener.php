<?php

declare(strict_types=1);

namespace App\Listeners\Tenant\Custom;

use App\Events\Tenant\Custom\CreateCategoryEvent;
use App\Events\Tenant\Custom\UpdatedCategoryEvent;
use App\Models\Tenants\Category;
use App\Models\Tenants\Language;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class CustomCategoryEventListener implements ShouldQueue
{
    /**
     * @param CreateCategoryEvent $event
     * @return void
     */
    public function onCustomCategoryCreated(
        CreateCategoryEvent $event
    ): void
    {
        Language::where('iso', '!=', app()->getLocale())->get()->map(
            function (Language $language) use ($event): void {
                $matchedTranslation = collect($event->translations)
                    ->filter(fn(array $translation) => $translation['iso'] === $language->getAttribute('iso'))
                    ->firstOrFail();

                Category::create([
                    'row_id' => $event->category->id,
                    'base_id' => $event->category->base_id,
                    'name' => optional($matchedTranslation)['name'] ?? $event->category->name,
                    'description' => optional($matchedTranslation)['description'] ?? $event->category->description,
                    'parent_id' => $event->category->parent_id,
                    'iso' => $language->iso,
                    'margin_value' => $event->category->margin_value,
                    'margin_type' => $event->category->margin_type,
                    'discount_value' => $event->category->discount_value,
                    'discount_type' => $event->category->discount_type,
                    'created_by' => $event->category->created_by,
                    'published' => $event->category->published,
                    'published_by' => $event->category->published_by,
                    'published_at' => $event->category->published_at,
                ]);
            }
        );
    }

    /**
     * handle update category with base id
     * @param UpdatedCategoryEvent $event
     */
    public function onCustomCategoryUpdated(
        UpdatedCategoryEvent $event
    ): void
    {
        $event->category->base_id = $event->category->parent?->base_id ?: $event->category->base_id;

        Category::where(['row_id' => $event->category->id])->get()->except([$event->category->id])->map(
            function (Category $category) use ($event): void {
                $matchedTranslation = collect($event->translations)
                    ->filter(fn(array $translation) => $translation['iso'] === $category->getAttribute('iso'))
                    ->firstOrFail();

                $category->update([
                    'name' => optional($matchedTranslation)['name'] ?? $category->name,
                    'slug' => optional($matchedTranslation)['name'] ?
                        Str::slug(optional($matchedTranslation)['name'])
                        : $category->slug,

                    'description' => optional($matchedTranslation)['description'] ?? $category->description,
                    'parent_id' => $event->category->parent_id,
                    'base_id' => $event->category->parent?->base_id,

                    'margin_value' => $event->category->margin_value,
                    'margin_type' => $event->category->margin_type,
                    'discount_value' => $event->category->discount_value,
                    'discount_type' => $event->category->discount_type,

                    'published' => $event->category->published,
                    'published_by' => $event->category->published_by,
                    'published_at' => $event->category->published_at
                ]);
            }
        );

        $event->category->save();
    }

    /**
     * @param Dispatcher $dispatcher
     */
    public function subscribe(
        Dispatcher $dispatcher
    ): void
    {
        $dispatcher->listen(CreateCategoryEvent::class, [$this, 'onCustomCategoryCreated']);

        $dispatcher->listen(UpdatedCategoryEvent::class, [$this, 'onCustomCategoryUpdated']);
    }
}
