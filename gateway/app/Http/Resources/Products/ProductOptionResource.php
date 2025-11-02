<?php

namespace App\Http\Resources\Products;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Class ProductOptionResource
 * @package App\Http\Resources\Products
 * @OA\Schema(
 *     schema="ProductOptionResource",
 *     title="Product Option Resource"
 *
 * )
 */
class ProductOptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     * @OA\Property(format="string", title="id", default=1, description="id", property="id"),
     * @OA\Property(format="string", title="name", default="shoes", description="name", property="name"),
     * @OA\Property(format="string", title="price", default=50, description="price", property="price"),
     * @OA\Property(format="string", title="display_price", default="50 EGP", description="display_price", property="display_price"),
     * @OA\Property(format="string", title="sale_price", default=50, description="sale_price", property="sale_price"),
     * @OA\Property(format="boolean", title="incremental", default=true, description="incremental", property="incremental"),
     * @OA\Property(format="boolean", title="published", default=true, description="published", property="published"),
     * @OA\Property(format="boolean", title="override", default=true, description="override", property="override"),
     * @OA\Property(format="boolean", title="free", default=true, description="free", property="free"),
     * @OA\Property(format="int64", title="default_selected", default=1, description="default_selected", property="default_selected"),
     * @OA\Property(format="boolean", title="price_switch", default=true, description="price_switch", property="price_switch"),
     * @OA\Property(format="string", title="created_at", default="2022-06-28T11:59:11.789201Z", description="created_at", property="created_at"),
     * @OA\Property(format="string", title="updated_at", default="2022-06-30T11:59:11.789201Z", description="updated_at", property="updated_at"),
     */
    public function toArray($request)
    {
        return [
            "id" => $this->option->row_id,
            "variation_id" => $this->id,
            "name" => $this->option->name,
            "description" => $this->option->description,
            "input_type" => $this->option->input_type,
//            "min" => $this->option->min,
//            "max" => $this->option->max,
//            "width" => $this->option->width,
//            "height" => $this->option->height,
//            "length" => $this->option->length,
//            "unit" => $this->option->unit,
            "sort" => $this->sort,
            "price" => $this->price->amount(),
            "display_price" => $this->price->format(),
            "sale_price" => $this->sale_price,
            "single" => $this->single,
            "upto" => $this->upto,
            "mime_type" => $this->mime_type,
            "parent_id" => $this->parent_id,
            "incremental" => $this->incremental,
            "incremental_by" => $this->incremental_by,
            "published" => $this->published,
            "override" => $this->override,
            "properties" => $this->properties,
            "default_selected" => $this->default_selected,
            "switch_price" => $this->switch_price,
            "expire_date" => $this->expire_date,
            "appendage" => $this->appendage,
            "child" => $this->child,
            "expire_after" => $this->expire_after,
            'varies' => $this->priceVaries(),
//            "created_at" => $this->created_at,
//            "updated_at" => $this->updated_at
        ];
    }
}
