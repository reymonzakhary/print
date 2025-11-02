<?php

namespace App\Http\Resources\Blueprints;

use App\Http\Resources\Products\ProductIndexResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class BlueprintResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'ns' => $this->ns,
            'blueprint' => $this->blueprint,
            'products' => ProductIndexResource::collection($this->products),
            'configuration' => $this->configuration,
            'sort' => $this->sort,
        ];
    }
}
