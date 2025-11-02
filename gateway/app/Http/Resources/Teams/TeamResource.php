<?php

declare(strict_types=1);

namespace App\Http\Resources\Teams;

use App\Http\Resources\Address\AddressResource;
use App\Http\Resources\Users\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class TeamResource
 * @package App\Http\Resources\Teams
 * @OA\Schema(
 * )
 */
final class TeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     *
     * @OA\Property(format="int64", title="ID", default=1, description="ID", property="id"),
     * @OA\Property(format="string", title="name", default="chd team", description="name", property="name"),
     * @OA\Property(format="string", title="description", default="cario house developments team", description="team description", property="description"),
     */
    public function toArray(
        Request $request
    ): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'users' => UserResource::collection($this->whenLoaded('users')),
            'members' => UserResource::collection($this->whenLoaded('members')),
            'address' => AddressResource::collection($this->whenLoaded('addresses'))
        ];
    }
}
