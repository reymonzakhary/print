<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ProduceBulkItemsRequest
 * @package App\Http\Resources\Order\ProduceBulkItemsRequest
 * @OA\Schema(
 * )
 */
class ProduceBulkItemsRequest extends FormRequest
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
     * @OA\Property(type="array",
     *  @OA\Items(
     *     @OA\Property(property="order",title="order", type="integer", example="150"),
     *  @OA\Property(type="array", title="items",  description="items", property="items",
     *     @OA\Items(
     *          @OA\Property(property="id",title="id", type="integer", example="150",),
     *      )
     *  )
     * )
     * )
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            '*.order' => 'exists:orders,id',
            '*.items' => 'required|array',
            '*.items.*.id' => 'exists:items,id',
        ];
    }
}
