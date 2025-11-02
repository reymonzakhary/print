<?php

namespace App\Http\Resources\Profile;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProfileResource
 * @package App\Http\Resources\Profile
 * @OA\Schema(
 * )
 */
class ProfileResource extends JsonResource
{
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
        return tap(new ProfileResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * @OA\Property(format="date", title="dob", default="2021-09-08T12:19:37.000000Z", description="dob", property="dob"),
     * @OA\Property(format="string", title="first name", default="name", description="first name", property="first_name"),
     * @OA\Property(format="string", title="last name", default="name", description="last name", property="last_name"),
     * @OA\Property(format="string", title="gender", default="male", description="gender can be male|female|other", property="gender"),
     * @OA\Property(format="string", title="avatar", default="null", description="name", property="avatar"),
     * @OA\Property(format="json", title="custom field", default="test", description="name", property="custom_field"),
     * @OA\Property(format="date", title="created at", default="2021-09-08T12:19:37.000000Z", description="name", property="created_at"),
     * @OA\Property(format="date", title="updated at", default="2021-09-08T12:19:37.000000Z", description="name", property="updated_at"),
     *
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->filterFields([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender,
            'dob' => $this->dob,
            'avatar' => $this->avatar(),
            'bio' => $this->bio,
            'custom_field' => $this->custom_field,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
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
