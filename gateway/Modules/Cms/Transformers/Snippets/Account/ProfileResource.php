<?php

namespace Modules\Cms\Transformers\Snippets\Account;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender,
            'dob' => $this->dob,
            'avatar' => $this->avatar(),
            'bio' => $this->bio,
            'custom_field' => $this->custom_field,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
