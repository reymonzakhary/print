<?php

namespace App\Http\Resources\Categories;

use App\Plugins\Moneys;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexPrintCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => data_get($this->resource, '_id.$oid', data_get($this->resource, '_id', $this->resource['id'] ?? null)),
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
            "production_dlv" => optional($this->resource)['production_dlv'],
            "start_cost" => optional($this->resource)['start_cost'],
            "display_start_cost" =>  (new Moneys())->setDecimal(5)->setPrecision(5)->setAmount(optional($this->resource)['start_cost'])->format(),
            "linked" => data_get($this->resource, 'linked.$oid', data_get($this->resource, 'linked', $this->resource['linked'] ?? null)),
            "ref_id" => optional($this->resource)['ref_id'],
            "ref_category_name" => optional($this->resource)['ref_category_name'],
            "additional" => $this->getAdditional(optional($this->resource)['additional'] ?? []),
            "suppliers" => optional($this->resource)['suppliers'],
            "matches" => optional($this->resource)['matches'],
            "vat" => optional($this->resource)['vat'],
            "created_at" => Carbon::createFromTimestamp(optional(optional($this->resource)['created_at'])['$date'] / 1000, 'UTC')->toDateTimeString()

        ];
    }

    public function getAdditional(
        array $additional = []
    ): array
    {
        return collect($additional)->map(fn($v) => collect($v)->flatMap(fn($v, $k) => [$k => $v]))->toArray();
    }
}
