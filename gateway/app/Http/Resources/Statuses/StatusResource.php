<?php

namespace App\Http\Resources\Statuses;

use App\Enums\Status;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class StatusResource
 * @package App\Http\Resources\StatusResource
 * @OA\Schema(
 * )
 */
class StatusResource extends JsonResource
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
        return tap(new StatusResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * @OA\Property(format="int64", title="ID",description="ID", property="id", example="1"),
     * @OA\Property(format="string", title="code", description="code", property="code", example="301"),
     * @OA\Property(format="string", title="name",description="name", property="name", example="draft"),
     * @OA\Property(format="string", title="description",description="description", property="description", example="This is a default order or offer status"),
     * @OA\Property(format="string", title="created_at", description="created_at", property="created_at", example="2022-03-22T14:01:16.000000Z"),
     * @OA\Property(format="int64", title="updated_at",description="updated_at", property="updated_at", example="2022-03-22T14:01:16.000000Z"),
     *
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return Status::getStatusByCode($this->resource);
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
