<?php

namespace App\Http\Resources\Hostnames;

use App\Enums\Status;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HostnameContractResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "contract_nr" => $this->contract_nr,

            "st" => Status::getStatusByCode($this->st),
            "active" => $this->active,
            "activated_at" => $this->activated_at,

            "requester_id" => $this->requester_id,
            "requester_type" => $this->requester_type,
            "requester_connection" => $this->requester_connection,

            "receiver_id" => $this->receiver_id,
            "receiver_type" => $this->receiver_type,
            "receiver_connection" => $this->receiver_connection,

            "manager_contract" => $this->receiver_connection === 'cec',

            "custom_fields" => $this->custom_fields['contract'] ?? [],
            'can_request_quotation' => $this->custom_fields['canRequestQuotation'] ?? false,

            "type" => $this->type,
            "start_at" => $this->start_at,
            "end_at" => $this->end_at,
            "has_handshake" => $this->has_handshake,
            "am_requester" => $this->am_requester,
            "am_receiver" => $this->am_receivere,

            "blueprint_id" => $this->blueprint_id,
            "callback" => $this->callback,
            "webhook" => $this->webhook,
            "updated_at" => $this->updated_at,
            "created_at" => $this->created_at,
        ];
    }
}
