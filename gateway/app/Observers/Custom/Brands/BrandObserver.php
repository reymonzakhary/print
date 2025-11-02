<?php

namespace App\Observers\Custom\Brands;

use App\Models\Tenants\Brand;

class BrandObserver
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
     * Handle the Brand "saving" event.
     * @param Brand $brand
     * @return void
     */
    public function saving(Brand $brand)
    {
//        dump('saving');
    }

    /**
     * Handle the Brand "saved" event.
     * @param Brand $brand
     * @return void
     */
    public function saved(Brand $brand)
    {
//        dump('saved');
    }

    /**
     * Handle the Brand "creating" event.
     * @param Brand $brand
     * @return void
     */
    public function creating(Brand $brand)
    {

    }

    /**
     * Handle the Brand "created" event.
     *
     * @param Brand $brand
     * @return void
     */
    public function created(Brand $brand)
    {

        if (!$brand->row_id) {
            $brand->row_id = $brand->id;
            $brand->save();
        }

    }

    /**
     * Handle the Brand "updating" event.
     * @param Brand $brand
     * @return void
     */
    public function updating(Brand $brand)
    {
//        dump('updating');
    }

    /**
     * Handle the Brand "updated" event.
     *
     * @param Brand $brand
     * @return void
     */
    public function updated(Brand $brand)
    {
        /**
         * creating a slug as default
         */
    }

    /**
     * Handle the Brand "deleted" event.
     *
     * @param Brand $brand
     * @return void
     */
    public function deleted(Brand $brand)
    {
        //
    }

    /**
     * Handle the Brand "restored" event.
     *
     * @param Brand $brand
     * @return void
     */
    public function restored(Brand $brand)
    {
    }

    /**
     * Handle the Brand "force deleted" event.
     *
     * @param Brand $brand
     * @return void
     */
    public function forceDeleted(Brand $brand)
    {
    }
}
