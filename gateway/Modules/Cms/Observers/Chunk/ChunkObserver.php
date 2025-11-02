<?php

namespace Modules\Cms\Observers\Chunk;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Cms\Entities\Chunk;

class ChunkObserver
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
     * @param Chunk $chunk
     * @return mixed
     */
    public function retrieved(Chunk $chunk): mixed
    {
        return Cache::remember(tenant()->uuid.'.chunk.'.$chunk->name, 1440, function () use ($chunk) {
            return $chunk;
        });
    }

    /**
     * Handle the order "saving" event.
     * @param Chunk $chunk
     * @return void
     */
    public function saving(Chunk $chunk){}

    /**
     * Handle the order "saved" event.
     * @param Chunk $chunk
     * @return void
     */
    public function saved(Chunk $chunk){}

    /**
     * Handle the order "creating" event.
     * @param Chunk $chunk
     * @return void
     */
    public function creating(Chunk $chunk) {}

    /**
     * Handle the order "created" event.
     *
     * @param Chunk $chunk
     * @return void
     */
    public function created(Chunk $chunk)
    {
        return Cache::remember(tenant()->uuid.'.chunk.'.$chunk->name, 1440, function () use ($chunk) {
            return $chunk;
        });
    }

    /**
     * Handle the order "updating" event.
     * @param Chunk $chunk
     * @return void
     */
    public function updating(Chunk $chunk)
    {

    }

    /**
     * Handle the order "updated" event.
     *
     * @param Chunk $chunk
     * @return void
     */
    public function updated(Chunk $chunk)
    {
        Cache::forget(tenant()->uuid.'.chunk.'.$chunk->name);
        return Cache::remember(tenant()->uuid.'.chunk.'.$chunk->name, 1440, function () use ($chunk) {
            return $chunk;
        });
    }

    /**
     * Handle the order "deleted" event.
     *
     * @param Chunk $chunk
     * @return void
     */
    public function deleted(Chunk $chunk)
    {
        Cache::forget(tenant()->uuid.'.chunk.'.$chunk->name);
    }

    /**
     * Handle the order "restored" event.
     *
     * @param Chunk $chunk
     * @return void
     */
    public function restored(Chunk $chunk)
    {
    }

    /**
     * Handle the order "force deleted" event.
     *
     * @param Chunk $chunk
     * @return void
     */
    public function forceDeleted(Chunk $chunk)
    {
    }
}
