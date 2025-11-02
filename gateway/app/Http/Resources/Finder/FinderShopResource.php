<?php

namespace App\Http\Resources\Finder;

use App\Http\Resources\Products\PrintProductShopCalcResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinderShopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'tenant_id' =>data_get($this->resource, 'supplier'),
            'results' =>  PrintProductShopCalcResource::make(data_get($this->resource, 'results'))
        ];
    }
}
