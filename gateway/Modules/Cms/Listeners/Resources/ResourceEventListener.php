<?php

namespace Modules\Cms\Listeners\Resources;

use App\Models\Tenants\Language;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Cms\Entities\Resource;
use Modules\Cms\Enums\BlockKeysEnum;
use Modules\Cms\Enums\BlockTypesEnum;
use Modules\Cms\Enums\ProductTypesEnum;
use Modules\Cms\Events\Resources\CreateResourceEvent;
use Modules\Cms\Events\Resources\DeleteResourceEvent;
use Modules\Cms\Events\Resources\LockResourceEvent;
use Modules\Cms\Events\Resources\UpdateResourceEvent;

class ResourceEventListener implements ShouldQueue
{
    /**
     *
     */
    public function onResourceCreate(
        $event
    )
    {
        $event->resource->created_by = $event->user->id;
        if ($event->resource->published) {
            $event->resource->published_by = $event->user->id;
            $event->resource->published_on = Carbon::now();
        }
        $base_id = $event->resource->base_id;
        /**
         * creating a slug as default
         */

        $event->resource->resource_id = $event->resource->id;
        $event->resource->language = $event->language->iso;
        $event->resource->save();
        $languages = Language::where('iso', '!=', $event->language->iso)->get()->pluck('iso');
        $dataToStore = $event->resource->toArray();
        unset($dataToStore['id']);
        foreach ($languages as $language) {
            $dataToStore['language'] = $language;
            $dataToStore['base_id'] = $base_id;
            $dataToStore['uri'] = $event->resource->uri;
            Resource::create($dataToStore);
        }
        if ($event->resource->parent) {
            $parentInAllLocales = Resource::where([['resource_id', $event->resource->parent_id]])->get();
            foreach ($parentInAllLocales as $parent) {
                $parent->isfolder = true;
//                $parent->uri = str_replace(".html",'',$parent->uri);
                $parent->save();
            }
        }
    }


    public function onResourceUpdate(
        $event
    )
    {
        $event->resource->updated_by = $event->user->id;
        $uri = explode('/', $event->resource->uri);
        $index = count($uri) - 1;
        $uri[$index] = $event->resource->slug;
        $uri = implode('/', $uri);
        $event->resource->uri = $uri;
    
        $children = $event->resource->children()->where('language', $event->resource->language)->get();
        if ($children->count() > 0) {
            foreach ($children as $children) {
                $uri = explode('/', $children->uri);
                $uri[$index] = $event->resource->slug;
                $uri = implode('/', $uri);
                $children->update(['uri' => $uri]);/**/
            }
        }

        $resources = Resource::where([['resource_id', $event->resource->resource_id]])->get();

        foreach ($resources as $resource) {

            $resourceContent = $resource->content??[];
            if ($event->resource->resourceType->name == ProductTypesEnum::PRODUCT->value) {
                $resourceContent = array_filter($resourceContent, fn ($item) => ($item['key'] != BlockKeysEnum::BOOPS->value && $item['key'] != BlockKeysEnum::CATEGORY->value));

                $content = collect($event->resource->content)->filter(fn ($item) => ($item['key'] == BlockKeysEnum::BOOPS->value || $item['key'] == BlockKeysEnum::CATEGORY->value))->first();

                $resourceContent[] = [
                    'key' => is_numeric($content['value'])? BlockKeysEnum::CATEGORY->value: BlockKeysEnum::BOOPS->value,
                    'type' => BlockTypesEnum::CATEGORY->value,
                    'value' => $content['value'],
                ];

            } else {
                $resourceContent = collect($resourceContent)->filter(function ($item) {
                    return $item['key'] != BlockKeysEnum::CATEGORY->value;
                })->toArray();
            }
            $resource->content = $resourceContent;

            if ($event->resource->published) {
                $resource->published_by = $event->user->id;
                $resource->published_on = Carbon::now();
            } else {
                $resource->published_on = null;
                $resource->published_by = null;
            }

            if ($event->resource->parent) {
                $resource->isfolder = true;
            } else {
                $resource->isfolder = false;
            }
            $resource->sort = $event->resource->sort;
            $resource->parent_id = $event->resource->parent_id;
            $resource->published = $event->resource->published;
            $resource->hidden = $event->resource->hidden;
            $resource->searchable = $event->resource->searchable;
            $resource->cacheable = $event->resource->cacheable;
            $resource->resource_type_id = $event->resource->resource_type_id;
            $resource->category = $event->resource->category;
            $resource->template_id = $event->resource->template_id;
            $resource->hide_children_in_tree = $event->resource->hide_children_in_tree;

            if (!$event->resource->parent_id) {
                $resource->base_id = $event->resource->id;
            } elseif ($event->resource->parent) {
                $resource->base_id = $event->resource->parent->base_id;
            } else {
                $resource->base_id = $event->resource->parent_id;
            }

            $uri = optional($resource->parent)->uri;
            if ($uri) {
                $resource->uri = $uri . '/' . $resource->slug;
            } else {
                $resource->uri = '/' . $resource->slug;
            }
            $resource->save();
        }


    }

    /**
     * @param $event
     */
    public function onResourceRetrived(
        $event
    )
    {
        Resource::where('locked_by', $event->user->id)->update(['locked_by' => null]);
        if ($event->resource->locked_by === null) {
            $event->resource->locked_by = $event->user->id;
            $event->resource->save();
        }
    }

    /**
     * @param $event
     */
    public function onResourceDeleted(
        $event
    )
    {
        $resources = Resource::where('resource_id', $event->resource->id)->get();
        foreach ($resources as $resource) {
            $resource->deleted_by = $event->user->id;
            $resource->save();
            $resource->delete();
            $resource->children()->get()->map(function ($child) use ($event) {
                $child->deleted_by = $event->user->id;
                $child->save();
                $child->delete();
            });
        }

    }

    /**
     * @param $events
     */
    public function subscribe($events): void
    {
        $events->listen(
            CreateResourceEvent::class,
            'Modules\Cms\Listeners\Resources\ResourceEventListener@onResourceCreate'
        );

        $events->listen(
            UpdateResourceEvent::class,
            'Modules\Cms\Listeners\Resources\ResourceEventListener@onResourceUpdate'
        );

        $events->listen(
            LockResourceEvent::class,
            'Modules\Cms\Listeners\Resources\ResourceEventListener@onResourceRetrived'
        );

        $events->listen(
            DeleteResourceEvent::class,
            'Modules\Cms\Listeners\Resources\ResourceEventListener@onResourceDeleted'
        );
    }
}
