<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class CartResource
 * @package App\Http\Resources\Cart
 * @OA\Schema(
 *     schema="CartResource",
 *     title="Cart Resource"
 *
 * )
 */
class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    /**
     * @OA\Property(property="Products",type="array" ,
     *       @OA\Items(
     *          @OA\Property(property="id", type="string", example=1),
     *          @OA\Property(property="cart_id", type="string", example=1),
     *          @OA\Property(property="sku_id", type="string", example=3),
     *          @OA\Property(property="product_id", type="string", example=2),
     *          @OA\Property(property="variation", type="string", example=false),
     *          @OA\Property(property="qty", type="string", example=100),
     *        )
     *     ),
     */
    public function toArray($request)
    {
        return [
            'products' => CartProductVariationResource::collection($this->sortBy('id'))->resolve()
        ];
    }
}
