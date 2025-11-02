<?php

namespace App\Http\Resources\Members;

use App\Http\Resources\Address\AddressResource;
use App\Http\Resources\Companies\CompanyResource;
use App\Http\Resources\Context\ContextResource;
use App\Http\Resources\Profile\ProfileResource;
use App\Http\Resources\Roles\RoleResource;
use App\Http\Resources\Teams\TeamResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class MemberResource
 * @package App\Http\Resources\Members
 * @OA\Schema(
 * )
 */
class MemberResource extends JsonResource
{

    protected array $defaultHide = ['created_at'];
    /**
     * @var array
     */
    protected array $withoutFields = [];

    /**
     * @param mixed $resource
     * @return AnonymousResourceCollection|mixed
     */
    public static function collection($resource)
    {
        return tap(new MemberResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    /**
     * @OA\Property(format="int64", title="ID", default=1, description="ID", property="id"),
     * @OA\Property(format="string", title="username", default="ahmed.hifny", description="username", property="username"),
     * @OA\Property(format="string", title="email", default="user@example.com", description="email", property="email"),
     * @OA\Property(format="date", title="email_verified_at", default="2021-09-08T12:19:37.000000Z", description="email_verified_at", property="email_verified_at"),
     * @OA\Property(format="date", title="created_at", default="2021-09-08T12:19:37.000000Z", description="created_at", property="created_at"),
     * @OA\Property(format="date", title="updated_at", default="2021-09-08T12:19:37.000000Z", description="updated_at", property="updated_at"),
     * @OA\Property(type="array", property="ctx", @OA\Items(ref="#/components/schemas/ContextResource")),
     * @OA\Property(title="profile", ref="#/components/schemas/ProfileResource", property="profile"),
     * @OA\Property(type="array", property="roles", @OA\Items(ref="#/components/schemas/RoleResource")),
     * @OA\Property(type="array", property="teams", @OA\Items(ref="#/components/schemas/TeamResource")),
     * @OA\Property(type="array", property="companies", @OA\Items(ref="#/components/schemas/CompanyResource")),
     * @OA\Property(type="array", property="addresses", @OA\Items(ref="#/components/schemas/AddressResource")),
     */
    public function toArray($request)
    {
        return $this->filterFields([
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'ctx' => ContextResource::collection($this->whenLoaded('contexts'))->hide($this->withoutFields),
            'profile' => ProfileResource::make($this->whenLoaded('profile'))->hide($this->withoutFields),
            'roles' => RoleResource::collection($this->roles)->hide($this->withoutFields),
            'teams' => TeamResource::collection($this->userTeams),
            'companies' => CompanyResource::make($this->companies->first()),
            'addresses' => AddressResource::collection($this->addresses),
            'type' => $this->type,
        ]);
    }

    /**
     * Set the keys that are supposed to be filtered out.
     *
     * @param array $fields
     * @return $this
     */
    public function hide(array $fields)
    {
        $this->withoutFields = $fields;

        return $this;
    }

    /**
     * Remove the filtered keys.
     *
     * @param $array
     * @return array
     */
    protected function filterFields(array $array)
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }
}
