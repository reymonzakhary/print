<?php

namespace App\Observers\Blueprints;

use App\Models\Tenants\Blueprint;

class BlueprintObserver
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
     * @param Blueprint $blueprint
     */
    public function retrieved(Blueprint $blueprint)
    {}

    /**
     * Handle the Blueprint "saving" event.
     * @param Blueprint $blueprint
     * @return void
     */
    public function saving(Blueprint $blueprint){}

    /**
     * Handle the Blueprint "saved" event.
     * @param Blueprint $blueprint
     * @return void
     */
    public function saved(Blueprint $blueprint){}

    /**
     * Handle the Blueprint "creating" event.
     * @param Blueprint $blueprint
     * @return void
     */
    public function creating(Blueprint $blueprint){
        /**
         * must be fixed
         */
//        $blueprint->created_by = auth()->user()->id;
    }

    /**
     * Handle the Blueprint "created" event.
     *
     * @param Blueprint $blueprint
     * @return void
     */
    public function created(Blueprint $blueprint){}

    /**
     * Handle the Blueprint "updating" event.
     * @param Blueprint $blueprint
     * @return void
     */
    public function updating(Blueprint $blueprint)
    {
        $blueprint->updated_by = auth()->user()->id;
    }

    /**
     * Handle the Blueprint "updated" event.
     *
     * @param Blueprint $blueprint
     * @return void
     */
    public function updated(Blueprint $blueprint){}

    /**
     * Handle the Blueprint "deleted" event.
     *
     * @param Blueprint $blueprint
     * @return void
     */
    public function deleted(Blueprint $blueprint){}

    /**
     * Handle the Blueprint "restored" event.
     *
     * @param Blueprint $blueprint
     * @return void
     */
    public function restored(Blueprint $blueprint){}

    /**
     * Handle the Blueprint "force deleted" event.
     *
     * @param Blueprint $blueprint
     * @return void
     */
    public function forceDeleted(Blueprint $blueprint){}
}
