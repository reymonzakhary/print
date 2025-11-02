<?php

namespace Modules\Cms\Transformers\Snippets\Account;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
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
            "coc" => $this->coc,
            "tax_nr" => $this->tax_nr,
            "email" => $this->email,
            "url" => $this->url,
            "addresses" => AddressResource::collection($this->addresses)->hide([
                'full_name', 'company_name', 'phone_number', 'tax_nr'
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
