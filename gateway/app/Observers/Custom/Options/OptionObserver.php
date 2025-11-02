<?php

namespace App\Observers\Custom\Options;

use App\Models\Tenants\Option;

class OptionObserver
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
     * Handle the Option "saving" event.
     * @param Option $option
     * @return void
     */
    public function saving(Option $option)
    {
//        dump('saving');
    }

    /**
     * Handle the Option "saved" event.
     * @param Option $option
     * @return void
     */
    public function saved(Option $option)
    {
//        dump('saved');
    }

    /**
     * Handle the Option "creating" event.
     * @param Option $option
     * @return void
     */
    public function creating(Option $option)
    {

    }

    /**
     * Handle the Option "created" event.
     *
     * @param Option $option
     * @return void
     */
    public function created(Option $option)
    {
        /**
         * creating a slug as default
         */
        $r = $option;

        if (
            optional($r->parent)->id === null ||
            optional($r->parent)->id === $r->id
        ) {
            $r->parent_id = null;
        }

        if (!$option->row_id) {
            $option->row_id = $option->id;
        }

        while (
            ($r->parent !== null && optional($r->parent)->id !== $r->id) ||
            optional($r->parent)->id !== null
        ) {
            $r = $r->parent;
        }

        if (!$option->base_id) {
            $option->base_id = $r->id;
        }

        $option->save();
    }

    /**
     * Handle the Option "updating" event.
     * @param Option $option
     * @return void
     */
    public function updating(Option $option)
    {
//        dump('updating');
    }

    /**
     * Handle the Option "updated" event.
     *
     * @param Option $option
     * @return void
     */
    public function updated(Option $option)
    {
        /**
         * creating a slug as default
         */
    }

    /**
     * Handle the Option "deleted" event.
     *
     * @param Option $option
     * @return void
     */
    public function deleted(Option $option)
    {
        //
    }

    /**
     * Handle the Option "restored" event.
     *
     * @param Option $option
     * @return void
     */
    public function restored(Option $option)
    {
    }

    /**
     * Handle the Option "force deleted" event.
     *
     * @param Option $option
     * @return void
     */
    public function forceDeleted(Option $option)
    {
    }
}
