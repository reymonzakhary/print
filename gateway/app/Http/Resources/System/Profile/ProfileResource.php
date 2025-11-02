<?php

declare(strict_types=1);

namespace App\Http\Resources\System\Profile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProfileResource extends JsonResource
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
    public static function collection(mixed $resource): mixed
    {
        return tap(
            new ProfileResourceCollection($resource),

            function ($collection) {
                $collection->collects = __CLASS__;
            }
        );
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        return $this->filterFields([
            'first_name' => $this->resource->getAttribute('first_name'),
            'last_name' => $this->resource->getAttribute('last_name'),

            'gender' => $this->resource->getAttribute('gender'),
            'dob' => $this->resource->getAttribute('dob'),
            'avatar' => $this->resource->avatar(),
            'bio' => $this->resource->getAttribute('bio'),

            'custom_field' => $this->resource->getAttribute('custom_field'),

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
    public function hide(array $fields): static
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
    private function filterFields(array $array): array
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }
}
