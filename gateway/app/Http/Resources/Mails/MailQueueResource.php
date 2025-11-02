<?php

namespace App\Http\Resources\Mails;

use App\Http\Resources\Statuses\StatusResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MailQueueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "model" => $this->model,
            "status" => StatusResource::make($this->st),
            "message" => $this->message,
            "sent_at" => $this->sent_at,
            "from" => $this->from,
            "to" => $this->to,
            "subject" => $this->subject,
            "cc" => $this->cc,
            "bcc" => $this->bcc,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }
}
