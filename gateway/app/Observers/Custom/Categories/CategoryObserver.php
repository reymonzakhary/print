<?php

namespace App\Observers\Custom\Categories;

use App\Models\Tenants\Category;

class CategoryObserver
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
     * Handle the Category "saving" event.
     * @param Category $category
     * @return void
     */
    public function saving(Category $category)
    {
//        dump('saving');
    }

    /**
     * Handle the Category "saved" event.
     * @param Category $category
     * @return void
     */
    public function saved(Category $category)
    {
//        dump('saved');
    }

    /**
     * Handle the Category "creating" event.
     * @param Category $category
     * @return void
     */
    public function creating(Category $category)
    {

    }

    /**
     * Handle the Category "created" event.
     *
     * @param Category $category
     * @return void
     */
    public function created(Category $category)
    {
        /**
         * creating a slug as default
         */
        $r = $category;

        if (
            optional($r->parent)->id === null ||
            optional($r->parent)->id === $r->id
        ) {
            $r->parent_id = null;
        }

        if (!$category->row_id) {
            $category->row_id = $category->id;
        }

        while (
            ($r->parent !== null && optional($r->parent)->id !== $r->id) ||
            optional($r->parent)->id !== null
        ) {
            $r = $r->parent;
        }

        if (!$category->base_id) {
            $category->base_id = $r->id;
        }

        $category->save();
    }

    /**
     * Handle the Category "updating" event.
     * @param Category $category
     * @return void
     */
    public function updating(Category $category)
    {
//        dump('updating');
    }

    /**
     * Handle the Category "updated" event.
     *
     * @param Category $category
     * @return void
     */
    public function updated(Category $category)
    {
        /**
         * creating a slug as default
         */
    }

    /**
     * Handle the Category "deleted" event.
     *
     * @param Category $category
     * @return void
     */
    public function deleted(Category $category)
    {
        //
    }

    /**
     * Handle the Category "restored" event.
     *
     * @param Category $category
     * @return void
     */
    public function restored(Category $category)
    {
    }

    /**
     * Handle the Category "force deleted" event.
     *
     * @param Category $category
     * @return void
     */
    public function forceDeleted(Category $category)
    {
    }
}
