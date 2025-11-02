<?php

namespace App\Http\Resources\Shops;

use App\Models\Tenants\Box;

class ShopVariationsResource extends ShopProductResource
{


    public function toArray($request)
    {
        return match ((bool)$this->variation) {
            false => $this->singleProductResponse($request),
            true => match ((bool)$this->excludes) {
                (bool)$this->stock_product && (bool)!$this->excludes =>
                $this->productVariationWithOutStockResponse($request),
                (bool)!$this->stock_product && (bool)!$this->excludes =>
                $this->productVariationWithStockResponse($request),
                (bool)!$this->stock_product && (bool)$this->excludes =>
                $this->productCombinationWithOutStockResponse($request),
                (bool)$this->stock_product && (bool)$this->excludes =>
                $this->productCombinationWithStockResponse($request),
            },
        };
    }

    /**
     * @param $request
     * @return array
     */
    protected function singleProductResponse($request)
    {
        return array_merge(parent::toArray($request), [
            'id' => $this->row_id,
            'variations' => []
        ]);
    }

    /**
     * @param $request
     * @return array
     */
    protected function productVariationWithOutStockResponse($request)
    {
        return array_merge(parent::toArray($request), [
            'id' => $this->row_id,
            'variations' => collect(
                $this->variations->groupBy('box.name')
            )->map(function ($v, $k) {
                $box = Box::where('name', $k)->first();
                return [
                    'id' => $box->row_id,
                    'name' => $box->name,
                    'description' => $box->description,
                    'slug' => $box->slug,
                    'input_type' => $box->input_type,
                    'incremental' => $box->incremental,
                    'select_limit' => $box->select_limit,
                    'option_limit' => $box->option_limit,
                    'sqm' => $box->sqm,
                    'iso' => trim($box->iso),
                    'base_id' => $box->base_id,
                    'is_parent' => !$box->parent_id,
                    'media' => collect($box->media)->map(fn($md) => $md->path . $md->name)->toArray(),
                    'options' => ShopVariationResource::collection($v->unique('option_id')),
                ];
            })->values()

        ]);
    }

    /**
     * @param $request
     * @return array
     */
    protected function productVariationWithStockResponse($request)
    {
        return array_merge(parent::toArray($request), [
            'id' => $this->row_id,
            'variations' => collect(
                $this->variations->groupBy('box.name')
            )->map(function ($v, $k) {
                $box = Box::where('name', $k)->first();
                return [
                    'id' => $box->row_id,
                    'name' => $box->name,
                    'description' => $box->description,
                    'slug' => $box->slug,
                    'input_type' => $box->input_type,
                    'incremental' => $box->incremental,
                    'select_limit' => $box->select_limit,
                    'option_limit' => $box->option_limit,
                    'sqm' => $box->sqm,
                    'iso' => trim($box->iso),
                    'base_id' => $box->base_id,
                    'is_parent' => !$box->parent_id,
                    'media' => collect($box->media)->map(fn($md) => $md->path . $md->name)->toArray(),
                    'options' => ShopVariationResource::collection($v->unique('option_id')),
                ];
            })->values()

        ]);
    }


    protected function productCombinationWithOutStockResponse($request)
    {
        return array_merge(parent::toArray($request), [
            'id' => $this->row_id,
            'variations' => ShopProductSkuResource::collection($this->skus()
                ->with([
                    'variations.ancestorsAndSelf',
                    'variations.ancestorsAndSelf.option',
                    'variations.ancestorsAndSelf.box',
                    'variations.ancestorsAndSelf.option.children',
                    'variations.ancestorsAndSelf.product',
                ])
                ->paginate(request()->perPage ?? 10))
        ]);
    }

    protected function productCombinationWithStockResponse($request)
    {
        return array_merge(parent::toArray($request), [
            'id' => $this->row_id,
            'variations' => ShopProductSkuResource::collection($this->skus()
                ->with([
                    'variations.ancestorsAndSelf',
                    'variations.ancestorsAndSelf.option',
                    'variations.ancestorsAndSelf.box',
                    'variations.ancestorsAndSelf.option.children',
                    'variations.ancestorsAndSelf.product',
                    'stock'
                ])
                ->paginate(request()->per_page ?? 10))
        ]);
    }
}
