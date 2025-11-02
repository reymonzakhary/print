<?php

namespace App\Http\Resources\Catalogues;

use App\Plugins\Moneys;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Class CatalogueResource
 * @package App\Http\Resources\Catalogues
 * @OA\Schema(
 * )
 */
class CatalogueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
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
        return tap(new CatalogueResourceCollection($resource), function ($collection) {
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
     * @OA\Property(format="string", title="type", example="biotop", description="type", property="type"),
     * @OA\Property(format="int64", title="type_id", example="6541321", description="type_id", property="type_id"),
     * @OA\Property(format="int64", title="grs", example="80", description="grs", property="grs"),
     * @OA\Property(format="int64", title="grs_id", example="654231", description="grs_id", property="grs_id"),
     * @OA\Property(format="string", title="width", example="420", description="width", property="width"),
     * @OA\Property(format="string", title="height", example="360", description="height", property="height"),
     * @OA\Property(format="string", title="depth", example="1", description="depth", property="depth"),
     * @OA\Property(format="string", title="price", example="800", description="price", property="price"),
     * @OA\Property(format="string", title="calc_type", example="m2", description="calc_type", property="calc_type"),
     * @OA\Property(format="date", title="created_at", example="2021-09-08T12:19:37.000000Z", description="created_at", property="created_at"),
     * @OA\Property(format="date", title="updated_at", example="2021-09-08T12:19:37.000000Z", description="updated_at", property="updated_at"),
     */
    public function toArray($request)
    {
        return $this->filterFields([
            'id' => $this->id,
            'supplier' =>$this->supplier,
            'art_nr' => $this->art_nr,
            'material' => $this->material,
            'material_link' => $this->material_link,
            'material_id' => optional($this)->material_id,
            'grs' => $this->grs . " gr",
            'grs_link' => $this->grs_link,
            'grs_id' => optional($this)->grs_id,
            'price' => $this->price,
            'display_price' =>  (new Moneys())->setPrecision(5)
                ->setDecimal(5)->setAmount($this->price)->format(),
            'ean' => $this->ean,
            'calc_type' => $this->calc_type,
            "density" => $this->density,
            "sheet" => $this->sheet,
            "width" => $this->width,
            "length" => $this->length,
            "height" => $this->height,
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
