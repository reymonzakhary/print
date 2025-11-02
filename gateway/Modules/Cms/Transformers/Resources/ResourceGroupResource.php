<?php

namespace Modules\Cms\Transformers\Resources;

use App\Http\Resources\Teams\TeamResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResourceGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sort' => $this->sort,
            'resources' => ResourceIndexResource::collection($this->resources),
            'teams' => TeamResource::collection($this->teams),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
