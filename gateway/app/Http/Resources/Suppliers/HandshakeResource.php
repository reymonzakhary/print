<?php

namespace App\Http\Resources\Suppliers;

use App\Http\Resources\Statuses\StatusResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HandshakeResource extends JsonResource
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
            'contract_nr' => $this->contract_nr,
            'st' => StatusResource::make($this->st),
            'activated_at' => $this->activated_at,
            'active' => $this->active,
            'callback' => $this?->callback,
            'webhook' => $this->webhook,
            'address' => $this->address,
            'custom_fields' => $this->custom_fields,
            'type' => $this->type,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'blueprint_id' => $this->blueprint_id,
            'has_handshake' => $this->has_handshake,
            'am_requester' => $this->am_requester,
            'am_receiver' => $this->am_receiver,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
