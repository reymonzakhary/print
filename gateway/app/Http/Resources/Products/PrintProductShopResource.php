<?php

namespace App\Http\Resources\Products;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PrintProductShopResource
 * @package App\Http\Resources\Products
 * @OA\Schema(schema="PrintProductShopResource",title="Print Boops Resource options")
 */
class PrintProductShopResource extends JsonResource
{
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

        return [
            "id" => $this->resource['_id']['$oid'] ?? $this->resource['_id'],
            "iso" => optional($this->resource)['iso'],
            "tenant_name" => optional($this->resource)['tenant_name'],
            "host_id" => optional($this->resource)['host_id'],
            "tenant_id" => optional($this->resource)['tenant_id'],
            "category_id" => optional(optional($this->resource)['supplier_category'])['$oid'] ?? optional($this->resource)['supplier_category'],
            "category_name" => optional($this->resource)['category_name'],
            "category_display_name" => optional($this->resource)['category_display_name'],
            "category_slug" => optional($this->resource)['category_slug'],
            "linked" => optional(optional($this->resource)['linked'])['$oid'],
            "shareable" => optional($this->resource)['shareable'],
            "published" => optional($this->resource)['published'],
            "object" => $this->getObject(optional($this->resource)['object']),
            "prices" => optional($this->resource)['prices'] ? PrintProductShopResource::collection(optional($this->resource)['prices']) : [],
            "created_at" => optional(optional($this->resource)['created_at'])['$date']
        ];

    }

    /**
     * Class PrintProductShopResource_object
     * @OA\Schema(
     *     schema="PrintProductShopResource_object",
     *   description="PrintProductShopResource_object",
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
}
