<?php

namespace App\Http\Resources\Countries;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'percentage' => $this->percentage,
            'default' => $this->default
        ];
    }
}
