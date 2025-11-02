<?php

namespace App\Http\Resources\Margin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'mode' => $this['mode'],
            'slots' => collect(optional($this)['slots'])->map(function ($slot) {
                return [
                    "from" => (int) $slot['from'],
                    "to" => (int) $slot['to'],
                    "type" => $slot['type'],
                    "value" => $slot['value'],
                ];
            })->toArray(),
            'status' => (bool) $this['status']
        ];
    }
}
