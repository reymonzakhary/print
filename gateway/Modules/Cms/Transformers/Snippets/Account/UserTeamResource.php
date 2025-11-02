<?php

namespace Modules\Cms\Transformers\Snippets\Account;

use Illuminate\Http\Resources\Json\JsonResource;

class UserTeamResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'admin' => optional($this->pivot)->admin,
            'authorizer' => optional($this->pivot)->authorizer
        ];
    }
}
