<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\Context\ContextResource;
use App\Http\Resources\Profile\ProfileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserHistoryResource extends JsonResource
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
            'owner' => $this?->isOwner(),
            'email' => $this->email,
            'ctx' => ContextResource::collection($this->whenLoaded('contexts')),
            'profile' => ProfileResource::make($this->whenLoaded('profile')),
        ];
    }
}
