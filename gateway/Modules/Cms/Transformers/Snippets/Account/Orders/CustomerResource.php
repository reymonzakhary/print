<?php

namespace Modules\Cms\Transformers\Snippets\Account\Orders;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Cms\Transformers\Snippets\Account\ProfileResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'owner' => $this?->isOwner(),
            'email' => $this->email,
            'profile' => ProfileResource::make($this->whenLoaded('profile'))->hide($this->withoutFields),
        ];
    }
}
