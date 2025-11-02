<?php

namespace App\Http\Resources\Boxes;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BoxIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'incremental' => $this->incremental,
            'select_limit' => $this->select_limit,
            'iso' => $this->iso,

        ];
    }
}
