<?php

namespace App\Observers\Products;

use App\Models\Tenant\Variation;

class VariationObserver
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
     * Handle the Variation "saving" event.
     * @param Variation $Variation
     * @return void
     */
    public function saving(Variation $Variation)
    {
        dump('saving');
    }

    /**
     * Handle the Variation "saved" event.
     * @param Variation $Variation
     * @return void
     */
    public function saved(Variation $Variation)
    {
        dump('saved');
    }

    /**
     * Handle the Variation "creating" event.
     * @param Variation $Variation
     * @return void
     */
    public function creating(Variation $Variation)
    {
        dump('creating');
    }

    /**
     * Handle the Variation "created" event.
     *
     * @param Variation $Variation
     * @return void
     */
    public function created(Variation $Variation)
    {
        dump('created');
    }

    /**
     * Handle the Variation "updating" event.
     * @param Variation $Variation
     * @return void
     */
    public function updating(Variation $Variation)
    {
        dump('updating');
    }

    /**
     * Handle the Variation "updated" event.
     *
     * @param Variation $Variation
     * @return void
     */
    public function updated(Variation $Variation)
    {
        dump('updated');
    }

    /**
     * Handle the Variation "deleted" event.
     *
     * @param Variation $Variation
     * @return void
     */
    public function deleted(Variation $Variation)
    {
        //
    }

    /**
     * Handle the Variation "restored" event.
     *
     * @param Variation $Variation
     * @return void
     */
    public function restored(Variation $Variation)
    {
    }

    /**
     * Handle the Variation "force deleted" event.
     *
     * @param Variation $Variation
     * @return void
     */
    public function forceDeleted(Variation $Variation)
    {
    }
}
