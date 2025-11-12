<?php

namespace App\Http\Resources\Shops;

use App\Models\Tenant\Box;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopProductSkuResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->product->name,
            'ean' => $this->ean,
            'sku' => $this->sku,
            'low_qty_threshold' => $this->low_qty_threshold,
            'price' => $this->price->amount(),
            'display_price' => $this->price->format(),
            'stock_count' => $this->stockCount(),
            'in_stock' => $this->inStock(),
            'published' => $this->published,
            'media' => collect($this->media)->map(fn($md) => $md->path . $md->name)->toArray(),
            'product' => ShopProductSkuVariationResource::collection(
                collect($this->variations)
                    ->map(fn($variation) => $variation->ancestorsAndSelf->sortBy('sort'))->flatten()
            ),
            'appendage' => collect($this->product->appendage->groupBy('box.name'))->map(function ($v, $k) {
                $v->load('option', 'option.children');
                return array_merge(
                    Box::where([['name', $k], ['iso', app()->getLocale()]])->first()->toArray(),
                    [
                        'options' => ShopVariationResource::collection($v->unique('option_id'))
                    ]
                );
            })->values()
        ];
    }
}
