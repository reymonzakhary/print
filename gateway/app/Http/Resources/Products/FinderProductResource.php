<?php

namespace App\Http\Resources\Products;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinderProductResource extends JsonResource
{
    /**
     * @var array
     */
    protected array $withoutFields = [];

    /**
     * @param mixed $resource
     * @return mixed
     */
    public static function collection($resource): mixed
    {
        return tap(new FinderProductResourceCollection($resource), function ($collection) {
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
     * @OA\Property(format="int64", title="id", default="61cdc5a0bcc7630a69454e8b", description="id", property="id"),
     * @OA\Property(format="string", title="iso", default="en", description="iso", property="iso"),
     * @OA\Property(format="string", title="tenant_name", default="", description="tenant_name", example="drukwerkdeal", property="tenant_name"),
     * @OA\Property(format="string", title="tenant_id", default="", description="tenant_id", example="2373d94c-0169-4e2b-8a33-35279ed9413c", property="tenant_id"),
     * @OA\Property(format="string", title="category_id", default="", description="category_id", example="33", property="category_id"),
     * @OA\Property(format="string", title="category_name", default="", description="category_name", example="[""]", property="category_name"),
     * @OA\Property(format="string", title="category_display_name", default="", description="category_display_name", example="Envelopes", property="category_display_name"),
     * @OA\Property(format="string", title="category_slug", default="", description="category_slug", example="", property="category_slug"),
     * @OA\Property(format="string", title="linked", default="", description="linked", example="linked", property="linked"),
     * @OA\Property(format="string", title="shareable", default="false", description="shareable", example="false", property="shareable"),
     * @OA\Property(format="boolean", title="published", default="true", description="published", example="true", property="published"),
     * @OA\Property(type="array", property="object", @OA\Items(ref="#/components/schemas/PrintBoopsResource_object")),
     * @OA\Property(format="string", title="prices", default="", description="prices", example="null", property="prices"),
     * @OA\Property(format="string", title="created_at", default="", description="created_at", example="null", property="created_at"),
     */
    public function toArray($request)
    {
        $data = optional($this->resource)['_id'];
        $prices = optional($this->resource)['prices'];

        return $this->filterFields([
            "id" => $data['_id']['$oid'] ?? $data['_id'],
            "iso" => optional($data)['iso'],
            "tenant_name" => optional($data)['tenant_name'],
            "host_id" => optional($data)['host_id'],
            "tenant_id" => optional($data)['tenant_id'],
            "category_id" => optional(optional($data)['supplier_category'])['$oid'] ?? optional($data)['supplier_category'],
            "category_name" => optional($data)['category_name'],
            "category_display_name" => optional($data)['category_display_name'],
            "category_slug" => optional($data)['category_slug'],
            "linked" => optional(optional($data)['linked'])['$oid'],
            "shareable" => optional($data)['shareable'],
            "published" => optional($data)['published'],
            "object" => $this->getObject(optional($data)['object']),
            "prices" => PrintProductPriceResource::collection($prices??[]),
            "created_at" => optional(optional($data)['created_at'])['$date']
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
