<?php

namespace Modules\Campaign\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
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
            'slug' => $this->slug,
            'description' => $this->description,
            'start_on' => $this->start_on,
            'end_on' => $this->end_on,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'locked_by' => $this->locked_by,
            'active' => $this->active,
            'file' => $this->file,
            'config' => $this->config,
            'exports' => CampaignExportResource::collection($this->exports)
        ];
    }
}
