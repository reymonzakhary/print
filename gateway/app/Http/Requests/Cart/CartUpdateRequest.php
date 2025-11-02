<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

/**
 * Class CartUpdateRequest
 * @package App\Http\Requests\Cart
 * @OA\Schema(
 *     schema="CartUpdateRequest",
 *     title="Cart Update Request"
 *
 * )
 */
class CartUpdateRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    /**
     * @OA\Property(property="Product",type="array" ,
     *       @OA\Items(
     *          @OA\Property(property="quantity", type="string", example=200),
     *          @OA\Property(property="product_id", type="string", example=2),
     *          @OA\Property(property="variations", type="string", example="[{id:2},{id:1}]"),
     *        )
     *     ),
     */
    public function rules()
    {
        return [
            'variations' => 'array',
            'variations.*.id' => 'exists:variations,id',
            'reference' => 'string|min:3|max:100',
            'quantity' => 'required|numeric|min:1|max:1000000'
        ];
    }

    protected function prepareForValidation()
    {
        if(optional($this->product->variation)->type  === 'print')
        {
            $mainProduct = $this->product;
                $this->merge([
                    'product' => $mainProduct->variation->product,
                    'dlv' => $mainProduct->variation->price['dlv']['days'],
                    'divided' => $mainProduct->variation->divided,
                    'type' => $mainProduct->variation->type
                ]);

            $products = app('App\Shop\Product\ShopPrintProduct')
            ->setCategories($mainProduct->variation->category['slug'])->products();
            if(!optional($products)['prices']){
                throw ValidationException::withMessages([
                        'price' => $products->getData()->message
                    ]);
            }
            $variation = $mainProduct->variation->toArray();
            $variation['quantity'] = $this->quantity;
            $variation['price'] = $products['prices'][0];
            $variation['calculation'] = $products['calculation'];

            $this->merge([
                'price' => $variation['price']['ppp'],
                'variation' => $variation
            ]);
        }

    }
}
