<?php

namespace App\Http\Resources\Options;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchedOptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
