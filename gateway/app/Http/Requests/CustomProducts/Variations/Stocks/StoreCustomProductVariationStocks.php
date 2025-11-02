<?php

namespace App\Http\Requests\CustomProducts\Variations\Stocks;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreCustomProductVariationStocks
 * @package App\Http\Resources\Products
 * @OA\Schema(
 *     schema="StoreCustomProductVariationStocks",
 *     title="Product SKU Resource"
 *
 * )
 */
class StoreCustomProductVariationStocks extends FormRequest
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
     * @OA\Property(format="string", title="qty", default=100, description="qty", property="qty"),
     * @OA\Property(format="string", title="location_id", default=5, description="location_id", property="location_id"),
     */
    public function rules()
    {
        return [
            "qty" => "required",
            "location_id" => "nullable|exists:locations,id"
        ];
    }
}
