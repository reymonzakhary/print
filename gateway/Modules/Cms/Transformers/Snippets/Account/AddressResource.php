<?php

namespace Modules\Cms\Transformers\Snippets\Account;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Foundation\Settings\Settings;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $team_id = null;
        $team_name = null;
        $team_address = false;
        if(Settings::useTeamAddress()) {
            $team_address = true;
            $team_id = $this->pivot?->pivotParent?->id??$this->pivot?->team_id;
            $team_name = $this->pivot?->pivotParent?->name??$this->pivot?->team_name;
        }

        return [
            'id' => $this->id,
            'team_address' => $team_address,
            'team_id' => $team_id,
            'team_name' => $team_name,
            'address' => $this->address,
            'number' => $this->number,
            'city' => $this->city,
            'region' => $this->region,
            'zip_code' => $this->zip_code,
            'default' => $this->pivot->default ?? false,
            'country' => CountryResource::make($this->country),
            'type' => $this->pivot->type ?? null,
            'full_name' => $this->pivot->full_name ?? null,
            'company_name' => $this->pivot->company_name ?? null,
            'phone_number' => $this->pivot->phone_number ?? null,
            'tax_nr' => $this->pivot->tax_nr ?? null,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
