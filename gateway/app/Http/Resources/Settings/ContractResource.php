<?php

namespace App\Http\Resources\Settings;

use App\Enums\Status;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $receiver = new $this->receiver_type;
        $receiver_data = $receiver::find($this->receiver_id);

        return [
            "id" => $this->id,
            "st" => Status::getStatusByCode($this->st),
            "receiver" => $receiver_data ?? [],
            "receiver_id" => $this->receiver_id,
            "receiver_name" => $receiver_data?->custom_fields?->pick('name') ?? '',
            "activated_at" => $this->activeted_at,
            "active" => $this->active,
            "callback" => $this->callback,
            "webhook" => $this->webhook,
            "custom_fields" => $this->custom_fields['contract'] ?? [],
            "requester_id" => $this->requester_id,
            "contract_nr" => $this->contract_nr,
            "receiver_type" => $this->receiver_type,
            "requester_type" => $this->requester_type,
            "type" => $this->type,
            "start_at" => $this->start_at,
            "end_at" => $this->end_at,
            "blueprint_id" => $this->blueprint_id,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at ,
        ];
    }
}
