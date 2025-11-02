<?php

namespace App\Http\Resources\Teams;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrintCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id($this->resource),
            "sort" => optional($this->resource)['sort'],
            "tenant_id" => optional($this->resource)['tenant_id'],
            "tenant_name" => optional($this->resource)['tenant_name'],
            "countries" => optional($this->resource)['countries'],
            "sku" => optional($this->resource)['sku'],
            "name" => $this->resource['name'],
            "system_key" => $this->resource['system_key'],
            "display_name" => optional($this->resource)['display_name'],
            "slug" => $this->resource['slug'],
            "description" => optional($this->resource)['description'],
            "published" => optional($this->resource)['published'],
            "shareable" => optional($this->resource)['shareable'],
            "media" => optional($this->resource)['media'],
            "price_build" => optional($this->resource)['price_build'],
            "has_products" => optional($this->resource)['has_products'],
            "has_manifest" => optional($this->resource)['has_manifest'],
            "calculation_method" => optional($this->resource)['calculation_method'],
            "dlv_days" => optional($this->resource)['dlv_days'],
            "printing_method" => optional($this->resource)['printing_method'],
            "production_days" => optional($this->resource)['production_days'],
            "start_cost" => optional($this->resource)['start_cost'],
            "display_start_cost" => (new \App\Plugins\Moneys())->setAmount(optional($this->resource)['start_cost'])->setPrecision(5)->setDecimal(5)->format(),
            "linked" => optional(optional($this->resource)['linked'])['$oid'],
            "ref_id" => optional($this->resource)['ref_id'],
            "ref_category_name" => optional($this->resource)['ref_category_name'],
            "additional" => $this->getAdditional(optional($this->resource)['additional'] ?? []),
            "suppliers" => optional($this->resource)['suppliers'],
            "matches" => optional($this->resource)['matches'],
            "created_at" => Carbon::createFromTimestamp(optional(optional($this->resource)['created_at'])['$date'] / 1000, 'UTC')->toDateTimeString()
        ];
    }


    /**
     * @param array $additional
     * @return array
     */
    public function getAdditional(
        array $additional = []
    ): array {
        return collect($additional)->map(fn ($v) => collect($v)->flatMap(fn($v, $k) => match ($k) {
            'machine' => [$k => optional($v)['$oid']],
            default => [$k => $v]
        }))->toArray();
    }


    protected function id($obj)
    {
        return optional(optional($obj)['_id'])['$oid'] ?? optional($obj)['_id'];
    }
}
