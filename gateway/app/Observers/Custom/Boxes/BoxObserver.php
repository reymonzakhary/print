<?php

namespace App\Observers\Custom\Boxes;

use App\Models\Tenant\Box;

class BoxObserver
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
     * Handle the Box "saving" event.
     * @param Box $box
     * @return void
     */
    public function saving(Box $box)
    {
//        dump('saving');
    }

    /**
     * Handle the Box "saved" event.
     * @param Box $box
     * @return void
     */
    public function saved(Box $box)
    {
//        dump('saved');
    }

    /**
     * Handle the Box "creating" event.
     * @param Box $box
     * @return void
     */
    public function creating(Box $box)
    {

    }

    /**
     * Handle the Box "created" event.
     *
     * @param Box $box
     * @return void
     */
    public function created(Box $box)
    {
        /**
         * creating a slug as default
         */
        $r = $box;

        if (
            optional($r->parent)->id === null ||
            optional($r->parent)->id === $r->id
        ) {
            $r->parent_id = null;
        }

        if (!$box->row_id) {
            $box->row_id = $box->id;
        }

        while (
            ($r->parent !== null && optional($r->parent)->id !== $r->id) ||
            optional($r->parent)->id !== null
        ) {
            $r = $r->parent;
        }

        if (!$box->base_id) {
            $box->base_id = $r->id;
        }

        $box->save();
    }

    /**
     * Handle the Box "updating" event.
     * @param Box $box
     * @return void
     */
    public function updating(Box $box)
    {
//        dump('updating');
    }

    /**
     * Handle the Box "updated" event.
     *
     * @param Box $box
     * @return void
     */
    public function updated(Box $box)
    {
        /**
         * creating a slug as default
         */
    }

    /**
     * Handle the Box "deleted" event.
     *
     * @param Box $box
     * @return void
     */
    public function deleted(Box $box)
    {
        //
    }

    /**
     * Handle the Box "restored" event.
     *
     * @param Box $box
     * @return void
     */
    public function restored(Box $box)
    {
    }

    /**
     * Handle the Box "force deleted" event.
     *
     * @param Box $box
     * @return void
     */
    public function forceDeleted(Box $box)
    {
    }
}
