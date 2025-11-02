<?php

namespace App\Listeners\Tenant\Custom;

use App\Events\Tenant\Custom\CreateProductCombinationWithOutStockEvent;
use App\Events\Tenant\Custom\CreateProductCombinationWithStockEvent;
use App\Events\Tenant\Custom\CreateProductEvent;
use App\Events\Tenant\Custom\CreateProductVariationWithOutStockEvent;
use App\Events\Tenant\Custom\CreateProductVariationWithStockEvent;
use App\Events\Tenant\Custom\CreateProductWithOutStockEvent;
use App\Events\Tenant\Custom\CreateProductWithStockEvent;
use App\Events\Tenant\Custom\UpdatedProductEvent;
use App\Events\Tenant\Custom\UpdateProductExcludesUpdated;
use App\Events\Tenant\Custom\UpdateProductSkuUpdated;
use App\Models\Tenants\Box;
use App\Models\Tenants\Language;
use App\Models\Tenants\Option;
use App\Models\Tenants\Product;
use App\Models\Tenants\Sku;
use App\Models\Tenants\Variation;
use Illuminate\Support\Str;
use function BenTools\CartesianProduct\cartesian_product;

class CustomProductEventListener
{
    /**
     * on product created
     */
    public function onCustomProductCreated($event)
    {
        collect(Language::where('iso', '!=', Str::lower(app()->getLocale()))->get())->map(function ($lang) use ($event) {
            $transition = collect($event->translation)->filter(fn($t) => $t['iso'] === $lang->iso)->first();
            Product::create([
                'row_id' => $event->product->id,
                'name' => optional($transition)['name'] ?? $event->product->name,
                'description' => optional($transition)['description'] ?? $event->product->description,
                'iso' => $lang->iso,
                'free' => $event->product->free,
                'properties' => $event->product->properties,
                'sale_start_at' => $event->product->sale_start_at,
                'sale_end_at' => $event->product->sale_end_at,
                'brand_id' => $event->product->brand_id,
                'category_id' => $event->product->category_id,
                'art_num' => $event->product->art_num,
                'sort' => $event->product->sort,
                'excludes' => $event->product->excludes,
                'stock_product' => $event->product->stock_product,
                'variation' => $event->product->variation,
                'combination' => $event->product->combination,
                'unit_id' => $event->product->unit_id,
                'vat_id' => $event->product->vat_id,
                'margin_value' => $event->product->margin_value,
                'margin_type' => $event->product->margin_type,
                'discount_value' => $event->product->discount_value,
                'discount_type' => $event->product->discount_type,
                'created_by' => $event->product->created_by,
                'expire_date' => $event->product->expire_date,
                'expire_after' => $event->product->expire_after,
                'published' => $event->product->published,
                'published_by' => $event->product->published_by,
                'published_at' => $event->product->published_at,

            ]);
        });
    }

    /**
     * handle update product with base id
     * @param $event
     */
    public function onCustomProductUpdated($event)
    {
        if (!$event->product->variation) {
            $event->product->deleteWithVariations();
        }


        collect(
            Product::where(['row_id' => $event->product->row_id])->get()->except([$event->product->id])
        )->map(function ($product) use ($event) {
            unset($product->hasBlueprint);
            $product->update([
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'free' => $event->product->free,
                'properties' => $event->product->properties,
                'sale_start_at' => $event->product->sale_start_at,
                'sale_end_at' => $event->product->sale_end_at,
                'brand_id' => $event->product->brand_id,
                'category_id' => $event->product->category_id,
                'art_num' => $event->product->art_num,
                'sort' => $event->product->sort,
                'stock_product' => $event->product->stock_product,
                'variation' => $event->product->variation,
                'excludes' => $event->product->excludes,
                'combination' => $event->product->combination,
                'low_qty_threshold' => $event->product->low_qty_threshold,
                'unit_id' => $event->product->unit_id,
                'vat_id' => $event->product->vat_id,
                'margin_value' => $event->product->margin_value,
                'margin_type' => $event->product->margin_type,
                'discount_value' => $event->product->discount_value,
                'discount_type' => $event->product->discount_type,
                'created_by' => $event->product->created_by,
                'published' => $event->product->published,
                'published_by' => $event->product->published_by,
                'published_at' => $event->product->published_at,
                'expire_date' => $event->product->expire_date,
                'expire_after' => $event->product->expire_after,
            ]);
        });
        $event->product->save();
    }

    public function onCustomProductWithStockUpdated($event)
    {
        $event->product->sku()->update(
            collect($event->request)->only([
                'price',
                'open_stock',
                'low_qty_threshold',
                'high_qty_threshold',
            ])->toArray()
        );

    }

    /**
     * on product created as single without stock
     */
    public function onProductWithOutStockCreated($event)
    {
        $sku = $event->product->sku()->create([
            'price' => optional($event->request)['price'],
            'open_stock' => null,
            'low_qty_threshold' => null,
            'high_qty_threshold' => null,
            'ean' => optional($event->request)['ean'] ?? ean13Generator()
        ]);

        collect(
            Product::where(['row_id' => $event->product->id])->get()->except([$event->product->id])
        )->map(function ($product) use ($event) {
            unset($product->hasBlueprint);
            $product->stock_product = false;
            $product->variation = false;
            $product->combination = false;
            $product->save();
        });
        if ($event->product->combination) {
            $skus = Sku::whereIn('id', collect($event->request['products'])->pluck('sku_id'))->get();
            $children = collect($skus)->map(function ($i) use ($sku) {
                return [
                    'product_id' => $i->product_id,
                    'parent_id' => $sku->id
                ];
            })->toArray();
            $sku->children()->insert($children);
        }

    }

    /**
     * on product created as single with stock
     */
    public function onProductWithStockCreated($event)
    {
        // handle product with stock
        $sku = $event->product->sku()->create([
            'ean' => optional($event->request)['ean'] ?? ean13Generator(),
            'price' => optional($event->request)['price'],
            'low_qty_threshold' => optional($event->request)['low_qty_threshold'],
            'high_qty_threshold' => optional($event->request)['high_qty_threshold'],
            'open_stock' => optional($event->request)['open_stock'],
        ]);
        $sku->stocks()->create($event->request['stock']);
        if ($event->product->combination) {
            $skus = Sku::whereIn('id', collect($event->request['products'])->pluck('sku_id'))->get();
            $children = collect($skus)->map(function ($i) use ($sku) {
                return [
                    'product_id' => $i->product_id,
                    'parent_id' => $sku->id
                ];
            })->toArray();
            $sku->children()->insert($children);
        }
    }

    /**
     * on product created as single without stock
     */
    public function onProductVariationWithOutStockCreated($event)
    {
        $event->product->sku()->create([
            'price' => optional($event->request)['price'],
            'low_qty_threshold' => optional($event->request)['low_qty_threshold'],
            'high_qty_threshold' => optional($event->request)['high_qty_threshold'],
            'open_stock' => optional($event->request)['open_stock'],
            'ean' => optional($event->request)['ean'] ?? ean13Generator()
        ]);
        $variations = collect($event->product->variations);
        $requestVariations = [];
        collect($event->request['variations'])->map(function ($variation) use ($event, $variations, &$requestVariations) {

            $options = Option::whereIn('row_id', collect($variation['options'])->pluck('id'))->get();
            $box = Box::where('row_id', $variation['id'])->first();


            collect($variation['options'])->map(function ($option) use ($event, $variation, $options, $box, $variations, &$requestVariations) {
                $current_option = $options->where('row_id', $option['id'])->first();
                $requestVariations[] = [
                    'box_id' => $box->row_id,
                    'option_id' => $option['id']
                ];
                $current = $variations->where('box_id', $box->row_id)->where('option_id', $option['id']);
                if ($current->count()) {
                    $this->updateVariation($event, $current->first(), $option, $box, $current_option);
                } else {
                    $this->createVariation($event, $variation, $option, $box, $current_option);
                }
            });
        });
        $requestVariations = collect($requestVariations);
        $variations->map(function ($i) use ($requestVariations) {
            if (
                !$requestVariations
                    ->where('box_id', $i->box_id)
                    ->where('option_id', $i->option_id)
                    ->count()
            ) {
                $i->delete();
            }

        });

    }

    public function onProductExcludesUpdated($event)
    {
        $query = $event->product->variations();
        $query->get()->map(function ($variation) {
            $variation?->sku()->first()?->stocks()?->delete();
            $variation?->sku()->delete();
        });
        $variations = $query->get()->unique("option_id");
        $query->update([
            'sku' => null,
            'sku_id' => null,
            'parent_id' => null
        ]);

        $query->whereNotIn('id', $variations->pluck('id'))->get()->map(function ($variation) {
            $variation?->media()->delete();
            $variation->delete();
        });

        $event->product->sku()->create([
            'price' => optional($event->request)['price'],
            'low_qty_threshold' => optional($event->request)['low_qty_threshold'],
            'high_qty_threshold' => optional($event->request)['high_qty_threshold'],
            'open_stock' => optional($event->request)['open_stock'],
            'ean' => optional($event->request)['ean'] ?? ean13Generator()
        ]);
    }

    /**
     * on product created as single with stock
     */
    public function onProductVariationWithStockCreated($event)
    {
        // @todo handle stock for product and options
        $event->product->sku()->create([
            'price' => optional($event->request)['price'],
            'low_qty_threshold' => optional($event->request)['low_qty_threshold'],
            'high_qty_threshold' => optional($event->request)['high_qty_threshold'],
            'open_stock' => optional($event->request)['open_stock'],
            'ean' => optional($event->request)['ean'] ?? ean13Generator()
        ]);

        $variations = collect($event->product->variations);
        $requestVariations = [];
        collect($event->request['variations'])->map(function ($variation) use ($event, $variations, &$requestVariations) {

            $options = Option::whereIn('id', collect($variation['options'])->pluck('id'))->get();
            $box = Box::where('id', $variation['id'])->first();


            collect($variation['options'])->map(function ($option) use ($event, $variation, $options, $box, $variations, &$requestVariations) {
                $current_option = $options->where('id', $option['id'])->first();
                $requestVariations[] = [
                    'box_id' => $box->id,
                    'option_id' => $option['id']
                ];
                $current = $variations->where('box_id', $box->id)->where('option_id', $option['id']);
                if ($current->count()) {
                    $this->updateVariation($event, $current->first(), $option, $box, $current_option);
                } else {
                    $this->createVariation($event, $variation, $option, $box, $current_option);
                }
            });
        });
        $requestVariations = collect($requestVariations);
        $variations->map(function ($i) use ($requestVariations) {
            if (
                !$requestVariations
                    ->where('box_id', $i->box_id)
                    ->where('option_id', $i->option_id)
                    ->count()
            ) {
                $i->delete();
            }

        });
        $event->product->sku()->first()->stocks()->create($event->request['stock']);

    }

    /**
     * on product created as single without stock
     */
    public function onProductCombinationWithOutStockCreated($event)
    {
        $formattedVariations = collect($event->request['variations'])
            ->reject(function ($variation) use ($event) {
                if ((bool)$variation['appendage']) {
                    collect($variation['options'])->map(function ($option) use ($event, $variation) {
                        $parent = $event->product->variations()->firstOrCreate([
                            'appendage' => $variation['appendage'],
                            'box_id' => $variation['id'],
                            'option_id' => $option['id'],
                            'price' => optional($option)['price'],
                            'properties' => optional($option)['properties'],
                        ]);

                        collect(optional($option)['child'])->map(fn($option) => Variation::firstOrCreate([
                            'parent_id' => $parent->id,
                            'appendage' => $variation['appendage'],
                            'box_id' => $variation['id'],
                            'option_id' => $option['id'],
                            'price' => optional($option)['price'],
                            'properties' => optional($option)['properties'],
                            'child' => true,
                        ])
                        );
                    });
                }
                return $variation['appendage'] === true;
            })
            ->mapWithKeys(function ($variation) {
                return [
                    $variation['id'] => array_key_exists('options', $variation) ? array_column($variation['options'], 'id') : []
                ];
            })->toArray();
        collect(cartesian_product($formattedVariations)->asArray())->each(
            function ($combination) use ($event) {
                $sku = $event->product->sku()->create([
                    'price' => optional($event->request)['price'],
                    'low_qty_threshold' => optional($event->request)['low_qty_threshold'],
                    'high_qty_threshold' => optional($event->request)['high_qty_threshold'],
                    'open_stock' => optional($event->request)['open_stock'],
                    'ean' => optional($event->request)['ean'] ?? ean13Generator()
                ]);

                $parent = null;
                foreach ($combination as $box => $option) {
                    $s = next($combination) ? null : $sku;
                    $parent = $event->product->variations()->firstOrCreate([
                        'sku' => $s?->sku,
                        'sku_id' => $s?->id,
                        'box_id' => $box,
                        'option_id' => $option,
                        'published' => 1,
                        'price' => optional($option)['price'],
                        'properties' => optional($option)['properties'],
                        'parent_id' => $parent?->id
                    ]);
                }
            }
        );
    }

    /**
     * on product created as single without stock
     */
    public function onProductCombinationWithStockCreated($event)
    {
        $formattedVariations = collect($event->request['variations'])
            ->reject(function ($variation) use ($event) {
                if ((bool)$variation['appendage']) {
                    collect($variation['options'])->map(function ($option) use ($event, $variation) {
                        $parent = $event->product->variations()->firstOrCreate([
                            'appendage' => $variation['appendage'],
                            'box_id' => $variation['id'],
                            'price' => optional($option)['price'],
                            'properties' => optional($option)['properties'],
                            'option_id' => $option['id']
                        ]);

                        collect(optional($option)['child'])->map(fn($option) => Variation::firstOrCreate([
                            'parent_id' => $parent->id,
                            'appendage' => $variation['appendage'],
                            'box_id' => $variation['id'],
                            'price' => optional($option)['price'],
                            'properties' => optional($option)['properties'],
                            'option_id' => $option['id'],
                            'child' => true,
                        ])
                        );
                    });
                }

                return $variation['appendage'] === true;
            })
            ->mapWithKeys(fn($variation) => [
                $variation['id'] => array_key_exists('options', $variation) ? array_column($variation['options'], 'id') : []
            ])->toArray();


        collect(cartesian_product($formattedVariations)->asArray())->each(
            function ($combination) use ($event) {
                $sku = $event->product->sku()->create([
                    'price' => optional($event->request)['price'],
                    'low_qty_threshold' => optional($event->request)['low_qty_threshold'],
                    'high_qty_threshold' => optional($event->request)['high_qty_threshold'],
                    'open_stock' => optional($event->request)['open_stock'],
                    'ean' => optional($event->request)['ean'] ?? ean13Generator()
                ]);

                $parent = null;
                foreach ($combination as $box => $option) {

                    $s = next($combination) ? null : $sku;
                    $parent = $event->product->variations()->firstOrCreate([
                        'sku' => $s?->sku,
                        'sku_id' => $s?->id,
                        'box_id' => $box,
                        'option_id' => $option,
                        'price' => optional($option)['price'],
                        'properties' => optional($option)['properties'],
                        'published' => 1,
                        'parent_id' => $parent?->id
                    ]);
                }
            }
        );
    }

    /**
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            CreateProductEvent::class,
            'App\Listeners\Tenant\Custom\CustomProductEventListener@onCustomProductCreated'
        );
        $events->listen(
            CreateProductWithOutStockEvent::class,
            'App\Listeners\Tenant\Custom\CustomProductEventListener@onProductWithOutStockCreated'
        );
        $events->listen(
            CreateProductWithStockEvent::class,
            'App\Listeners\Tenant\Custom\CustomProductEventListener@onProductWithStockCreated'
        );
        $events->listen(
            CreateProductVariationWithOutStockEvent::class,
            'App\Listeners\Tenant\Custom\CustomProductEventListener@onProductVariationWithOutStockCreated'
        );
        $events->listen(
            CreateProductVariationWithStockEvent::class,
            'App\Listeners\Tenant\Custom\CustomProductEventListener@onProductVariationWithStockCreated'
        );
        $events->listen(
            CreateProductCombinationWithOutStockEvent::class,
            'App\Listeners\Tenant\Custom\CustomProductEventListener@onProductCombinationWithOutStockCreated'
        );
        $events->listen(
            CreateProductCombinationWithStockEvent::class,
            'App\Listeners\Tenant\Custom\CustomProductEventListener@onProductCombinationWithStockCreated'
        );
        $events->listen(
            UpdatedProductEvent::class,
            'App\Listeners\Tenant\Custom\CustomProductEventListener@onCustomProductUpdated'
        );
        $events->listen(
            UpdateProductSkuUpdated::class,
            'App\Listeners\Tenant\Custom\CustomProductEventListener@onCustomProductWithStockUpdated'
        );
        $events->listen(
            UpdateProductExcludesUpdated::class,
            'App\Listeners\Tenant\Custom\CustomProductEventListener@onProductExcludesUpdated'
        );
    }

    public function failed($event, $exception)
    {
        dd($exception);
    }

    private function updateVariation($event, $variation, $option, $box, $current_option)
    {
        $variation->update([
            'single' => $current_option->single,
            'upto' => $current_option->upto,
            'mime_type' => $current_option->mime_type,
            'price' => optional($option)['price'],
            'override' => (bool)optional($option)['price'],
            'properties' => optional($option)['properties'],
            'box_id' => $box->id,
            'incremental' => $box->incremental,
            'incremental_by' => optional($option)['incremental_by'] ?? $current_option->incremental_by,
            'input_type' => $box->input_type,
            'appendage' => optional($option)['appendage'] ?? $box->appendage,
            'default_selected' => optional($option)['default_selected'],
            'switch_price' => (bool)optional($option)['switch_price'],
            'option_id' => $current_option->id,
            'expire_date' => optional($option)['expire_date'],
            'expire_after' => optional($option)['expire_after']
        ]);
    }

    private function createVariation($event, $variation, $option, $box, $current_option)
    {
        $event->product->variations()->create([
            'single' => $current_option->single,
            'upto' => $current_option->upto,
            'mime_type' => $current_option->mime_type,
            'price' => optional($option)['price'],
            'override' => (bool)optional($option)['price'],
            'properties' => optional($option)['properties'],
            'box_id' => $variation['id'],
            'incremental' => $box->incremental,
            'incremental_by' => optional($option)['incremental_by'] ?? $current_option->incremental_by,
            'input_type' => $box->input_type,
            'appendage' => optional($option)['appendage'] ?? $box->appendage,
            'default_selected' => optional($option)['default_selected'],
            'switch_price' => (bool)optional($option)['switch_price'],
            'option_id' => $option['id'],
            'expire_date' => optional($option)['expire_date'],
            'expire_after' => optional($option)['expire_after']
        ]);
    }
}
