<?php

namespace App\Observers\Custom\Products;

use App\Models\Tenant\Product;

class ProductObserver
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
     *
     */
    public function retrieved(Product $product)
    {
        $blueprint = $product->blueprints;
        $product->hasBlueprint = (bool)$blueprint?->first();
    }

    /**
     * Handle the Product "saving" event.
     * @param Product $product
     * @return void
     */
    public function saving(Product $product)
    {
    }

    /**
     * Handle the Product "saved" event.
     * @param Product $product
     * @return void
     */
    public function saved(Product $product)
    {
//        dump('saved');
    }

    /**
     * Handle the Product "creating" event.
     * @param Product $product
     * @return void
     */
    public function creating(Product $product)
    {

    }

    /**
     * Handle the Product "created" event.
     *
     * @param Product $product
     * @return void
     */
    public function created(Product $product)
    {

        if (!$product->row_id) {
            $product->row_id = $product->id;
            $product->save();
        }

//        if($product->row_id === $product->id && !$product->variation) {
//            $product->sku()->create(request()->only(
//                'price', 'low_qty_threshold', 'high_qty_threshold','ean', 'open_stock'
//            ));
//        }


    }

    /**
     * Handle the Product "updating" event.
     * @param Product $product
     * @return void
     */
    public function updating(Product $product)
    {
        if (
            array_key_exists('stock_product', $product->getDirty())
            && request()?->product?->id
            && request()?->product?->id === $product?->id
            && !$product->variation
        ) {
            $sku = $product->sku()->first();
            if ($product->getDirty()["stock_product"]) {
                $sku->update(request()->only(
                    'price', 'low_qty_threshold', 'high_qty_threshold', 'open_stock'
                ));

                $sku->stocks()->create(request()->only(
                    'stock'
                )["stock"]);
            } else {
                $sku->stocks()->delete();
                $sku->update(array_merge([
                    'low_qty_threshold' => null,
                    'high_qty_threshold' => null,
                    'open_stock' => null,
                ], request()->only('price')));
            }

        }

    }

    /**
     * Handle the Product "updated" event.
     *
     * @param Product $product
     * @return void
     */
    public function updated(Product $product)
    {
    }

    /**
     * Handle the Product "deleted" event.
     *
     * @param Product $product
     * @return void
     */
    public function deleted(Product $product)
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     *
     * @param Product $product
     * @return void
     */
    public function restored(Product $product)
    {
    }

    /**
     * Handle the Product "force deleted" event.
     *
     * @param Product $product
     * @return void
     */
    public function forceDeleted(Product $product)
    {
    }
}
