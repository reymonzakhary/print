<?php

namespace App\Http\Resources\Catalogues;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SystemCatalogueResource extends JsonResource
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
        return tap(new SystemCatalogueResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->filterFields([
            'id' => $this['_id']['$oid'],
            'name' => $this['name'],
            'slug' => $this['slug'],
            'display_name' => getDisplayName($this['display_name']),
            'sort' => $this['sort'],
            'additional' => $this['additional'],
            "current_supplier" => SupplierCatalogueResource::make(collect(optional($this)['me']??[])->first()??[])
        ]);
    }

    /**
     * Set the keys that are supposed to be filtered out.
     *
     * @param array $fields
     * @return $this
     */
    /**
     * Class PrintBoopsResource_object
     * @OA\Schema(
     *     schema="PrintBoopsResource_object",
     *   description="PrintBoopsResource_object",
     *   title="Print Boops Resource Boxes",
     *    @OA\Property(format="int64", title="key_link", default="", description="key_link", property="key_link", example=""),
     *    @OA\Property(format="string", title="value_link", default=1, description="value_link", property="value_link" , example="1"),
     *    @OA\Property(format="string", title="box_id", default=1, description="box_id", property="box_id" , example="1"),
     *    @OA\Property(format="string", title="option_id", default=1, description="drukwerkdeal", property="option_id" , example="1"),
     * )
     */
    public function getObject($data)
    {
        if ($data) {
            $data = collect($data)->map(function ($item) {
                $item['key_link'] = $item['key_link']['$oid'] ?? $item['key_link'];
                $item['value_link'] = $item['value_link']['$oid'] ?? $item['value_link'];
                $item['box_id'] = $item['box_id']['$oid'] ?? $item['box_id'];
                $item['option_id'] = $item['option_id']['$oid'] ?? $item['option_id'];
                return $item;
            });
        }
        return $data;
    }

    public function hide(array $fields)
    {
        $this->withoutFields = $fields;

        return $this;
    }

    /**
     * Remove the filtered keys.
     *
     * @param array $array
     * @return array
     */
    protected function filterFields(array $array)
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }
}
