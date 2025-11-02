<?php


namespace Modules\Cms\Observers\Resource;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Cms\Entities\Resource;
use App\Models\Tenants\Media\FileManager;
use Modules\Cms\Enums\BlockTypesEnum;

class ResourceObserver
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
     * @param Resource $resource
     * @return mixed
     */
    public function retrieved(Resource $resource)
    {
        return Cache::remember(tenant()->uuid.'.resource.'.$resource->id, 1440, function () use ($resource) {
            return $resource;
        });
    }

    /**
     * Handle the order "saving" event.
     * @param Resource $resource
     * @return void
     */
    public function saving(Resource $resource)
    {

    }

    /**
     * Handle the order "saved" event.
     * @param Resource $resource
     * @return void
     */
    public function saved(Resource $resource)
    {
    }

    /**
     * Handle the order "creating" event.
     * @param Resource $resource
     * @return void
     */
    public function creating(Resource $resource)
    {

//        $resource->created_by = Auth::user()->id;

        /**
         * creating a slug as default
         */
        $resource->slug = Str::slug($resource->title);

        /**
         * check if parent has send it
         * and not matching the roles proccess
         */
        if (
            $resource->count() === 0 ||
            optional($resource->parent)->id === null
        ) {
            $resource->parent_id = null;
        }
    }

    /**
     * Handle the order "created" event.
     *
     * @param Resource $resource
     * @return void
     */
    public function created(Resource $resource)
    {
        /**
         * creating a slug as default
         */
        $r = $resource;

        if (
            optional($r->parent)->id === null ||
            optional($r->parent)->id === $r->id
        ) {
            $r->parent_id = null;
        }

        while (
            ($r->parent !== null && optional($r->parent)->id !== $r->id) ||
            optional($r->parent)->id !== null
        ) {
            $r = $r->parent;
        }

        if (!$resource->base_id) {
            $resource->base_id = $r->resource_id??$r->id;
        }

        $uri = optional($resource->parent)->uri;
        if (!$resource->uri) {
            $resource->uri = '/' . $resource->slug;
        }

        if ($uri) {
            $resource->uri = $uri . '/' . $resource->slug;
        }
        $resource->save();

        return Cache::remember(tenant()->uuid.'.resource.'.$resource->id, 1440, function () use ($resource) {
            return $resource;
        });
    }

    /**
     * Handle the order "updating" event.
     * @param Resource $resource
     * @return void
     */
    public function updating(Resource $resource)
    {
//        dd($resource);
//        $resource->updated_by = Auth::user()->id;
    }

    /**
     * Handle the order "updated" event.
     *
     * @param Resource $resource
     * @return void
     */
    public function updated(Resource $resource)
    {
        Cache::forget(tenant()->uuid.'.resource.'.$resource->id);
        return Cache::remember(tenant()->uuid.'.resource.'.$resource->id, 1440, function () use ($resource) {
            return $resource;
        });
    }

    /**
     * Handle the order "deleted" event.
     *
     * @param Resource $resource
     * @return void
     */
    public function deleted(Resource $resource)
    {
        Cache::forget(tenant()->uuid.'.resource.'.$resource->id);
    }

    /**
     * Handle the order "restored" event.
     *
     * @param Resource $resource
     * @return void
     */
    public function restored(Resource $resource)
    {
    }

    /**
     * Handle the order "force deleted" event.
     *
     * @param Resource $resource
     * @return void
     */
    public function forceDeleted(Resource $resource)
    {
    }
}
