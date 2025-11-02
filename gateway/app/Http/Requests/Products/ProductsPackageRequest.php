<?php

namespace App\Http\Requests\Products;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use JsonSerializable;

/**
 * Class ProductsPackageRequest
 * @package App\Http\Requests\Products
 * @OA\Schema(
 *     schema="ProductsPackageRequest",
 *     title="Custom store Package Products Request"
 *
 * )
 */
class ProductsPackageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     * @OA\Property(format="string", title="products", default="[]", description="products", property="products"),
     * @OA\Property(format="string", title="products.*.product_id", default=1, description="products.*.product_id", property="products.*.product_id"),
     * */
    public function rules()
    {
        return [
            'products' => 'array|required_if:combination,true',
            'products.*.sku_id' => 'required|exists:skus,id'
        ];
    }

    protected function prepareForValidation()
    {
        if (!$this->product->combination) {
            throw ValidationException::withMessages([
                'Product' => __('this Product is not a Package type'),
            ]);
        }
    }
}
