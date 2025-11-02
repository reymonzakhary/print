<?php

namespace App\Http\Resources\Companies;

use App\Http\Resources\Address\AddressResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class CompanyResource
 * @package App\Http\Resources\Companies
 * @OA\Schema(
 * )
 */
class CompanyResource extends JsonResource
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
        return tap(new CompanyResourceCollection($resource), function ($collection) {
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
     * @OA\Property(format="string", title="name", default="CHD", description="name", property="name"),
     * @OA\Property(format="string", title="description", default="softwere house", description="description", property="description"),
     * @OA\Property(format="int64", title="coc", default="6541321", description="coc", property="coc"),
     * @OA\Property(format="int64", title="tax_nr", default="654231", description="tax_nr", property="tax_nr"),
     * @OA\Property(format="string", title="email", default="CHD", description="email", property="email"),
     * @OA\Property(format="string", title="url", default="CHD", description="url", property="url"),
     * @OA\Property(type="array", property="addresses", @OA\Items(ref="#/components/schemas/AddressResource")),
     * @OA\Property(format="date", title="created_at", default="2021-09-08T12:19:37.000000Z", description="created_at", property="created_at"),
     * @OA\Property(format="date", title="updated_at", default="2021-09-08T12:19:37.000000Z", description="updated_at", property="updated_at"),
     */
    public function toArray($request)
    {
        return $this->filterFields([
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            "coc" => $this->coc,
            "tax_nr" => $this->tax_nr,
            "email" => $this->email,
            "url" => $this->url,
            "vat_id" => $this->vat_id,
            "dial_code" => $this->dial_code,
            "phone" => $this->phone,
            "addresses" => AddressResource::collection($this->addresses)->hide([
                'full_name', 'company_name', 'phone_number', 'tax_nr'
            ]),
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
