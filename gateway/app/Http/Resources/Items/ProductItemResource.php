<?php

namespace App\Http\Resources\Items;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use JsonSerializable;

final class ProductItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            "type" => optional($this)['type'],
            "items" => optional($this)['items'],
            "price" => ProductItemPriceResource::make(array_merge(
                optional($this)['price']??[],
                ['shipping_cost' => optional($this)['shipping_cost']],
                ['selling_price_inc_shipping' => optional($this)['selling_price_inc_shipping']]
            )),
            "divided" => optional($this)['divided'],
            "margins" => optional($this)['margins'],
            "product" => optional($this)['product'],
//            ProductProductResource::collection(optional($this)['product'] instanceof ArrayObject ? optional($this)['product']->toArray() :  optional($this)['product'] ),
            "category" => optional($this)['category'],
            "external" => optional($this)['external'],
            "quantity" => optional($this)['quantity'],
            "tenant_id" => optional($this)['tenant_id'],
            "connection" => optional($this)['connection'],
            "calculation" => optional($this)['calculation'],
            "external_id" => optional($this)['external_id'],
            "tenant_name" => optional($this)['tenant_name'],
            "external_name" => auth()->user()->isOwner() ? optional($this)['external_name'] : optional($this)['tenant_name'],
            "calculation_type" => optional($this)['calculation_type'],
            "order_ref" => auth()->user()->isOwner() ? optional($this)['order_ref'] : null,
            "order_ref_nr" => auth()->user()->isOwner() ? optional($this)['order_ref_nr'] : null,
            "item_ref" => auth()->user()->isOwner() ? optional($this)['item_ref'] : null,

        ];
    }

    protected function getObject(): array
    {
        return collect(optional($this)['items'])->map(fn($v, $k) => [
            "key" => optional($v)['key'],
            "value" => optional($v)['value'],
            "value_id" => optional($v)['value_id'],
            "value_dimension" => optional($v)['value_dimension'],
            "value_dynamic" => optional($v)['value_dynamic'],
            "value_unit" => optional($v)['value_unit'],
            "box_id" => optional($v)['box_id'],
            "key_id" => optional($v)['key_id'],
            "key_appendage" => optional($v)['key_appendage'],
            "key_calc_ref" => optional($v)['key_calc_ref'],
            "key_start_cost" => optional($v)['key_start_cost'],
            "key_incremental" => optional($v)['key_incremental'],
            "key_link" => optional($v)['key_link'],
            "option_id" => optional($v)['option_id'],
            "value_link" => optional($v)['value_link'],
            "display_key" => getDisplayName(optional($v)['display_key'], Str::lower(request()->get('iso'))),
            "display_value" => getDisplayName(optional($v)['display_value'], Str::lower(request()->get('iso')))
        ])->toArray();
    }
}
