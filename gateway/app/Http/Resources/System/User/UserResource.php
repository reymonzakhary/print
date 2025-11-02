<?php

declare(strict_types=1);

namespace App\Http\Resources\System\User;

use App\Http\Resources\System\Profile\ProfileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Companies\CompanyResource;

final class UserResource extends JsonResource
{
    /**
     * @var array
     */
    protected array $withoutFields = [];

    /**
     * @param mixed $resource
     *
     * @return AnonymousResourceCollection|mixed
     */
    public static function collection($resource): mixed
    {
        return tap(
            new UserResourceCollection($resource),

            static function (UserResourceCollection $collection): void {
                $collection->collects = __CLASS__;
            }
        );
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray(
        Request $request
    ): array
    {
        return $this->filterFields([
            'id' => $this->resource->getAttribute('id'),

            'owner' => $this->resource->isOwner(),
            'username' => $this->resource->getAttribute('username'),
            'email' => $this->resource->getAttribute('email'),
            'email_verified_at' => $this->resource->getAttribute('email_verified_at'),
            'type' => $this->resource->getAttribute('type'),

            'profile' => ProfileResource::make(
                $this->whenLoaded('profile')
            ),

            'company' => CompanyResource::make(
                $this->whenLoaded('company')
            ),

            'updated_at' => $this->resource->getAttribute('updated_at'),
            'created_at' => $this->resource->getAttribute('created_at'),
        ]);
    }

    /**
     * Set the keys that are supposed to be filtered out.
     *
     * @param array $fields
     *
     * @return $this
     */
    public function hide(
        array $fields
    ): UserResource
    {
        $this->withoutFields = $fields;

        return $this;
    }

    /**
     * Remove the filtered keys.
     *
     * @param array $array
     *
     * @return array
     */
    protected function filterFields(
        array $array
    ): array
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }
}
