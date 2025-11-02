<?php

namespace Modules\Cms\Transformers\Snippets\Account;

use App\Foundation\Settings\Settings;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Cms\Transformers\Snippets\Account\Orders\OrderResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        if (Settings::useTeamAddress()) {
            $addresses = $this->resource->userTeams->map(function ($team) {
                return $team->address()->get();
            })->flatten();
        } else {
            $addresses = $this->resource->addresses()->get();
        }

        return [
            'id' => $this->id,
            'owner' => $this?->isOwner(),
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'profile' => ProfileResource::make($this->profile),
            // 'permission' => collect($this->roles)
            //     ->map(fn($role) => $role->permissions)
            //     ->unique()
            //     ->mapWithKeys(fn($k) => $k->pluck('name')),
            // 'roles' => UserRoleResource::collection($this->roles),
            'orders' => OrderResource::collection($this->orders),
            'teams' => UserTeamResource::collection($this->userTeams),
            'companies' => CompanyResource::collection($this->companies),
            'addresses' => AddressResource::collection($addresses),
        ];
    }
}
