<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class SupplierConfingResource extends JsonResource
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
            'through' => $this->through,
            'mail' => optional($this)->mail,
            'ftp' => optional($this)->ftp,
            'orders' => optional($this)->orders,
            'order_statuses' => optional($this)->order_statuses,
        ];
    }
}
