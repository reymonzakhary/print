<?php

namespace App\Http\Resources\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
			"title" => $this->title,
			"subject" => $this->subject,
			"body" => $this->body,
			"parent_id" => $this->parent_id,
			"to" => $this->to->value,
			"type" => $this->type->value,
			"contract" => ContractResource::make($this->contract),
            'can_request_quotation' => $this->custom_fields['canRequestQuotation'] ?? false,
			"sender_name" => $this->sender_name,
			"sender_email" => $this->sender_email,
			"recipient_email" => $this->recipient_email,
			"read" => $this->read,
			"read_at" => $this->read_at,
			"from" => $this->from,
            'path' => $this->path,
            'depth' => $this->depth,
            'whoami' => domain()?->id === $this->sender_hostname? 'sender':'recipient',
			"created_at" => $this->created_at,
			"updated_at" => $this->updated_at
        ];
    }
}
